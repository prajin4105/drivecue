<?php

namespace App\Services;

use App\Models\User;
use App\Models\VehicleRecord;
use App\Models\ReminderLog;
use App\Models\Subscription;
use App\Models\WhatsappTopup;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    /**
     * Get the active subscription for a center.
     */
    public function getActiveSubscription(int $userId): ?Subscription
    {
        return Subscription::where('user_id', $userId)
            ->where('status', 'active')
            ->where('end_date', '>=', Carbon::today())
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * Count WhatsApp messages used by this center owner.
     *  - Trial: count all-time logs.
     *  - Paid: count current calendar month logs.
     */
    public function countWhatsappUsed(int $userId, bool $isTrial): int
    {
        $query = ReminderLog::where('user_id', $userId)
            ->where('message_type', 'whatsapp');

        if (!$isTrial) {
            $query->whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            ]);
        }

        return $query->count();
    }

    /**
     * Check if a center owner has quota remaining to send a WhatsApp message.
     */
    public function checkQuota(int $userId): array
    {
        $sub = $this->getActiveSubscription($userId);

        if (!$sub) {
            return [
                'allowed' => false,
                'reason' => 'No active subscription. Contact admin to activate your plan.',
                'used' => 0,
                'limit' => 0,
                'plan_name' => '',
            ];
        }

        $plan = $sub->plan;
        $planName = $plan->name;
        $isTrial = (bool) $plan->is_trial;
        $limit = (int) $plan->whatsapp_limit;

        // Limit = 0 means unlimited
        if ($limit === 0) {
            return [
                'allowed' => true,
                'reason' => '',
                'used' => 0,
                'limit' => 0,
                'plan_name' => $planName,
            ];
        }

        // Add topups
        $topups = WhatsappTopup::where('user_id', $userId)
            ->where('payment_status', 'paid')
            ->sum('reminders_added');

        $totalLimit = $limit + (int) $topups;
        $used = $this->countWhatsappUsed($userId, $isTrial);

        if ($used >= $totalLimit) {
            $scope = $isTrial ? 'total (lifetime)' : 'this month';
            $reason = "WhatsApp limit reached for {$scope}. "
                    . "Used {$used} of {$totalLimit}. "
                    . ($isTrial
                        ? 'Upgrade your plan or contact admin for a top-up.'
                        : 'Contact admin for a top-up or wait for next month.');
            return [
                'allowed' => false,
                'reason' => $reason,
                'used' => $used,
                'limit' => $totalLimit,
                'plan_name' => $planName,
            ];
        }

        return [
            'allowed' => true,
            'reason' => '',
            'used' => $used,
            'limit' => $totalLimit,
            'plan_name' => $planName,
        ];
    }

    /**
     * Get remaining messages count.
     */
    public function getRemainingQuota(int $userId): int
    {
        $quota = $this->checkQuota($userId);
        if (!$quota['allowed']) {
            return 0;
        }
        if ($quota['limit'] === 0) {
            return PHP_INT_MAX;
        }
        return max(0, $quota['limit'] - $quota['used']);
    }

    /**
     * Format a vehicle number GJ03AB1234 -> GJ 03 AB 1234
     */
    public function formatVehicleNumber(string $number): string
    {
        $clean = strtoupper(trim($number));
        $clean = preg_replace('/[^A-Z0-9]/', '', $clean) ?? '';
        
        if (preg_match('/^([A-Z]{2})([0-9]{1,2})([A-Z]{1,3})([0-9]{1,4})$/', $clean, $m)) {
            return $m[1] . ' ' . str_pad($m[2], 2, '0', STR_PAD_LEFT) . ' ' . $m[3] . ' ' . $m[4];
        }
        return $clean;
    }

    /**
     * Build the message body by replacing placeholders with actual vehicle details.
     */
    public function buildMessage(VehicleRecord $record, User $owner): string
    {
        $template = trim((string) $owner->whatsapp_message_template);
        if ($template === '') {
            $template = "Hello {customer_name},\n\nYour vehicle {vehicle_number} PUC is expiring on {expiry_date}.\nPlease renew your PUC at {center_name}.\n\nContact: {center_mobile}\n\n- {center_name}";
        }

        $customerName = trim((string) $record->customer_name);
        if ($customerName === '') {
            $customerName = 'Customer';
        }

        $vehicleNumber = $this->formatVehicleNumber($record->vehicle_number);
        if ($vehicleNumber === '') {
            $vehicleNumber = 'your vehicle';
        }

        $issueDate = $record->issue_date ? Carbon::parse($record->issue_date)->format('d M Y') : '-';
        $expiryDate = $record->expiry_date ? Carbon::parse($record->expiry_date)->format('d M Y') : '-';

        $daysLeft = '-';
        if ($record->expiry_date) {
            $days = Carbon::today()->diffInDays(Carbon::parse($record->expiry_date), false);
            if ($days > 0) {
                $daysLeft = $days . ' day' . ($days === 1 ? '' : 's') . ' left';
            } elseif ($days === 0) {
                $daysLeft = 'expires today';
            } else {
                $overdue = abs($days);
                $daysLeft = $overdue . ' day' . ($overdue === 1 ? '' : 's') . ' overdue';
            }
        }

        $ownerName = trim((string) $owner->first_name . ' ' . (string) $owner->last_name);
        $center = trim((string) $owner->center_name);
        if ($center === '') {
            $center = 'Drive Cue';
        }

        $values = [
            '{customer_name}' => $customerName,
            '{customer_mobile}' => (string) $record->customer_mobile,
            '{vehicle_number}' => $vehicleNumber,
            '{vehicle_type}' => (string) $record->vehicle_type,
            '{fuel_type}' => (string) $record->fuel_type,
            '{certificate_number}' => (string) $record->puc_certificate_number,
            '{issue_date}' => $issueDate,
            '{expiry_date}' => $expiryDate,
            '{days_left}' => $daysLeft,
            '{center_name}' => $center,
            '{center_mobile}' => (string) $owner->mobile,
            '{owner_name}' => $ownerName !== '' ? $ownerName : 'Center Owner',
            '{app_name}' => 'Drive Cue',
        ];

        return trim(strtr($template, $values));
    }

    /**
     * Resolves the template name and language code based on days left and language selection.
     */
    public function resolveWaTemplate(int $daysLeft, string $lang): array
    {
        if ($daysLeft < 0) {
            $stage = 'expired';
        } elseif ($daysLeft <= 1) {
            $stage = 'tomorrow';
        } else {
            $stage = '7_days';
        }

        $isGuj = $lang === 'guj';

        $names = [
            'expired' => $isGuj ? 'puc_reminder_expired_guj' : 'puc_reminder_expired',
            'tomorrow' => $isGuj ? 'puc_reminder_tomorrow_guj' : 'puc_reminder_tomorrow',
            '7_days' => $isGuj ? 'puc_reminder_7_days_guj' : 'puc_reminder_7_days',
        ];

        return [
            'name' => $names[$stage],
            'lang' => $isGuj ? 'gu' : 'en_US',
        ];
    }

    /**
     * Sends a WhatsApp message via Meta Cloud API using the requested variables.
     */
    public function sendMetaWhatsapp(
        string $phone,
        string $name,
        string $vehicle,
        string $expiry,
        int $daysLeft,
        string $centerPhone,
        string $centerName,
        string $centerAddress,
        string $templateName,
        string $templateLang
    ): array {
        $token = env('META_WA_TOKEN');
        $phoneId = env('META_WA_PHONE_ID');
        $apiVersion = env('META_WA_API_VERSION', 'v19.0');

        if (empty($token) || empty($phoneId)) {
            return ['ok' => false, 'error' => 'META_WA_TOKEN or META_WA_PHONE_ID not set in .env'];
        }

        $url = "https://graph.facebook.com/{$apiVersion}/{$phoneId}/messages";

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $phone,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => $templateLang],
                'components' => [[
                    'type' => 'body',
                    'parameters' => [
                        ['type' => 'text', 'text' => $name],           // {{1}}
                        ['type' => 'text', 'text' => $vehicle],        // {{2}}
                        ['type' => 'text', 'text' => $expiry],         // {{3}}
                        ['type' => 'text', 'text' => $centerPhone],    // {{4}}
                        ['type' => 'text', 'text' => $centerName],     // {{5}}
                        ['type' => 'text', 'text' => $centerAddress],  // {{6}}
                    ],
                ]],
            ],
        ];

        try {
            $response = Http::withToken($token)
                ->timeout(15)
                ->post($url, $payload);

            if ($response->successful()) {
                return ['ok' => true, 'error' => null];
            }

            $errorDetail = $response->json('error.message') ?? $response->json('error.code') ?? 'Unknown API Error';
            return ['ok' => false, 'error' => 'HTTP ' . $response->status() . ' - ' . $errorDetail . ' | Raw: ' . substr($response->body(), 0, 300)];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => 'Network error: ' . $e->getMessage()];
        }
    }
}
