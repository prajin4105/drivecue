@extends('layouts.dashboard')

@section('title', 'Checkout | ' . config('app.name'))

@section('content')
<div class="dash-wrap" style="max-width: 800px; margin: 0 auto;">
    <div class="dash-header">
        <div>
            <h1 class="dash-title">Checkout</h1>
            <p class="dash-sub">Review your plan and complete the payment.</p>
        </div>
    </div>

    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.02); display:flex; flex-direction:column; md:flex-row;">
        
        <!-- Order Summary -->
        <div style="background:#f8fafc; padding:24px; border-bottom:1px solid #e2e8f0;">
            <h2 style="font-size:16px; font-weight:700; color:#0f172a; margin-top:0; margin-bottom:16px;">Order Summary</h2>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <div>
                    <div style="font-size:16px; font-weight:700; color:#1e293b;">{{ $plan->name }}</div>
                    <div style="font-size:13px; color:#64748b;">{{ ucfirst($billingCycle) }} Subscription (Strictly {{ $billingCycle === 'yearly' ? '365' : '30' }} Days)</div>
                </div>
                <div style="font-size:20px; font-weight:800; color:#0f172a;">
                    ₹{{ number_format($billingCycle === 'yearly' ? $plan->yearly_price : $plan->monthly_price, 2) }}
                </div>
            </div>
            
            <div style="font-size:13px; color:#64748b; margin-top:16px; padding-top:16px; border-top:1px dashed #cbd5e1;">
                <strong>Note:</strong> If you already have an active subscription, this plan will be queued and will automatically start when your current plan ends.
            </div>
        </div>

        <!-- Billing Details Form -->
        <div style="padding:24px;">
            <h2 style="font-size:16px; font-weight:700; color:#0f172a; margin-top:0; margin-bottom:16px;">Billing Information</h2>
            <p style="font-size:13px; color:#64748b; margin-top:0; margin-bottom:24px;">Please confirm your details for the invoice.</p>
            
            <form id="checkoutForm" onsubmit="event.preventDefault(); startPayment();">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                    <div>
                        <label style="display:block; font-size:13px; font-weight:600; color:#475569; margin-bottom:6px;">Full Name</label>
                        <input type="text" id="billing_name" value="{{ auth()->user()->name }}" required class="form-input" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px;">
                    </div>
                    <div>
                        <label for="billing_email" style="display:block; font-size:14px; font-weight:600; color:#334155; margin-bottom:6px;">Email Address <span style="color:#ef4444">*</span></label>
                        <input type="email" id="billing_email" value="{{ (str_ends_with(auth()->user()->email, '@drivecue.com') || str_ends_with(auth()->user()->email, '@drive.cue')) ? '' : auth()->user()->email }}" required class="form-input" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px;">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                    <div>
                        <label style="display:block; font-size:13px; font-weight:600; color:#475569; margin-bottom:6px;">Mobile Number</label>
                        <input type="text" id="billing_mobile" value="{{ auth()->user()->mobile }}" required class="form-input" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px;">
                    </div>
                    <div>
                        <label style="display:block; font-size:13px; font-weight:600; color:#475569; margin-bottom:6px;">GST Number (Optional)</label>
                        <input type="text" id="billing_gst" placeholder="e.g. 22AAAAA0000A1Z5" class="form-input" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px;">
                    </div>
                </div>

                <div style="margin-bottom:24px;">
                    <label style="display:block; font-size:13px; font-weight:600; color:#475569; margin-bottom:6px;">Billing Address</label>
                    <textarea id="billing_address" rows="3" required class="form-input" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px; font-family:inherit;">{{ auth()->user()->center_address }}</textarea>
                </div>

                <button type="submit" id="payBtn" style="width:100%; background:#2563eb; color:#fff; border:none; padding:14px; border-radius:8px; font-size:16px; font-weight:700; cursor:pointer; display:flex; justify-content:center; align-items:center; gap:8px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    Proceed to Pay ₹{{ number_format($billingCycle === 'yearly' ? $plan->yearly_price : $plan->monthly_price, 2) }}
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    async function startPayment() {
        const btn = document.getElementById('payBtn');
        btn.disabled = true;
        btn.innerHTML = 'Processing...';

        try {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Create Order
            const orderRes = await fetch("{{ route('payments.create-order') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: JSON.stringify({
                    plan_id: {{ $plan->id }},
                    billing_cycle: "{{ $billingCycle }}"
                })
            });

            if (!orderRes.ok) {
                const err = await orderRes.json();
                alert(err.message || 'Error creating order');
                btn.disabled = false;
                btn.innerHTML = 'Proceed to Pay';
                return;
            }

            const order = await orderRes.json();

            // Open Razorpay
            const options = {
                key: order.key,
                amount: order.amount,
                currency: order.currency,
                name: order.name,
                description: order.description,
                order_id: order.order_id,
                handler: async function (response) {
                    btn.innerHTML = 'Verifying...';
                    
                    const verifyRes = await fetch("{{ route('payments.verify') }}", {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                        body: JSON.stringify(response)
                    });

                    if (verifyRes.ok) {
                        alert('Payment successful!');
                        window.location.href = "{{ route('billing.index') }}";
                    } else {
                        const err = await verifyRes.json();
                        alert(err.message || 'Payment verification failed');
                        btn.disabled = false;
                        btn.innerHTML = 'Proceed to Pay';
                    }
                },
                prefill: {
                    name: document.getElementById('billing_name').value,
                    email: document.getElementById('billing_email').value,
                    contact: document.getElementById('billing_mobile').value
                },
                theme: {
                    color: "#2563eb"
                }
            };
            
            const rzp = new Razorpay(options);
            rzp.on('payment.failed', function (response) {
                alert('Payment failed: ' + response.error.description);
                btn.disabled = false;
                btn.innerHTML = 'Proceed to Pay';
            });
            rzp.open();

        } catch (e) {
            alert('An unexpected error occurred.');
            btn.disabled = false;
            btn.innerHTML = 'Proceed to Pay';
        }
    }
</script>
@endsection
