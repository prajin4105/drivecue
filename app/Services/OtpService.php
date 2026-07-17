<?php

namespace App\Services;

use App\Models\OtpVerification;
use App\Models\OtpRateLimit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use RuntimeException;
use InvalidArgumentException;

class OtpService
{
    protected bool $enabled;
    protected string $apiUrl;
    protected string $apiKey;
    protected string $senderId;
    protected string $route;
    protected int $expiryMinutes;
    protected int $otpLength;
    protected int $rateLimitWindow;
    protected int $maxPerPhone;
    protected int $maxPerIp;
    protected string $defaultCountryCode;

    public function __construct()
    {
        $this->enabled = filter_var(env('SMS_PROVIDER_ENABLED', false), FILTER_VALIDATE_BOOLEAN);
        $this->apiUrl = env('NINZA_API_URL', 'https://ninzasms.in.net/auth/send_sms');
        $this->apiKey = env('NINZA_API_KEY', '');
        $this->senderId = env('NINZA_SENDER_ID', '15155');
        $this->route = env('NINZA_ROUTE', '');
        
        $this->expiryMinutes = (int) env('OTP_EXPIRY_MINUTES', 5);
        $this->otpLength = (int) env('OTP_LENGTH', 6);
        
        $this->rateLimitWindow = (int) env('OTP_RATE_LIMIT_WINDOW_MINUTES', 10);
        $this->maxPerPhone = (int) env('OTP_MAX_PER_PHONE', 3);
        $this->maxPerIp = (int) env('OTP_MAX_PER_IP', 10);
        $this->defaultCountryCode = env('DEFAULT_COUNTRY_CODE', '91');
    }

    /**
     * Normalize mobile number to last 10 digits.
     */
    public function normalizeMobile(string $mobile): string
    {
        $digits = preg_replace('/\D+/', '', $mobile) ?? '';
        if (strlen($digits) > 10 && str_starts_with($digits, $this->defaultCountryCode)) {
            $digits = substr($digits, strlen($this->defaultCountryCode));
        }
        return substr($digits, -10);
    }

    /**
     * Check if a mobile number is valid (Indian format: 10 digits starting with 6-9).
     */
    public function isValidMobile(string $mobile): bool
    {
        return (bool) preg_match('/^[6-9][0-9]{9}$/', $this->normalizeMobile($mobile));
    }

    /**
     * Assert rate limit for OTP requests.
     */
    public function assertRateLimit(string $mobile, string $ip): void
    {
        $mobile = $this->normalizeMobile($mobile);

        // Clean up expired rate limits
        OtpRateLimit::where('created_at', '<', Carbon::now()->subMinutes($this->rateLimitWindow))->delete();

        // Check phone rate limit
        $phoneCount = OtpRateLimit::where('identifier', $mobile)
            ->where('type', 'phone')
            ->where('created_at', '>', Carbon::now()->subMinutes($this->rateLimitWindow))
            ->count();

        if ($phoneCount >= $this->maxPerPhone) {
            throw new RuntimeException("Too many OTP requests for this mobile number. Please wait {$this->rateLimitWindow} minutes.");
        }

        // Check IP rate limit
        $ipCount = OtpRateLimit::where('identifier', $ip)
            ->where('type', 'ip')
            ->where('created_at', '>', Carbon::now()->subMinutes($this->rateLimitWindow))
            ->count();

        if ($ipCount >= $this->maxPerIp) {
            throw new RuntimeException("Too many OTP requests from this device. Please try again later.");
        }

        // Record rate limits
        OtpRateLimit::insert([
            ['identifier' => $mobile, 'type' => 'phone', 'created_at' => Carbon::now()],
            ['identifier' => $ip, 'type' => 'ip', 'created_at' => Carbon::now()]
        ]);
    }

    /**
     * Generate and send a new OTP.
     */
    public function createOtp(string $mobile, string $purpose, string $ip): string
    {
        $mobile = $this->normalizeMobile($mobile);

        if (!in_array($purpose, ['register', 'forgot_password'])) {
            throw new InvalidArgumentException('Invalid OTP purpose.');
        }

        $this->assertRateLimit($mobile, $ip);

        $min = (int) str_pad('1', $this->otpLength, '0');
        $max = (int) str_repeat('9', $this->otpLength);
        $otp = (string) random_int($min, $max);
        $expiresAt = Carbon::now()->addMinutes($this->expiryMinutes);

        // Store OTP hash
        OtpVerification::create([
            'mobile' => $mobile,
            'otp_code' => Hash::make($otp),
            'purpose' => $purpose,
            'expires_at' => $expiresAt,
            'is_used' => false
        ]);

        $sent = $this->sendOtpSms($mobile, $otp);

        if (!$sent && !config('app.debug')) {
            // Local fallback / mock if local environment
            if (app()->environment('local', 'testing')) {
                $this->logMockOtp($mobile, $otp, 'API Timeout Fallback');
                session(['last_debug_otp' => $otp]);
                return $otp;
            }
            throw new RuntimeException('OTP could not be sent. Please try again.');
        }

        if (config('app.debug') || !$sent) {
            session(['last_debug_otp' => $otp]);
        }

        return $otp;
    }

    /**
     * Verifies the latest unused OTP for a mobile number.
     */
    public function verifyOtp(string $mobile, string $purpose, string $otp): bool
    {
        $mobile = $this->normalizeMobile($mobile);
        $otp = preg_replace('/\D+/', '', $otp) ?? '';

        if ($otp === '' || !in_array($purpose, ['register', 'forgot_password'])) {
            return false;
        }

        $verification = OtpVerification::where('mobile', $mobile)
            ->where('purpose', $purpose)
            ->where('is_used', false)
            ->where('expires_at', '>=', Carbon::now())
            ->orderBy('id', 'desc')
            ->first();

        if (!$verification || !Hash::check($otp, $verification->otp_code)) {
            return false;
        }

        $verification->update([
            'is_used' => true,
            'used_at' => Carbon::now()
        ]);

        return true;
    }

    /**
     * Send OTP via Ninza SMS API.
     */
    protected function sendOtpSms(string $mobile, string $otp): bool
    {
        if (!$this->enabled) {
            $this->logMockOtp($mobile, $otp, 'Provider Disabled');
            return true;
        }

        if (empty($this->apiKey)) {
            Log::channel('single')->warning("Ninza API key missing. OTP not sent for mobile: {$mobile}");
            return false;
        }

        $payload = [
            'sender_id' => $this->senderId,
            'variables_values' => $otp,
            'numbers' => $this->defaultCountryCode . $mobile,
        ];

        if ($this->route !== '') {
            $payload['rout'] = $this->route;
        }

        $url = $this->apiUrl;
        $headers = [
            'Authorization' => $this->apiKey,
            'Accept' => '*/*',
            'Content-Type' => 'application/json',
        ];

        // Local Hostinger workaround from original code
        if (app()->environment('local') && str_contains($url, 'https://ninzasms.in.net')) {
            $url = str_replace('https://ninzasms.in.net', 'https://www.ninzasms.in.net', $url);
            $headers['Host'] = 'ninzasms.in.net';
        }

        try {
            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->connectTimeout(15)
                ->post($url, $payload);

            Log::channel('single')->info("OTP API mobile={$mobile} http={$response->status()} response={$response->body()}");

            if ($response->failed()) {
                return false;
            }

            $json = $response->json();
            if (is_array($json)) {
                $statusText = strtolower((string) ($json['status'] ?? $json['success'] ?? $json['message'] ?? ''));
                if (in_array($statusText, ['false', 'failed', 'error'], true)) {
                    return false;
                }
            }

            return true;
        } catch (\Throwable $e) {
            Log::channel('single')->error("OTP Send Exception mobile={$mobile} error={$e->getMessage()}");
            return false;
        }
    }

    /**
     * Write mock OTP to a special log.
     */
    protected function logMockOtp(string $mobile, string $otp, string $reason): void
    {
        $logPath = storage_path('logs/otp_mock.log');
        $line = '[' . date('Y-m-d H:i:s') . "] [{$reason}] OTP {$otp} for {$mobile}" . PHP_EOL;
        @file_put_contents($logPath, $line, FILE_APPEND);
    }
}
