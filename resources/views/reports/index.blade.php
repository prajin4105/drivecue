@extends('layouts.dashboard')

@section('title', 'Reports')
@section('page-title', 'Reports')
@section('page-subtitle', 'Track records, revenue, renewals and reminder performance.')

@push('styles')
<style>
    .rp{padding:28px 24px 48px;max-width:1500px;margin:auto}.rp-filter,.rp-card,.rp-kpi{background:#fff;border:1px solid #e2e8f0;border-radius:16px;box-shadow:0 1px 3px rgba(15,23,42,.06)}.rp-filter{display:flex;align-items:end;gap:12px;padding:16px;margin-bottom:20px;flex-wrap:wrap}.rp-field{display:grid;gap:6px}.rp-field label,.rp-kpi-label{font-size:11px;font-weight:800;letter-spacing:.07em;text-transform:uppercase;color:#64748b}.rp-field input{height:40px;border:1px solid #cbd5e1;border-radius:9px;padding:0 11px;font:600 13px inherit;color:#334155}.rp-button{height:40px;border:0;border-radius:9px;padding:0 16px;background:#2563eb;color:#fff;font:700 13px inherit;cursor:pointer}.rp-print{background:#fff;border:1px solid #cbd5e1;color:#334155}.rp-kpis{display:grid;grid-template-columns:repeat(4,minmax(180px,1fr));gap:14px;margin-bottom:20px}.rp-kpi{padding:19px;border-left:4px solid #2563eb}.rp-kpi.green{border-left-color:#16a34a}.rp-kpi.amber{border-left-color:#d97706}.rp-kpi.red{border-left-color:#dc2626}.rp-kpi-value{font-size:29px;font-weight:800;letter-spacing:-.04em;color:#0f172a;margin:8px 0 5px}.rp-kpi-meta{font-size:12px;color:#94a3b8;font-weight:600}.rp-grid{display:grid;grid-template-columns:minmax(0,1.55fr) minmax(280px,.75fr);gap:20px}.rp-card{overflow:hidden}.rp-card-head{padding:18px 20px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;gap:12px}.rp-card-title{font-size:15px;font-weight:800;color:#0f172a}.rp-card-sub{font-size:12px;color:#94a3b8;margin-top:3px}.rp-card-body{padding:18px 20px}.rp-badge{align-self:start;padding:5px 10px;border-radius:99px;background:#eff6ff;color:#1d4ed8;font-size:11px;font-weight:800}.rp-stack{display:grid;gap:20px}.rp-metrics{display:grid;grid-template-columns:repeat(2,1fr);gap:10px}.rp-metric{padding:13px;border-radius:10px;background:#f8fafc;border:1px solid #f1f5f9}.rp-metric strong{display:block;font-size:22px;color:#0f172a}.rp-metric span{font-size:11px;color:#64748b;font-weight:700}.rp-progress{height:8px;background:#f1f5f9;border-radius:99px;overflow:hidden;margin-top:14px}.rp-progress i{display:block;height:100%;background:#22c55e;border-radius:inherit}.rp-table{width:100%;border-collapse:collapse}.rp-table th{text-align:left;color:#94a3b8;font-size:11px;text-transform:uppercase;letter-spacing:.06em;padding:0 14px 11px}.rp-table td{padding:13px 14px;border-top:1px solid #f1f5f9;font-size:13px;color:#475569;font-weight:600}.rp-table strong{color:#0f172a}.rp-status{font-size:11px;font-weight:800;padding:4px 8px;border-radius:99px}.rp-status.active{color:#166534;background:#dcfce7}.rp-status.soon{color:#92400e;background:#fef3c7}.rp-status.expired{color:#991b1b;background:#fee2e2}@media(max-width:900px){.rp{padding:20px 16px}.rp-kpis{grid-template-columns:repeat(2,1fr)}.rp-grid{grid-template-columns:1fr}}@media(max-width:520px){.rp-kpis{grid-template-columns:1fr}.rp-table-wrap{overflow:auto}.rp-table{min-width:650px}}@media print{.psh-sidebar,.psh-topbar,.rp-filter{display:none!important}.rp{padding:0}.rp-card,.rp-kpi{box-shadow:none}}
</style>
@endpush

@section('content')
<div class="rp">
    <form class="rp-filter" method="GET" action="{{ route('reports.index') }}" id="report-filter-form">
        <input type="hidden" name="from" id="from_date" value="{{ $from->toDateString() }}">
        <input type="hidden" name="to" id="to_date" value="{{ $to->toDateString() }}">
        
        <div class="rp-field">
            <label for="reportrange">Date Range</label>
            <div id="reportrange" style="background: #fff; cursor: pointer; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 9px; min-width: 260px; font-weight: 600; font-size: 13px; color: #334155; display: flex; align-items: center; justify-content: space-between;">
                <span></span>
                <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 2;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            </div>
        </div>
        
        <button class="rp-button" type="submit">Apply report</button>
        <button class="rp-button rp-print" type="button" onclick="window.print()">Print report</button>
    </form>

    <div class="psh-kpis" style="margin-bottom: 24px;">
        <div class="psh-kpi-card is-primary">
            <div class="psh-kpi-top">
                <div class="psh-kpi-icon"><svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5z"/></svg></div>
                <div class="psh-kpi-label">Records added</div>
            </div>
            <div class="psh-kpi-value">{{ number_format($totalAdded) }}</div>
            <div class="psh-kpi-meta">During selected period</div>
        </div>
        <div class="psh-kpi-card is-success">
            <div class="psh-kpi-top">
                <div class="psh-kpi-icon"><svg viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
                <div class="psh-kpi-label">PUC revenue</div>
            </div>
            <div class="psh-kpi-value">₹{{ number_format($totalRevenue, 0) }}</div>
            <div class="psh-kpi-meta">From new vehicle records</div>
        </div>
        <div class="psh-kpi-card is-warning">
            <div class="psh-kpi-top">
                <div class="psh-kpi-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
                <div class="psh-kpi-label">Renewal pipeline</div>
            </div>
            <div class="psh-kpi-value">{{ number_format($expiringInRange) }}</div>
            <div class="psh-kpi-meta">Expiring in this date range</div>
        </div>
        <div class="psh-kpi-card is-danger">
            <div class="psh-kpi-top">
                <div class="psh-kpi-icon"><svg viewBox="0 0 24 24"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div>
                <div class="psh-kpi-label">Already expired</div>
            </div>
            <div class="psh-kpi-value">{{ number_format($expiredInRange) }}</div>
            <div class="psh-kpi-meta">Need follow-up attention</div>
        </div>
    </div>

    <div class="rp-grid">
        <div class="rp-stack">
            <section class="rp-card"><div class="rp-card-head"><div><div class="rp-card-title">Records and expiry trend</div><div class="rp-card-sub">Daily activity for {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}</div></div><span class="rp-badge">Live data</span></div><div class="rp-card-body"><div id="trendChart"></div></div></section>
            <section class="rp-card"><div class="rp-card-head"><div><div class="rp-card-title">Expiry follow-up list</div><div class="rp-card-sub">Vehicles whose PUC expires in the selected period.</div></div><span class="rp-badge">{{ $followups->total() }} shown</span></div><div class="rp-card-body rp-table-wrap">
                @if($followups->isEmpty()) <p style="color:#94a3b8;font-size:13px;font-weight:600">No expiry records found for this period.</p>
                @else 
                <table class="rp-table"><thead><tr><th>Customer</th><th>Vehicle</th><th>Type</th><th>Expiry</th><th>Status</th></tr></thead><tbody>@foreach($followups as $record) @php($days = now()->startOfDay()->diffInDays($record->expiry_date, false)) <tr><td><strong>{{ $record->customer_name ?: '—' }}</strong><br><small>{{ $record->customer_mobile }}</small></td><td>{{ $record->vehicle_number }}</td><td>{{ $record->vehicle_type }}</td><td>{{ $record->expiry_date->format('d M Y') }}</td><td><span class="rp-status {{ $days < 0 ? 'expired' : ($days <= 7 ? 'soon' : 'active') }}">{{ $days < 0 ? 'Expired' : ($days === 0 ? 'Due today' : ($days <= 7 ? 'Due soon' : 'Active')) }}</span></td></tr> @endforeach</tbody></table> 
                @include('partials.pagination', ['paginator' => $followups])
                @endif 
            </div></section>
        </div>
        <aside class="rp-stack">
            <section class="rp-card"><div class="rp-card-head"><div><div class="rp-card-title">WhatsApp delivery health</div><div class="rp-card-sub">Message performance in this period.</div></div></div><div class="rp-card-body"><div class="rp-metrics"><div class="rp-metric"><strong>{{ $messageSent }}</strong><span>Sent</span></div><div class="rp-metric"><strong>{{ $messageFailed }}</strong><span>Failed</span></div><div class="rp-metric"><strong>{{ $messagePending }}</strong><span>Pending</span></div><div class="rp-metric"><strong>{{ $deliveryRate }}%</strong><span>Delivery rate</span></div></div><div class="rp-progress"><i style="width:{{ $deliveryRate }}%"></i></div></div></section>
            <section class="rp-card"><div class="rp-card-head"><div><div class="rp-card-title">Vehicle type summary</div><div class="rp-card-sub">New records by vehicle category.</div></div></div><div class="rp-card-body"><div id="vehicleChart"></div><p style="margin-top:12px;color:#64748b;font-size:12px;font-weight:600">Top category: <strong style="color:#0f172a">{{ $topType['label'] }}</strong> · {{ $topType['total'] }} records</p></div></section>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const labels = @json($trendLabels), records = @json($trendRecords), expiries = @json($trendExpiring);
    new ApexCharts(document.querySelector('#trendChart'), {chart:{type:'area',height:290,toolbar:{show:false},fontFamily:'Inter, sans-serif'},series:[{name:'New records',data:records},{name:'Expiring',data:expiries}],colors:['#2563eb','#f59e0b'],stroke:{curve:'smooth',width:2.5},fill:{type:'gradient',gradient:{opacityFrom:.24,opacityTo:.02}},dataLabels:{enabled:false},xaxis:{categories:labels,axisBorder:{show:false},axisTicks:{show:false}},yaxis:{min:0},grid:{borderColor:'#f1f5f9',strokeDashArray:4},legend:{position:'top',horizontalAlign:'right'}}).render();
    new ApexCharts(document.querySelector('#vehicleChart'), {chart:{type:'bar',height:245,toolbar:{show:false},fontFamily:'Inter, sans-serif'},series:[{name:'Records',data:@json($vehicleSummary->pluck('total')->values())}],colors:['#2563eb','#16a34a','#d97706','#dc2626','#7c3aed','#0891b2'],plotOptions:{bar:{horizontal:true,borderRadius:5,distributed:true}},dataLabels:{enabled:true},xaxis:{categories:@json($vehicleSummary->pluck('label')->values()),min:0},legend:{show:false},grid:{borderColor:'#f1f5f9'}}).render();
});
</script>
@endpush

@push('footer-scripts')
<script>
$(function() {
    var start = moment("{{ $from->toDateString() }}");
    var end = moment("{{ $to->toDateString() }}");
    var isInitialized = false;

    function cb(start, end) {
        $('#reportrange span').html(start.format('MMM D, YYYY') + ' - ' + end.format('MMM D, YYYY'));
        $('#from_date').val(start.format('YYYY-MM-DD'));
        $('#to_date').val(end.format('YYYY-MM-DD'));

        if (isInitialized) {
            var url = new URL(window.location.href);
            url.searchParams.set('from', start.format('YYYY-MM-DD'));
            url.searchParams.set('to', end.format('YYYY-MM-DD'));
            url.searchParams.set('page', '1');
            window.location.href = url.toString();
        }
    }

    $('#reportrange').daterangepicker({
        startDate: start,
        endDate: end,
        autoApply: true,
        ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'This week': [moment().startOf('week'), moment().endOf('week')],
           'Last week': [moment().subtract(1, 'week').startOf('week'), moment().subtract(1, 'week').endOf('week')],
           'Last 7 days': [moment().subtract(6, 'days'), moment()],
           'Last 14 days': [moment().subtract(13, 'days'), moment()],
           'This month': [moment().startOf('month'), moment().endOf('month')],
           'Last 30 days': [moment().subtract(29, 'days'), moment()],
           'Last month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
           'This year': [moment().startOf('year'), moment().endOf('year')]
        }
    }, cb);

    cb(start, end);
    isInitialized = true;
});
</script>
@endpush
