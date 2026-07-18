<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $payment->razorpay_order_id }}</title>
    <style>
        body { font-family: 'Inter', sans-serif; margin: 0; padding: 40px; color: #334155; line-height: 1.5; background: #f8fafc; }
        .invoice-container { max-width: 800px; margin: 0 auto; background: #fff; padding: 48px; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 48px; border-bottom: 2px solid #f1f5f9; padding-bottom: 24px; }
        .logo { font-size: 24px; font-weight: 800; color: #2563eb; display: flex; align-items: center; gap: 8px; }
        .logo svg { width: 28px; height: 28px; }
        .invoice-title { font-size: 32px; font-weight: 700; color: #0f172a; margin: 0; text-transform: uppercase; letter-spacing: 1px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px; }
        .info-section h3 { font-size: 14px; font-weight: 700; color: #64748b; text-transform: uppercase; margin: 0 0 12px 0; letter-spacing: 0.5px; }
        .info-section p { margin: 0 0 4px 0; font-size: 15px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 32px; }
        .table th { padding: 12px 16px; background: #f8fafc; font-size: 13px; font-weight: 700; color: #64748b; text-transform: uppercase; text-align: left; border-bottom: 2px solid #e2e8f0; }
        .table th.right { text-align: right; }
        .table td { padding: 16px; border-bottom: 1px solid #e2e8f0; font-size: 15px; }
        .table td.right { text-align: right; font-weight: 700; color: #0f172a; }
        .totals { width: 100%; max-width: 320px; margin-left: auto; }
        .totals-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f1f5f9; font-size: 15px; }
        .totals-row.grand { border-bottom: none; font-size: 20px; font-weight: 700; color: #0f172a; border-top: 2px solid #e2e8f0; padding-top: 16px; margin-top: 8px; }
        .footer { margin-top: 64px; text-align: center; color: #94a3b8; font-size: 13px; border-top: 1px solid #f1f5f9; padding-top: 24px; }
        
        @media print {
            body { background: #fff; padding: 0; }
            .invoice-container { box-shadow: none; padding: 0; max-width: 100%; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>
    <div style="max-width: 800px; margin: 0 auto 16px auto; text-align: right;">
        <button class="print-btn" onclick="window.print()" style="background: #2563eb; color: #fff; border: none; padding: 10px 20px; font-size: 14px; font-weight: 600; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-family: inherit;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Print / Save as PDF
        </button>
    </div>

    <div class="invoice-container">
        <div class="header">
            <div>
                <div class="logo">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                    DriveCue
                </div>
                <div style="margin-top: 12px; color: #64748b; font-size: 14px;">
                    <p style="margin:0 0 4px 0;">123 Business Avenue, Suite 100</p>
                    <p style="margin:0 0 4px 0;">Tech City, TC 400012</p>
                    <p style="margin:0;">support@drivecue.com</p>
                </div>
            </div>
            <div style="text-align: right;">
                <h1 class="invoice-title">Invoice</h1>
                <p style="color: #64748b; font-size: 15px; margin: 8px 0 0 0; font-weight: 500;">#{{ $payment->razorpay_order_id }}</p>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-section">
                <h3>Billed To</h3>
                <p style="font-weight: 700; color: #0f172a; font-size: 16px;">{{ $payment->user->name }}</p>
                <p>{{ $payment->user->email }}</p>
                <p>{{ $payment->user->mobile }}</p>
            </div>
            <div class="info-section">
                <h3>Payment Details</h3>
                <p><span style="color:#64748b; display:inline-block; width:100px;">Date:</span> <strong>{{ $payment->paid_at ? $payment->paid_at->format('d M Y') : $payment->created_at->format('d M Y') }}</strong></p>
                <p><span style="color:#64748b; display:inline-block; width:100px;">Payment ID:</span> <strong>{{ $payment->razorpay_payment_id ?? 'N/A' }}</strong></p>
                <p><span style="color:#64748b; display:inline-block; width:100px;">Status:</span> <span style="color:#16a34a; font-weight:700;">PAID</span></p>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div style="font-weight:700; color:#0f172a; margin-bottom:4px;">{{ $payment->plan->name ?? 'Subscription Plan' }}</div>
                        <div style="color:#64748b; font-size:13px;">{{ ucfirst($payment->billing_cycle) }} Subscription charge</div>
                    </td>
                    <td class="right">₹{{ number_format($payment->amount / 100, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="totals">
            <div class="totals-row">
                <span style="color:#64748b;">Subtotal</span>
                <span>₹{{ number_format($payment->amount / 100, 2) }}</span>
            </div>
            <div class="totals-row grand">
                <span>Total Paid</span>
                <span>₹{{ number_format($payment->amount / 100, 2) }}</span>
            </div>
        </div>

        <div class="footer">
            <p>Thank you for subscribing to DriveCue. If you have any questions about this invoice, please contact support.</p>
            <p style="margin-top:8px;">&copy; {{ date('Y') }} DriveCue. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
