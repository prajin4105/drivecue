<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Fetch active subscription
        $activeSubscription = Subscription::with('plan')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        // Fetch past successful payments
        $payments = Payment::with('plan')
            ->where('user_id', $user->id)
            ->where('status', 'paid')
            ->orderBy('paid_at', 'desc')
            ->get();

        return view('billing.index', compact('activeSubscription', 'payments'));
    }

    public function invoice(Request $request, Payment $payment)
    {
        // Ensure the payment belongs to the authenticated user
        if ($payment->user_id !== $request->user()->id || $payment->status !== 'paid') {
            abort(403, 'Unauthorized access to this invoice.');
        }
        
        $payment->load(['plan', 'user']);

        return view('billing.invoice', compact('payment'));
    }
}
