<?php

namespace App\Services;

use App\Models\PaymentSetting;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class RazorpayService
{
    public function mode(): string { return PaymentSetting::mode(); }

    public function keyId(?string $mode = null): ?string
    {
        return config('razorpay.' . ($mode ?? $this->mode()) . '.key_id');
    }

    public function configured(?string $mode = null): bool
    {
        $config = config('razorpay.' . ($mode ?? $this->mode()));
        return filled($config['key_id'] ?? null) && filled($config['key_secret'] ?? null);
    }

    public function client(string $mode): PendingRequest
    {
        return Http::baseUrl('https://api.razorpay.com/v1')
            ->acceptJson()
            ->asJson()
            ->withBasicAuth(config("razorpay.{$mode}.key_id"), config("razorpay.{$mode}.key_secret"));
    }

    public function verifySignature(string $orderId, string $paymentId, string $signature, string $mode): bool
    {
        $secret = (string) config("razorpay.{$mode}.key_secret");
        return $secret !== '' && hash_equals(hash_hmac('sha256', $orderId . '|' . $paymentId, $secret), $signature);
    }

    public function verifyWebhook(string $payload, ?string $signature, string $mode): bool
    {
        $secret = (string) config("razorpay.{$mode}.webhook_secret");
        return $secret !== '' && filled($signature) && hash_equals(hash_hmac('sha256', $payload, $secret), $signature);
    }
}
