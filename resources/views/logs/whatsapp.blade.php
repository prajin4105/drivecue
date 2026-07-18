@extends('layouts.dashboard')

@section('title', 'WhatsApp Logs')

@section('content')
<style>
.logs-toolbar-card { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 24px; }
.logs-toolbar-card > div { display: flex; flex-direction: column; gap: 4px; }
.logs-toolbar-card > div strong { font-size: 1.25rem; font-weight: 820; color: #0B1020; letter-spacing: -0.03em; }
.logs-toolbar-card > div span { font-size: .88rem; color: #64748B; font-weight: 700; }

.logs-filter-card { display: grid; grid-template-columns: minmax(220px, 1fr) 140px 140px 140px auto; gap: 10px; padding: 10px; border-radius: 18px; background: #fff; box-shadow: 0 4px 12px rgba(11,16,32,.03); border: 1px solid var(--cust-border); margin-bottom: 24px; align-items: center; }
.logs-search-wrap, .logs-select-wrap { position: relative; display: flex; align-items: center; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; transition: .18s ease; }
.logs-search-wrap:focus-within, .logs-select-wrap:focus-within { background: #fff; border-color: var(--cust-blue); box-shadow: 0 0 0 3px rgba(37,99,235,.08); }
.logs-search-wrap svg, .logs-select-wrap svg { position: absolute; left: 14px; width: 16px; height: 16px; color: #94A3B8; stroke: currentColor; stroke-width: 2.5; fill: none; pointer-events: none; }
.logs-search-wrap input, .logs-select-wrap select, .logs-select-wrap input[type="date"] { width: 100%; border: none; background: transparent; padding: 0 14px 0 38px; height: 42px; font-size: .88rem; font-weight: 760; color: #0B1020; outline: none; appearance: none; }
.logs-select-wrap select { cursor: pointer; padding-right: 32px; }
.logs-select-wrap::after { content: ""; position: absolute; right: 14px; width: 8px; height: 8px; border-right: 2px solid #94A3B8; border-bottom: 2px solid #94A3B8; transform: rotate(45deg); pointer-events: none; }
.logs-select-wrap input[type="date"]::-webkit-calendar-picker-indicator { cursor: pointer; opacity: 0; width: 100%; position: absolute; }
.logs-filter-card .logs-btn { height: 44px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 18px; border-radius: 12px; font-size: .88rem; font-weight: 820; border: none; cursor: pointer; transition: .18s ease; }
.logs-btn-primary { background: var(--cust-blue); color: #fff; box-shadow: 0 6px 16px rgba(37,99,235,.18); }
.logs-btn-primary:hover { background: var(--cust-blue-dark); transform: translateY(-1px); }

.logs-table-wrapper { background: #fff; border-radius: 20px; box-shadow: 0 6px 20px rgba(11,16,32,.03); border: 1px solid var(--cust-border); overflow: hidden; margin-bottom: 24px; }
.logs-table { width: 100%; border-collapse: separate; border-spacing: 0; text-align: left; }
.logs-table th { padding: 14px 20px; font-size: .75rem; font-weight: 920; text-transform: uppercase; letter-spacing: .08em; color: #64748B; background: #F8FAFC; border-bottom: 1px solid var(--cust-border); }
.logs-table td { padding: 16px 20px; border-bottom: 1px solid var(--cust-border); vertical-align: middle; }
.logs-table tr:last-child td { border-bottom: none; }
.logs-table tbody tr { transition: .15s ease; }
.logs-table tbody tr:hover { background: #F8FAFC; }

.log-status-pill { display: inline-flex; align-items: center; justify-content: center; min-height: 28px; padding: 0 10px; border-radius: 999px; border: 1px solid; font-size: .75rem; line-height: 1; font-weight: 700; white-space: nowrap; gap: 6px;}
.log-status-pill::before { content: ""; width: 6px; height: 6px; border-radius: 999px; background: currentColor; }
.log-status-sent { color: #166534; background: #F0FDF4; border-color: #BBF7D0; }
.log-status-failed { color: #B91C1C; background: #FEF2F2; border-color: #FECACA; }
.log-status-pending { color: #A16207; background: #FFFBEB; border-color: #FDE68A; }

@media (max-width: 1024px) {
    .logs-filter-card { grid-template-columns: 1fr 1fr; }
    .logs-filter-card .logs-search-wrap { grid-column: span 2; }
}
@media (max-width: 640px) {
    .logs-filter-card { grid-template-columns: 1fr; }
    .logs-filter-card .logs-search-wrap { grid-column: span 1; }
}
</style>

<div style="padding-bottom: 40px;">
    <div class="logs-toolbar-card">
        <div>
            <strong>WhatsApp Logs</strong>
            <span>View your history of WhatsApp reminders sent to customers.</span>
        </div>
    </div>

    <form class="logs-filter-card" method="get" action="{{ route('whatsapp-logs.index') }}">
        <div class="logs-search-wrap">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>
            <input name="search" placeholder="Search by mobile or vehicle..." value="{{ request('search') }}">
        </div>
        <div class="logs-select-wrap">
            <svg viewBox="0 0 24 24"><path d="M3 6h18"></path><path d="M7 12h10"></path><path d="M10 18h4"></path></svg>
            <select name="status" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            </select>
        </div>
        <input type="hidden" name="from" id="from_date" value="{{ request('from', $from->format('Y-m-d')) }}">
        <input type="hidden" name="to" id="to_date" value="{{ request('to', $to->format('Y-m-d')) }}">

        <div class="logs-select-wrap" style="grid-column: span 2; display: flex; align-items: center; justify-content: space-between; padding-right: 14px; cursor: pointer;" id="reportrange">
            <svg viewBox="0 0 24 24" style="position: absolute; left: 14px; width: 16px; height: 16px; color: #94A3B8; stroke: currentColor; fill: none; stroke-width: 2.5;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            <span style="padding-left: 38px; font-size: .88rem; font-weight: 760; color: #0B1020;"></span>
            <svg viewBox="0 0 24 24" style="width: 14px; height: 14px; stroke: #94A3B8; fill: none; stroke-width: 2.5;"><polyline points="6 9 12 15 18 9"></polyline></svg>
        </div>
        <button type="submit" class="logs-btn logs-btn-primary">Filter</button>
    </form>

    <div class="logs-table-wrapper">
        <div style="overflow-x: auto;">
            <table class="logs-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Customer / Vehicle</th>
                        <th>Mobile</th>
                        <th>Message Type</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>
                                <div style="font-weight: 760; color: #0B1020; font-size: .9rem;">{{ $log->sent_at ? $log->sent_at->format('d M, Y') : $log->created_at->format('d M, Y') }}</div>
                                <div style="font-size: .75rem; color: #64748B; font-weight: 700;">{{ $log->sent_at ? $log->sent_at->format('h:i A') : $log->created_at->format('h:i A') }}</div>
                            </td>
                            <td>
                                @if($log->vehicleRecord)
                                    <div style="font-weight: 760; color: #0B1020; font-size: .9rem;">{{ $log->vehicleRecord->customer_name }}</div>
                                    <div style="font-size: .75rem; color: #64748B; font-weight: 700; margin-top: 2px;">{{ $log->vehicleRecord->vehicle_number }}</div>
                                @else
                                    <span style="color: #94A3B8; font-style: italic;">Unknown Vehicle</span>
                                @endif
                            </td>
                            <td>
                                <div style="font-weight: 760; color: #0B1020; font-size: .9rem;">{{ $log->customer_mobile }}</div>
                            </td>
                            <td>
                                <div style="display:inline-block; padding: 4px 10px; background: #F1F5F9; border-radius: 6px; font-size: .8rem; font-weight: 760; color: #334155;">
                                    {{ ucfirst($log->message_type) }} <span style="opacity: 0.5;">•</span> {{ ucfirst(str_replace('_', ' ', $log->reminder_stage)) }}
                                </div>
                            </td>
                            <td>
                                <span class="log-status-pill log-status-{{ strtolower($log->status) }}">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px 20px;">
                                <svg viewBox="0 0 24 24" style="width: 48px; height: 48px; color: #94A3B8; stroke: currentColor; stroke-width: 1.5; fill: none; margin: 0 auto 12px; display: block;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                <strong style="display: block; font-size: 1rem; color: #0B1020; font-weight: 760;">No WhatsApp logs found</strong>
                                <p style="color: #64748B; font-size: .88rem; font-weight: 700; margin-top: 4px;">There are no messages matching your filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if ($logs->total() > 0)
        <div style="margin-top: 16px; border: 1px solid var(--puc-border); border-radius: 16px; overflow: hidden; box-shadow: 0 12px 30px rgba(11,16,32,.04);">
            @include('partials.pagination', ['paginator' => $logs])
        </div>
    @endif
</div>
@endsection

@push('footer-scripts')
<script>
$(function() {
    var start = moment("{{ request('from', $from->format('Y-m-d')) }}");
    var end = moment("{{ request('to', $to->format('Y-m-d')) }}");
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
