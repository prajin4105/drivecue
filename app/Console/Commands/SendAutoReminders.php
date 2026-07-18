<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\VehicleRecord;
use App\Models\ReminderLog;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use App\Models\CronLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendAutoReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:send-auto-reminders';

    /**
     * The console command description.
     */
    protected $description = 'Sends automated WhatsApp reminders based on center owner settings';

    /**
     * Execute the console command.
     */
    public function handle(WhatsAppService $whatsAppService)
    {
        $cronLog = CronLog::create([
            'command' => $this->signature,
            'status' => 'running',
            'started_at' => Carbon::now(),
        ]);

        $this->info('Starting automated WhatsApp reminders...');

        // Get all active center owners who have auto_reminder_days configured
        $users = User::where('role', 1)
            ->where('status', 'active')
            ->whereNotNull('auto_reminder_days')
            ->get();

        $totalSent = 0;
        $totalFailed = 0;
        $totalSkipped = 0;

        foreach ($users as $user) {
            $daysArray = $user->auto_reminder_days;
            if (empty($daysArray) || !is_array($daysArray)) {
                continue;
            }

            $lang = $user->whatsapp_language === 'guj' ? 'guj' : 'en';
            
            $centerPhone = env('CENTER_PHONE') ?: $user->mobile ?: '+910000000000';
            $centerName = trim((string) ($user->center_name ?? 'Our PUC Center'));
            $centerAddress = trim((string) ($user->center_address ?? '')) ?: env('CENTER_ADDRESS_FALLBACK', 'Shop No. 12, Ring Road, Rajkot, Gujarat - 360001');

            foreach ($daysArray as $daysBefore) {
                $daysBefore = (int) $daysBefore;
                
                // Expiry date should be exactly exactly "today + $daysBefore"
                $targetDate = Carbon::today()->addDays($daysBefore)->toDateString();

                $vehicles = VehicleRecord::where('user_id', $user->id)
                    ->where('expiry_date', $targetDate)
                    ->get();

                foreach ($vehicles as $record) {
                    // Prevent duplicates: Check if an auto reminder was already sent for this specific vehicle for THIS specific stage (daysLeft)
                    // Or if any reminder was sent successfully today.
                    $alreadySent = ReminderLog::where('vehicle_record_id', $record->id)
                        ->where('message_type', 'whatsapp')
                        ->where('status', 'sent')
                        ->where(function($query) use ($daysBefore) {
                            $query->where('reminder_stage', 'auto_cron_' . $daysBefore)
                                  ->orWhereDate('created_at', Carbon::today());
                        })
                        ->exists();

                    if ($alreadySent) {
                        $totalSkipped++;
                        continue;
                    }

                    // Check quota
                    $quota = $whatsAppService->checkQuota($user->id);
                    if (!$quota['allowed']) {
                        ReminderLog::create([
                            'user_id' => $user->id,
                            'vehicle_record_id' => $record->id,
                            'customer_mobile' => $record->customer_mobile ?? '',
                            'message_type' => 'whatsapp',
                            'reminder_stage' => 'auto_cron_' . $daysBefore,
                            'message_body' => 'Skipped — WhatsApp quota exceeded.',
                            'status' => 'skipped',
                            'provider_response' => 'Quota exceeded',
                            'sent_at' => null,
                        ]);
                        $totalSkipped++;
                        break 2; // Break the vehicle and daysBefore loop for this user, as their quota is exceeded
                    }

                    // Proceed to send
                    $name = trim((string) $record->customer_name) ?: 'Customer';
                    $vehicle = $whatsAppService->formatVehicleNumber($record->vehicle_number);
                    $expiry = Carbon::parse($record->expiry_date)->format('d M Y');
                    $phone = '+91' . preg_replace('/\D/', '', $record->customer_mobile);
                    if (str_starts_with($record->customer_mobile, '91') && strlen(preg_replace('/\D/', '', $record->customer_mobile)) === 12) {
                        $phone = '+' . preg_replace('/\D/', '', $record->customer_mobile);
                    }

                    $tpl = $whatsAppService->resolveWaTemplate($daysBefore, $lang);

                    try {
                        $result = $whatsAppService->sendMetaWhatsapp(
                            $phone,
                            $name,
                            $vehicle,
                            $expiry,
                            $daysBefore,
                            $centerPhone,
                            $centerName,
                            $centerAddress,
                            $tpl['name'],
                            $tpl['lang']
                        );

                        $expiryNote = $daysBefore === 0 ? "PUC expires TODAY ({$expiry})" : "PUC expires on {$expiry} in {$daysBefore} days";
                        $messageBody = "PUC Reminder [{$tpl['name']}] → {$name} | {$vehicle} | {$expiryNote}";

                        ReminderLog::create([
                            'user_id' => $user->id,
                            'vehicle_record_id' => $record->id,
                            'customer_mobile' => $record->customer_mobile,
                            'message_type' => 'whatsapp',
                            'reminder_stage' => 'auto_cron_' . $daysBefore,
                            'message_body' => $messageBody,
                            'status' => $result['ok'] ? 'sent' : 'failed',
                            'provider_response' => $result['ok'] ? null : ($result['error'] ?? 'Unknown error'),
                            'sent_at' => $result['ok'] ? Carbon::now() : null,
                        ]);

                        if ($result['ok']) {
                            $totalSent++;
                        } else {
                            $totalFailed++;
                        }
                    } catch (\Throwable $e) {
                        Log::error('[CRON-SEND-FAIL] record_id=' . $record->id . ' ' . $e->getMessage());
                        ReminderLog::create([
                            'user_id' => $user->id,
                            'vehicle_record_id' => $record->id,
                            'customer_mobile' => $record->customer_mobile,
                            'message_type' => 'whatsapp',
                            'reminder_stage' => 'auto_cron_' . $daysBefore,
                            'message_body' => 'Exception: ' . $e->getMessage(),
                            'status' => 'failed',
                            'provider_response' => $e->getMessage(),
                            'sent_at' => null,
                        ]);
                        $totalFailed++;
                    }
                }
            }
        }

        $output = "Done! Sent: {$totalSent}, Failed: {$totalFailed}, Skipped: {$totalSkipped}";
        $this->info($output);
        
        $cronLog->update([
            'status' => 'success',
            'output' => $output,
            'completed_at' => Carbon::now(),
        ]);
    }
}
