<?php

namespace App\Http\Controllers;

use App\Models\VehicleRecord;
use App\Models\ReminderLog;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    protected $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    public function index()
    {
        $user = auth()->user();
        $uid = $user->id;

        $today = Carbon::today()->toDateString();
        $tomorrow = Carbon::tomorrow()->toDateString();
        $next7 = Carbon::today()->addDays(7)->toDateString();
        $next30 = Carbon::today()->addDays(30)->toDateString();
        $monthStart = Carbon::today()->startOfMonth()->toDateTimeString();

        // Basic Counts
        $totalRecords = VehicleRecord::where('user_id', $uid)->count();
        $expiredRecords = VehicleRecord::where('user_id', $uid)->where('expiry_date', '<', $today)->count();
        $dueToday = VehicleRecord::where('user_id', $uid)->where('expiry_date', $today)->count();
        $expiring7 = VehicleRecord::where('user_id', $uid)->whereBetween('expiry_date', [$tomorrow, $next7])->count();
        $expiring30 = VehicleRecord::where('user_id', $uid)->whereBetween('expiry_date', [$tomorrow, $next30])->count();
        $activeRecords = max(0, $totalRecords - $expiredRecords);
        $monthAdded = VehicleRecord::where('user_id', $uid)->where('created_at', '>=', $monthStart)->count();

        // WhatsApp logs counts
        $whatsAppLogs = ReminderLog::where('user_id', $uid)->where('message_type', 'whatsapp')->count();
        $whatsAppSent = ReminderLog::where('user_id', $uid)->where('message_type', 'whatsapp')->where('status', 'sent')->count();
        $whatsAppFailed = ReminderLog::where('user_id', $uid)->where('message_type', 'whatsapp')->where('status', 'failed')->count();
        $whatsAppPending = ReminderLog::where('user_id', $uid)->where('message_type', 'whatsapp')->where('status', 'pending')->count();
        
        $deliveryRate = $whatsAppLogs > 0 ? (int) round(($whatsAppSent / $whatsAppLogs) * 100) : 0;

        // Quota / Subscription info
        $quota = $this->whatsAppService->checkQuota($uid);
        $sub = $this->whatsAppService->getActiveSubscription($uid);
        
        $planName = $quota['plan_name'] ?: 'Starter';
        $whatsAppLimit = $quota['limit'];
        $whatsAppUsed = $quota['used'];
        $whatsAppUsagePercent = $whatsAppLimit > 0 ? (int) round(($whatsAppUsed / $whatsAppLimit) * 100) : 0;

        $customerLimit = 0;
        if ($sub && $sub->plan) {
            $customerLimit = (int) $sub->plan->customer_limit;
        }
        $customerUsagePercent = 0;
        if ($customerLimit > 0) {
            $customerUsagePercent = (int) round(($totalRecords / $customerLimit) * 100);
            $customerUsagePercent = max(0, min(100, $customerUsagePercent));
        }

        $planDaysLeft = -1;
        $isTrial = false;
        $billingCycle = 'monthly';
        if ($sub) {
            $isTrial = (bool) $sub->plan->is_trial;
            $billingCycle = $sub->billing_cycle ?: 'monthly';
            if ($sub->payment_status === 'trial' || $sub->billing_cycle === 'lifetime') {
                $planDaysLeft = 99999; // Represents lifetime
            } else {
                $planDaysLeft = (int) Carbon::today()->diffInDays(Carbon::parse($sub->end_date), false);
            }
        }

        $activePercent = $totalRecords > 0 ? (int) round(($activeRecords / $totalRecords) * 100) : 0;
        $attentionCount = $expiredRecords + $dueToday + $expiring7;

        // Chart Data (last 6 months)
        $monthlyLabels = [];
        $monthlyCounts = [];
        $expiringCounts = [];

        for ($i = 5; $i >= 0; $i--) {
            $mStart = Carbon::today()->startOfMonth()->subMonths($i);
            $mEnd = $mStart->copy()->endOfMonth();
            $monthlyLabels[] = $mStart->format('M');

            $monthlyCounts[] = VehicleRecord::where('user_id', $uid)
                ->whereBetween('created_at', [$mStart->toDateTimeString(), $mEnd->toDateTimeString()])
                ->count();

            $expiringCounts[] = VehicleRecord::where('user_id', $uid)
                ->whereBetween('expiry_date', [$mStart->toDateString(), $mEnd->toDateString()])
                ->count();
        }

        $maxMonthCount = max(array_merge($monthlyCounts, [1]));

        // Priority renewals (expiry <= next 7 days)
        $priorityRows = VehicleRecord::where('user_id', $uid)
            ->where('expiry_date', '<=', $next7)
            ->orderBy('expiry_date', 'asc')
            ->limit(8)
            ->get();

        // Recent records
        $recentRecords = VehicleRecord::where('user_id', $uid)
            ->orderBy('id', 'desc')
            ->limit(7)
            ->get();

        // Vehicle type breakdown
        $vehicleTypes = VehicleRecord::select('vehicle_type', DB::raw('count(*) as total'))
            ->where('user_id', $uid)
            ->groupBy('vehicle_type')
            ->orderBy('total', 'desc')
            ->orderBy('vehicle_type', 'asc')
            ->limit(6)
            ->get();

        // Reminder Needs for modal
        $reminderNeeds = VehicleRecord::where('user_id', $uid)
            ->where('expiry_date', '<=', $next7)
            ->orderBy('expiry_date', 'asc')
            ->limit(100)
            ->get();

        // Sent logs (last 30 days) for modal
        $reminderSent = ReminderLog::where('user_id', $uid)
            ->where('status', 'sent')
            ->where('message_type', 'whatsapp')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->limit(60)
            ->get();

        $envWarning = (empty(env('META_WA_TOKEN')) || empty(env('META_WA_PHONE_ID')))
            ? 'META_WA_TOKEN or META_WA_PHONE_ID is missing in your .env file. WhatsApp sending will fail.'
            : null;

        // Focus Banner Tone & Text
        $focusTitle = 'All clear for today';
        $focusText = 'No urgent renewal follow-up is pending right now.';
        $focusTone = 'success';
        if ($expiredRecords > 0) {
            $focusTitle = 'Expired renewals need attention';
            $focusText = $expiredRecords . ' record' . ($expiredRecords === 1 ? '' : 's') . ' already expired. Follow up first.';
            $focusTone = 'danger';
        } elseif ($dueToday > 0) {
            $focusTitle = 'Renewals due today';
            $focusText = $dueToday . ' customer' . ($dueToday === 1 ? '' : 's') . ' need renewal follow-up today.';
            $focusTone = 'warning';
        } elseif ($expiring7 > 0) {
            $focusTitle = 'Upcoming renewals this week';
            $focusText = $expiring7 . ' record' . ($expiring7 === 1 ? '' : 's') . ' expire within 7 days.';
            $focusTone = 'info';
        } elseif ($totalRecords === 0) {
            $focusTitle = 'Start your center workspace';
            $focusText = 'No customer records saved yet. Add records from the sidebar.';
            $focusTone = 'info';
        }

        return view('dashboard', compact(
            'user', 'totalRecords', 'expiredRecords', 'dueToday', 'expiring7', 'expiring30',
            'activeRecords', 'monthAdded', 'whatsAppLogs', 'whatsAppSent', 'whatsAppFailed',
            'whatsAppPending', 'deliveryRate', 'planName', 'whatsAppLimit', 'whatsAppUsed',
            'whatsAppUsagePercent', 'planDaysLeft', 'isTrial', 'billingCycle', 'activePercent',
            'attentionCount', 'monthlyLabels', 'monthlyCounts', 'expiringCounts', 'maxMonthCount',
            'priorityRows', 'recentRecords', 'vehicleTypes', 'reminderNeeds', 'reminderSent',
            'envWarning', 'focusTitle', 'focusText', 'focusTone', 'customerLimit', 'customerUsagePercent'
        ));
    }

    public function bulkWhatsapp(Request $request)
    {
        $userId = auth()->id();
        $ids = array_filter(array_map('intval', (array) ($request->input('record_ids', []))));
        $lang = $request->input('lang', 'en') === 'guj' ? 'guj' : 'en';

        if (empty($ids)) {
            return response()->json(['success' => false, 'sent' => 0, 'failed' => 0, 'message' => 'No records selected.']);
        }

        $sent = 0;
        $failed = 0;
        $today = Carbon::today()->toDateString();
        $centerPhone = env('CENTER_PHONE') ?: auth()->user()->mobile ?: '+910000000000';
        $centerName = trim((string) (auth()->user()->center_name ?? 'Our PUC Center'));
        $centerAddress = trim((string) (auth()->user()->center_address ?? '')) ?: env('CENTER_ADDRESS_FALLBACK', 'Shop No. 12, Ring Road, Rajkot, Gujarat - 360001');

        foreach ($ids as $id) {
            try {
                $quota = $this->whatsAppService->checkQuota($userId);
                if (!$quota['allowed']) {
                    ReminderLog::create([
                        'user_id' => $userId,
                        'vehicle_record_id' => $id,
                        'customer_mobile' => '',
                        'message_type' => 'whatsapp',
                        'reminder_stage' => 'manual_dashboard',
                        'message_body' => 'Skipped — WhatsApp quota exceeded for this billing period.',
                        'status' => 'skipped',
                        'provider_response' => 'Quota exceeded',
                        'sent_at' => null,
                    ]);
                    $failed++;
                    continue;
                }

                $record = VehicleRecord::where('id', $id)->where('user_id', $userId)->first();
                if (!$record) {
                    ReminderLog::create([
                        'user_id' => $userId,
                        'vehicle_record_id' => $id,
                        'customer_mobile' => '',
                        'message_type' => 'whatsapp',
                        'reminder_stage' => 'manual_dashboard',
                        'message_body' => "Record #{$id} not found or does not belong to this user.",
                        'status' => 'failed',
                        'provider_response' => 'Vehicle record not found',
                        'sent_at' => null,
                    ]);
                    $failed++;
                    continue;
                }

                $name = trim((string) $record->customer_name) ?: 'Customer';
                $vehicle = $this->whatsAppService->formatVehicleNumber($record->vehicle_number);
                
                $expiry = Carbon::parse($record->expiry_date)->format('d M Y');
                $daysLeft = (int) Carbon::today()->diffInDays(Carbon::parse($record->expiry_date), false);
                
                $phone = '+91' . preg_replace('/\D/', '', $record->customer_mobile);
                if (str_starts_with($record->customer_mobile, '91') && strlen(preg_replace('/\D/', '', $record->customer_mobile)) === 12) {
                    $phone = '+' . preg_replace('/\D/', '', $record->customer_mobile);
                }

                $tpl = $this->whatsAppService->resolveWaTemplate($daysLeft, $lang);

                $result = $this->whatsAppService->sendMetaWhatsapp(
                    $phone,
                    $name,
                    $vehicle,
                    $expiry,
                    $daysLeft,
                    $centerPhone,
                    $centerName,
                    $centerAddress,
                    $tpl['name'],
                    $tpl['lang']
                );

                $expiryNote = $daysLeft < 0
                    ? "PUC expired on {$expiry} — OVERDUE"
                    : ($daysLeft === 0 ? "PUC expires TODAY ({$expiry})" : "PUC expires on {$expiry} in {$daysLeft} days");
                $messageBody = "PUC Reminder [{$tpl['name']}] → {$name} | {$vehicle} | {$expiryNote}";

                ReminderLog::create([
                    'user_id' => $userId,
                    'vehicle_record_id' => $id,
                    'customer_mobile' => $record->customer_mobile,
                    'message_type' => 'whatsapp',
                    'reminder_stage' => 'manual_dashboard',
                    'message_body' => $messageBody,
                    'status' => $result['ok'] ? 'sent' : 'failed',
                    'provider_response' => $result['ok'] ? null : ($result['error'] ?? 'Unknown error from WhatsApp API'),
                    'sent_at' => $result['ok'] ? Carbon::now() : null,
                ]);

                if ($result['ok']) {
                    $sent++;
                } else {
                    $failed++;
                }
            } catch (\Throwable $rowErr) {
                Log::error('[BULK-SEND-ROW-FAIL] id=' . $id . ' ' . $rowErr->getMessage());
                ReminderLog::create([
                    'user_id' => $userId,
                    'vehicle_record_id' => $id,
                    'customer_mobile' => '',
                    'message_type' => 'whatsapp',
                    'reminder_stage' => 'manual_dashboard',
                    'message_body' => 'Unexpected error while sending: ' . $rowErr->getMessage(),
                    'status' => 'failed',
                    'provider_response' => $rowErr->getMessage(),
                    'sent_at' => null,
                ]);
                $failed++;
            }
        }

        return response()->json([
            'success' => $sent > 0,
            'sent' => $sent,
            'failed' => $failed,
            'message' => $sent > 0
                ? "✅ {$sent} reminder" . ($sent > 1 ? 's' : '') . " sent!" . ($failed > 0 ? " ({$failed} failed)" : '')
                : "❌ All messages failed. Check your Meta API token & phone ID.",
        ]);
    }
}
