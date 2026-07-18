<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\RazorpayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function checkout(Request $request, Plan $plan)
    {
        $billingCycle = $request->query('billing_cycle', 'monthly');
        $razorpay = app(RazorpayService::class);
        return view('checkout.index', compact('plan', 'billingCycle', 'razorpay'));
    }

    public function createOrder(Request $request, RazorpayService $razorpay)
    {
        $data = $request->validate(['plan_id' => ['required', 'integer'], 'billing_cycle' => ['required', 'in:monthly,yearly']]);
        $plan = Plan::whereKey($data['plan_id'])->where('status', 'active')->where('is_trial', false)->firstOrFail();
        $amount = (int) round(((float) ($data['billing_cycle'] === 'yearly' ? $plan->yearly_price : $plan->monthly_price)) * 100);
        $mode = $razorpay->mode();

        if ($amount < 100 || ! $razorpay->configured($mode)) {
            return response()->json(['message' => 'Payments are not configured for the current Razorpay mode.'], 422);
        }

        $receipt = 'dc_' . Str::lower(Str::random(18));
        $response = $razorpay->client($mode)->post('/orders', ['amount' => $amount, 'currency' => 'INR', 'receipt' => $receipt, 'notes' => ['plan_id' => (string) $plan->id, 'user_id' => (string) $request->user()->id]])->throw()->json();
        Payment::create(['user_id' => $request->user()->id, 'plan_id' => $plan->id, 'billing_cycle' => $data['billing_cycle'], 'mode' => $mode, 'amount' => $amount, 'currency' => 'INR', 'razorpay_order_id' => $response['id'], 'status' => 'created', 'gateway_payload' => $response]);

        return response()->json(['key' => $razorpay->keyId($mode), 'order_id' => $response['id'], 'amount' => $amount, 'currency' => 'INR', 'name' => config('app.name'), 'description' => $plan->name . ' (' . ucfirst($data['billing_cycle']) . ')', 'prefill' => ['name' => $request->user()->name, 'contact' => $request->user()->mobile]]);
    }

    public function verify(Request $request, RazorpayService $razorpay)
    {
        $data = $request->validate(['razorpay_payment_id' => ['required', 'string'], 'razorpay_order_id' => ['required', 'string'], 'razorpay_signature' => ['required', 'string']]);
        $payment = Payment::where('razorpay_order_id', $data['razorpay_order_id'])->where('user_id', $request->user()->id)->firstOrFail();
        if (! $razorpay->verifySignature($payment->razorpay_order_id, $data['razorpay_payment_id'], $data['razorpay_signature'], $payment->mode)) abort(422, 'Payment signature could not be verified.');

        $gatewayPayment = $razorpay->client($payment->mode)->get('/payments/' . $data['razorpay_payment_id'])->throw()->json();
        if (($gatewayPayment['order_id'] ?? null) !== $payment->razorpay_order_id || ($gatewayPayment['status'] ?? null) !== 'captured') {
            $payment->update(['status' => 'authorized', 'razorpay_payment_id' => $data['razorpay_payment_id'], 'razorpay_signature' => $data['razorpay_signature'], 'gateway_payload' => $gatewayPayment]);
            return response()->json(['message' => 'Payment is authorised and awaiting capture. Your plan will activate automatically after capture.'], 202);
        }

        $this->fulfill($payment, $gatewayPayment, $data['razorpay_signature']);
        return response()->json(['message' => 'Payment verified. Your subscription is active.']);
    }

    public function webhook(Request $request, RazorpayService $razorpay)
    {
        $payload = $request->getContent();
        $data = json_decode($payload, true);
        $orderId = data_get($data, 'payload.payment.entity.order_id');
        $paymentId = data_get($data, 'payload.payment.entity.id');
        $payment = $orderId ? Payment::where('razorpay_order_id', $orderId)->first() : null;
        if (! $payment || ! $razorpay->verifyWebhook($payload, $request->header('X-Razorpay-Signature'), $payment->mode)) abort(400, 'Invalid webhook signature.');
        if (data_get($data, 'event') === 'payment.captured' && data_get($data, 'payload.payment.entity.status') === 'captured') $this->fulfill($payment, data_get($data, 'payload.payment.entity'), null, $paymentId);
        return response()->json(['ok' => true]);
    }

    private function fulfill(Payment $payment, array $gatewayPayload, ?string $signature = null, ?string $webhookPaymentId = null): void
    {
        DB::transaction(function () use ($payment, $gatewayPayload, $signature, $webhookPaymentId) {
            $payment = Payment::lockForUpdate()->findOrFail($payment->id);
            if ($payment->status === 'paid') return;
            
            // Find current active subscription
            $activeSub = Subscription::where('user_id', $payment->user_id)->where('status', 'active')->orderBy('end_date', 'desc')->first();
            
            $now = Carbon::today();
            $startDate = $activeSub ? Carbon::parse($activeSub->end_date)->addDay() : $now;
            
            // Strictly 30 days or 365 days
            $days = $payment->billing_cycle === 'yearly' ? 365 : 30;
            $endDate = $startDate->copy()->addDays($days - 1);
            
            // We don't cancel the active subscription, we just queue this one up if it starts in the future.
            // If it starts today, it's active. If it starts in the future, it's queued.
            $status = $startDate->isFuture() ? 'queued' : 'active';
            
            Subscription::create(['user_id' => $payment->user_id, 'plan_id' => $payment->plan_id, 'start_date' => $startDate, 'end_date' => $endDate, 'status' => $status, 'payment_status' => 'paid', 'billing_cycle' => $payment->billing_cycle, 'notes' => 'Razorpay payment ' . ($webhookPaymentId ?? $gatewayPayload['id'] ?? '')]);
            $payment->update(['status' => 'paid', 'paid_at' => now(), 'razorpay_payment_id' => $webhookPaymentId ?? $gatewayPayload['id'] ?? $payment->razorpay_payment_id, 'razorpay_signature' => $signature ?? $payment->razorpay_signature, 'gateway_payload' => $gatewayPayload]);
        });
    }
}
