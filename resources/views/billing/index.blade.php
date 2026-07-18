@extends('layouts.dashboard')

@section('title', 'Billing & Plans')

@section('content')
<div class="dash-wrap">
    <div class="dash-header">
        <div>
            <h1 class="dash-title">Billing & Plans</h1>
            <p class="dash-sub">Manage your subscription, view billing history, and download invoices.</p>
        </div>
        @if (!$activeSubscription)
        <div>
            <a href="{{ route('pricing.index') }}" class="dash-btn btn-primary" style="display:inline-flex; align-items:center; gap:6px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                Upgrade Plan
            </a>
        </div>
        @endif
    </div>

    <!-- Active Subscription Section -->
    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:24px; margin-bottom:24px; box-shadow:0 1px 3px rgba(0,0,0,0.02);">
        <h2 style="font-size:18px; font-weight:700; color:#0f172a; margin:0 0 16px 0;">Current Plan</h2>
        
        @if ($activeSubscription)
            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px; background:#f8fafc; padding:20px; border-radius:8px; border:1px solid #e2e8f0;">
                <div>
                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:8px;">
                        <span style="font-size:20px; font-weight:800; color:#0f172a;">{{ $activeSubscription->plan->name ?? 'Unknown Plan' }}</span>
                        <span style="background:#dcfce7; color:#16a34a; font-size:11px; font-weight:700; padding:4px 8px; border-radius:12px;">Active</span>
                    </div>
                    <div style="font-size:14px; color:#475569; font-weight:500;">
                        Billing Cycle: <strong style="color:#1e293b;">{{ ucfirst($activeSubscription->billing_cycle) }}</strong> &bull; 
                        Valid until: <strong style="color:#1e293b;">{{ $activeSubscription->end_date ? $activeSubscription->end_date->format('d M Y') : 'N/A' }}</strong>
                    </div>
                </div>
                <div style="display:flex; gap:24px;">
                    <div style="text-align:right;">
                        <div style="font-size:13px; color:#64748b; font-weight:600; margin-bottom:4px;">Customer Limit</div>
                        <div style="font-size:16px; font-weight:700; color:#0f172a;">{{ $activeSubscription->plan->customer_limit == 0 ? 'Unlimited' : number_format($activeSubscription->plan->customer_limit) }}</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:13px; color:#64748b; font-weight:600; margin-bottom:4px;">WhatsApp Limit</div>
                        <div style="font-size:16px; font-weight:700; color:#0f172a;">{{ $activeSubscription->plan->whatsapp_limit == 0 ? 'Unlimited' : number_format($activeSubscription->plan->whatsapp_limit) }}</div>
                    </div>
                </div>
            </div>
        @else
            <div style="background:#fff3cd; color:#856404; padding:16px; border-radius:8px; font-weight:600; border:1px solid #ffeeba;">
                You do not have an active subscription. <a href="{{ route('pricing.index') }}" style="color:#856404; text-decoration:underline;">Subscribe now</a> to unlock features.
            </div>
        @endif
    </div>

    <!-- Billing History Section -->
    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.02); overflow:hidden;">
        <div style="padding:24px; border-bottom:1px solid #f1f5f9;">
            <h2 style="font-size:18px; font-weight:700; color:#0f172a; margin:0;">Billing History</h2>
        </div>
        
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; text-align:left;">
                <thead style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                    <tr>
                        <th style="padding:16px 24px; font-size:13px; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:0.5px;">Date</th>
                        <th style="padding:16px 24px; font-size:13px; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:0.5px;">Amount</th>
                        <th style="padding:16px 24px; font-size:13px; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:0.5px;">Plan</th>
                        <th style="padding:16px 24px; font-size:13px; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:0.5px;">Status</th>
                        <th style="padding:16px 24px; font-size:13px; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:0.5px; text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:16px 24px; font-size:14px; color:#1e293b; font-weight:500;">
                                {{ $payment->paid_at ? $payment->paid_at->format('d M Y') : $payment->created_at->format('d M Y') }}
                            </td>
                            <td style="padding:16px 24px; font-size:14px; font-weight:700; color:#0f172a;">
                                ₹{{ number_format($payment->amount / 100, 2) }}
                            </td>
                            <td style="padding:16px 24px; font-size:14px; color:#475569;">
                                {{ $payment->plan->name ?? 'Unknown Plan' }} <span style="font-size:12px; color:#94a3b8; font-weight:600;">({{ ucfirst($payment->billing_cycle) }})</span>
                            </td>
                            <td style="padding:16px 24px;">
                                <span style="background:#dcfce7; color:#16a34a; font-size:12px; font-weight:700; padding:4px 8px; border-radius:4px;">Paid</span>
                            </td>
                            <td style="padding:16px 24px; text-align:right;">
                                <a href="{{ route('billing.invoice', $payment->id) }}" target="_blank" style="display:inline-flex; align-items:center; gap:6px; font-size:13px; font-weight:600; color:#2563eb; text-decoration:none;">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                    Invoice
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding:40px 24px; text-align:center;">
                                <div style="display:inline-flex; align-items:center; justify-content:center; width:48px; height:48px; background:#f1f5f9; border-radius:50%; margin-bottom:12px;">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" style="width:24px;height:24px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                </div>
                                <div style="font-size:16px; font-weight:600; color:#0f172a; margin-bottom:4px;">No billing history</div>
                                <div style="font-size:14px; color:#64748b;">You don't have any past payments yet.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
