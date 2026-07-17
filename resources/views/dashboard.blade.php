@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
@inject('waService', 'App\Services\WhatsAppService')

@php
if (!function_exists('reminder_badge')) {
    function reminder_badge(string $expiryDate): array {
        $days = (int) \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($expiryDate), false);
        if ($days < 0) return ['Expired', 'danger'];
        if ($days === 0) return ['Today', 'warning'];
        if ($days <= 7) return ['Due Soon', 'warning'];
        return ['Active', 'success'];
    }
}

if (!function_exists('dash_status_text')) {
    function dash_status_text(string $expiryDate): array {
        $days = (int) \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($expiryDate), false);
        if ($days < 0) return ['Expired', 'danger', abs($days) . ' days overdue'];
        if ($days === 0) return ['Today', 'warning', 'Renew today'];
        if ($days <= 7) return ['Due Soon', 'warning', $days . ' days left'];
        return ['Active', 'success', $days . ' days left'];
    }
}

if (!function_exists('dash_safe_date')) {
    function dash_safe_date(?string $date): string {
        if (!$date) return '-';
        return \Carbon\Carbon::parse($date)->format('d-m-Y');
    }
}

if (!function_exists('dash_percent')) {
    function dash_percent(int $value, int $max): int {
        if ($max <= 0) return 0;
        return max(0, min(100, (int) round(($value / $max) * 100)));
    }
}

if (!function_exists('dash_initial')) {
    function dash_initial(?string $value): string {
        $value = trim((string) $value);
        return strtoupper(substr($value !== '' ? $value : 'C', 0, 1));
    }
}
@endphp

<style>
    /* ===== DASHBOARD STYLE OVERRIDES ===== */
    .ds-wrap { max-width: 100%; padding: 0 0 28px; }
    .ds-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; margin-bottom: 28px; flex-wrap: wrap; }
    .ds-header-left { flex: 1; min-width: 200px; }
    .ds-breadcrumb { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: var(--blue); letter-spacing: .04em; text-transform: uppercase; margin-bottom: 10px; }
    .ds-breadcrumb::before { content: ''; display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: var(--blue); }
    .ds-page-title { font-size: clamp(22px, 3vw, 32px); font-weight: 700; color: var(--black); letter-spacing: -.03em; line-height: 1.1; }
    .ds-page-sub { margin-top: 6px; font-size: 14px; color: var(--muted); font-weight: 500; line-height: 1.6; }
    .ds-header-right { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    
    .ds-date-chip { display: inline-flex; align-items: center; gap: 7px; height: 40px; padding: 0 14px; border: 1px solid var(--line); border-radius: 12px; background: #fff; font-size: 13px; font-weight: 600; color: var(--text); box-shadow: 0 1px 2px rgba(15,23,42,.04); white-space: nowrap; }
    .ds-date-chip svg { width: 15px; height: 15px; stroke: var(--blue); fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    
    .ds-wa-btn { display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 18px; border: none; border-radius: 12px; background: #16A34A; color: #fff; font-family: inherit; font-size: 13px; font-weight: 700; cursor: pointer; box-shadow: 0 2px 8px rgba(22,163,74,.28); transition: background .16s, transform .14s; white-space: nowrap; }
    .ds-wa-btn:hover { background: #15803D; transform: translateY(-1px); }
    .ds-wa-btn:active { transform: translateY(0); }
    .ds-wa-btn svg { width: 16px; height: 16px; flex: 0 0 auto; }
    .ds-wa-badge { display: inline-flex; align-items: center; justify-content: center; min-width: 20px; height: 20px; padding: 0 5px; border-radius: 999px; background: rgba(255,255,255,.22); font-size: 11px; font-weight: 700; }
    
    .env-warn { background: #fff7ed; border: 1px solid #fed7aa; color: #c2410c; padding: 10px 16px; border-radius: 12px; font-size: 13px; font-weight: 600; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
    
    .ds-alert { display: flex; align-items: flex-start; gap: 14px; padding: 16px 20px; border-radius: 16px; border: 1px solid; margin-bottom: 24px; }
    .ds-alert.success { background: #F0FDF4; border-color: #ABEFC6; }
    .ds-alert.warning { background: #FFFBEB; border-color: #FDE68A; }
    .ds-alert.danger { background: #FFF1F2; border-color: #FECACA; }
    .ds-alert.info { background: #EFF6FF; border-color: var(--blue-bd); }
    
    .ds-alert-icon { width: 36px; height: 36px; min-width: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex: 0 0 auto; }
    .ds-alert.success .ds-alert-icon { background: #DCFCE7; }
    .ds-alert.warning .ds-alert-icon { background: #FEF3C7; }
    .ds-alert.danger .ds-alert-icon { background: #FFE4E6; }
    .ds-alert.info .ds-alert-icon { background: var(--blue-lt); }
    
    .ds-alert-body { flex: 1; min-width: 0; }
    .ds-alert-title { font-size: 14px; font-weight: 700; line-height: 1.3; }
    .ds-alert.success .ds-alert-title { color: #15803D; }
    .ds-alert.warning .ds-alert-title { color: #B45309; }
    .ds-alert.danger .ds-alert-title { color: #B91C1C; }
    .ds-alert.info .ds-alert-title { color: var(--blue-dark); }
    .ds-alert-text { font-size: 13px; color: var(--text); font-weight: 500; margin-top: 3px; line-height: 1.5; }
    
    .ds-kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 20px; }
    .ds-kpi { background: #fff; border: 1px solid var(--line); border-radius: 16px; padding: 20px; box-shadow: 0 1px 3px rgba(15,23,42,.07); position: relative; overflow: hidden; transition: box-shadow .16s, transform .14s; }
    .ds-kpi:hover { box-shadow: 0 10px 24px rgba(15,23,42,.07); transform: translateY(-2px); }
    .ds-kpi::after { content: ''; position: absolute; inset: 0 auto 0 0; width: 3px; border-radius: 2px 0 0 2px; }
    .ds-kpi.default::after { background: var(--blue); }
    .ds-kpi.success::after { background: #22C55E; }
    .ds-kpi.warning::after { background: #F59E0B; }
    .ds-kpi.danger::after { background: #EF4444; }
    
    .ds-kpi-icon { width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 14px; }
    .ds-kpi.default .ds-kpi-icon { background: var(--blue-lt); color: var(--blue); }
    .ds-kpi.success .ds-kpi-icon { background: #F0FDF4; color: #22C55E; }
    .ds-kpi.warning .ds-kpi-icon { background: #FFFBEB; color: #F59E0B; }
    .ds-kpi.danger .ds-kpi-icon { background: #FFF1F2; color: #EF4444; }
    .ds-kpi-icon svg { width: 20px; height: 20px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    
    .ds-kpi-label { font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--muted); margin-bottom: 6px; }
    .ds-kpi-value { font-size: 30px; font-weight: 700; letter-spacing: -.04em; line-height: 1; margin-bottom: 6px; color: var(--black); }
    .ds-kpi-meta { font-size: 12px; color: var(--muted); font-weight: 500; line-height: 1.4; }
    
    .ds-plan-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 20px; }
    .ds-plan-card { background: #fff; border: 1px solid var(--line); border-radius: 16px; padding: 20px; box-shadow: 0 1px 3px rgba(15,23,42,.07); }
    .ds-plan-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 18px; }
    .ds-plan-name { font-size: 16px; font-weight: 700; color: var(--black); }
    .ds-plan-sub { font-size: 12.5px; color: var(--muted); font-weight: 500; margin-top: 3px; }
    
    .ds-status-pill { display: inline-flex; align-items: center; gap: 6px; height: 28px; padding: 0 10px; border-radius: 999px; font-size: 11.5px; font-weight: 700; white-space: nowrap; }
    .ds-status-pill::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .ds-status-pill.active { background: #F0FDF4; color: #15803D; border: 1px solid #ABEFC6; }
    .ds-status-pill.trial { background: #FFFBEB; color: #B45309; border: 1px solid #FDE68A; }
    
    .ds-usage-meter { margin-bottom: 12px; }
    .ds-usage-meter:last-child { margin-bottom: 0; }
    .ds-usage-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
    .ds-usage-label { font-size: 12px; color: var(--text); font-weight: 600; }
    .ds-usage-value { font-size: 12px; color: var(--black); font-weight: 700; }
    
    .ds-track { height: 6px; border-radius: 999px; background: var(--line); overflow: hidden; }
    .ds-fill { height: 100%; border-radius: 999px; background: linear-gradient(90deg, var(--blue), var(--blue-dark)); min-width: 2px; transition: width .6s ease; }
    
    .ds-workload { display: grid; gap: 10px; }
    .ds-wl-row { display: flex; align-items: center; gap: 12px; padding: 12px 14px; background: var(--bg); border: 1px solid var(--line); border-radius: 8px; }
    .ds-wl-label { font-size: 12.5px; color: var(--text); font-weight: 600; flex: 1; }
    .ds-wl-count { font-size: 15px; font-weight: 700; color: var(--black); min-width: 30px; text-align: right; }
    .ds-wl-bar { flex: 1; height: 5px; border-radius: 999px; background: var(--line); overflow: hidden; }
    .ds-wl-fill { height: 100%; border-radius: 999px; background: var(--blue); }
    
    .ds-main-grid { display: grid; grid-template-columns: minmax(0, 1.5fr) minmax(300px, .5fr); gap: 20px; align-items: start; }
    .ds-aside { display: grid; gap: 16px; }
    
    .ds-card { background: #fff; border: 1px solid var(--line); border-radius: 16px; box-shadow: 0 1px 3px rgba(15,23,42,.07); overflow: hidden; }
    .ds-card-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 18px 20px; border-bottom: 1px solid var(--line); }
    .ds-card-title { font-size: 15px; font-weight: 700; color: var(--black); }
    .ds-card-sub { font-size: 12.5px; color: var(--muted); font-weight: 500; margin-top: 3px; }
    .ds-card-body { padding: 18px 20px; }
    
    .ds-chip { display: inline-flex; align-items: center; gap: 5px; padding: 5px 10px; border-radius: 999px; font-size: 11.5px; font-weight: 700; background: var(--blue-lt); color: var(--blue); border: 1px solid var(--blue-bd); white-space: nowrap; }
    .ds-chip::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
    
    .ds-table-wrap { overflow-x: auto; }
    .ds-table { width: 100%; border-collapse: collapse; min-width: 580px; }
    .ds-table th { text-align: left; padding: 0 14px 12px; font-size: 11px; font-weight: 700; letter-spacing: .07em; text-transform: uppercase; color: var(--muted); border-bottom: 1px solid var(--line); white-space: nowrap; }
    .ds-table td { padding: 14px; border-bottom: 1px solid var(--bg); font-size: 13px; font-weight: 600; color: var(--text); vertical-align: middle; white-space: nowrap; }
    .ds-table tr:last-child td { border-bottom: 0; }
    .ds-table tr:hover td { background: var(--bg); }
    
    .ds-customer { display: flex; align-items: center; gap: 10px; min-width: 180px; }
    .ds-avatar { width: 36px; height: 36px; min-width: 36px; border-radius: 10px; background: var(--blue-lt); border: 1px solid var(--blue-bd); color: var(--blue); display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; text-transform: uppercase; flex: 0 0 auto; }
    .ds-cust-name { display: block; font-size: 13px; font-weight: 700; color: var(--black); max-width: 180px; overflow: hidden; text-overflow: ellipsis; }
    .ds-cust-type { display: block; font-size: 11.5px; color: var(--muted); font-weight: 600; margin-top: 1px; }
    
    .ds-vehicle { display: inline-flex; padding: 4px 10px; border-radius: 999px; background: var(--bg); border: 1px solid var(--line); font-size: 12px; font-weight: 700; color: var(--text); letter-spacing: .01em; }
    
    .ds-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 9px; border-radius: 999px; font-size: 11.5px; font-weight: 700; border: 1px solid transparent; }
    .ds-badge::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
    .ds-badge.success { background: #F0FDF4; color: #15803D; border-color: #ABEFC6; }
    .ds-badge.warning { background: #FFFBEB; color: #B45309; border-color: #FDE68A; }
    .ds-badge.danger { background: #FFF1F2; color: #B91C1C; border-color: #FECACA; }
    
    .ds-metrics { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 14px; }
    .ds-metric { padding: 12px; background: var(--bg); border: 1px solid var(--line); border-radius: 8px; }
    .ds-metric-val { font-size: 22px; font-weight: 700; color: var(--black); letter-spacing: -.03em; line-height: 1; }
    .ds-metric-lbl { font-size: 11.5px; color: var(--muted); font-weight: 600; margin-top: 4px; }
    
    .ds-chart { display: grid; gap: 10px; }
    .ds-bar-row { display: grid; grid-template-columns: 32px 1fr 28px; gap: 8px; align-items: center; }
    .ds-bar-lbl { font-size: 11.5px; color: var(--muted); font-weight: 700; }
    .ds-bar-val { font-size: 12px; font-weight: 700; color: var(--text); text-align: right; }
    .ds-bar-track { height: 8px; border-radius: 999px; background: var(--line); overflow: hidden; }
    .ds-bar-fill { height: 100%; border-radius: 999px; background: var(--blue); min-width: 2px; }
    
    .ds-type-list { display: grid; gap: 8px; }
    .ds-type-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 13px; background: var(--bg); border: 1px solid var(--line); border-radius: 8px; }
    .ds-type-row span { font-size: 13px; color: var(--text); font-weight: 600; }
    .ds-type-row b { font-size: 14px; color: var(--black); font-weight: 700; }
    
    .ds-empty { text-align: center; padding: 28px 16px; border-radius: 12px; background: var(--bg); border: 1px dashed var(--line); }
    .ds-empty-icon { width: 44px; height: 44px; border-radius: 8px; background: var(--blue-lt); border: 1px solid var(--blue-bd); display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; }
    .ds-empty-icon svg { width: 22px; height: 22px; stroke: var(--blue); fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .ds-empty strong { display: block; font-size: 14px; font-weight: 700; color: var(--text); margin-bottom: 4px; }
    .ds-empty span { font-size: 13px; color: var(--muted); font-weight: 500; line-height: 1.5; }
    
    /* Modal Styles */
    #dsOverlay { display: none; position: fixed; inset: 0; background: rgba(15,23,42,.45); backdrop-filter: blur(4px); z-index: 9990; align-items: flex-start; justify-content: center; overflow-y: auto; padding: 24px 16px; }
    #dsOverlay.open { display: flex; }
    #dsModal { background: #fff; border-radius: 20px; border: 1px solid var(--line); width: min(880px, 100%); margin: auto; overflow: hidden; box-shadow: 0 24px 64px rgba(15,23,42,.16); animation: dsIn .2s ease; }
    @keyframes dsIn { from { opacity: 0; transform: translateY(12px) scale(.97); } to { opacity: 1; transform: none; } }
    
    .modal-header { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 20px 24px; border-bottom: 1px solid var(--line); }
    .modal-icon { width: 44px; height: 44px; min-width: 44px; border-radius: 12px; background: #F0FDF4; border: 1px solid #ABEFC6; display: flex; align-items: center; justify-content: center; flex: 0 0 auto; }
    .modal-title { font-size: 16px; font-weight: 700; color: var(--black); }
    .modal-sub { font-size: 12.5px; color: var(--muted); font-weight: 500; margin-top: 2px; }
    .modal-close { width: 34px; height: 34px; border-radius: 8px; border: 1px solid var(--line); background: var(--bg); cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--muted); font-size: 18px; line-height: 1; transition: background .14s, color .14s; }
    .modal-close:hover { background: var(--line); color: var(--text); }
    
    .modal-tabs { display: flex; border-bottom: 1px solid var(--line); padding: 0 24px; }
    .modal-tab { padding: 14px 0; margin-right: 24px; font-size: 13px; font-weight: 700; color: var(--muted); border: none; border-bottom: 2px solid transparent; background: none; cursor: pointer; display: flex; align-items: center; gap: 7px; transition: color .14s, border-color .14s; }
    .modal-tab.active { color: #16A34A; border-bottom-color: #16A34A; }
    .tab-count { display: inline-flex; align-items: center; justify-content: center; min-width: 20px; height: 20px; padding: 0 5px; border-radius: 999px; font-size: 11px; font-weight: 700; }
    .tab-count.red { background: #FFF1F2; color: #B91C1C; }
    .tab-count.green { background: #F0FDF4; color: #15803D; }
    
    .modal-toolbar { display: flex; align-items: center; gap: 10px; padding: 12px 24px; border-bottom: 1px solid var(--line); flex-wrap: wrap; background: var(--bg); }
    .modal-search { position: relative; flex: 1; min-width: 160px; }
    .modal-search svg { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; stroke: var(--muted); fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; pointer-events: none; }
    .modal-search input { width: 100%; height: 36px; border: 1px solid var(--line); border-radius: 8px; padding: 0 12px 0 36px; font-family: inherit; font-size: 13px; font-weight: 600; background: #fff; color: var(--text); outline: none; }
    .modal-search input:focus { border-color: var(--blue-bd); }
    
    .filter-btns { display: flex; gap: 6px; }
    .filter-btn { height: 36px; padding: 0 12px; border-radius: 8px; border: 1px solid var(--line); background: #fff; font-family: inherit; font-size: 12px; font-weight: 700; color: var(--text); cursor: pointer; transition: all .14s; }
    .filter-btn.f-all { background: var(--blue-lt); color: var(--blue); border-color: var(--blue-bd); }
    .filter-btn.f-expired { background: #FFF1F2; color: #B91C1C; border-color: #FECACA; }
    .filter-btn.f-expiring { background: #FFFBEB; color: #B45309; border-color: #FDE68A; }
    
    .sel-all-label { display: flex; align-items: center; gap: 7px; font-size: 12.5px; font-weight: 700; color: var(--text); cursor: pointer; white-space: nowrap; }
    .sel-all-label input { width: 14px; height: 14px; cursor: pointer; accent-color: var(--blue); }
    
    .modal-list { max-height: 360px; overflow-y: auto; }
    .modal-row { display: flex; align-items: center; gap: 12px; padding: 12px 24px; border-bottom: 1px solid var(--bg); transition: background .12s; }
    .modal-row:hover { background: var(--bg); }
    .modal-row:last-child { border-bottom: 0; }
    .modal-row.hidden { display: none; }
    .modal-row input[type=checkbox] { width: 15px; height: 15px; cursor: pointer; flex: 0 0 auto; accent-color: var(--blue); }
    
    .modal-footer { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 16px 24px; border-top: 1px solid var(--line); background: var(--bg); }
    .modal-sel-count { font-size: 13px; color: var(--muted); font-weight: 600; }
    .modal-sel-count b { color: var(--black); }
    
    .btn-cancel { height: 40px; padding: 0 18px; border-radius: 8px; border: 1px solid var(--line); background: #fff; font-family: inherit; font-size: 13px; font-weight: 700; color: var(--text); cursor: pointer; transition: background .14s; }
    .btn-cancel:hover { background: var(--bg); }
    
    .btn-send { height: 40px; padding: 0 20px; border-radius: 8px; border: none; background: #16A34A; color: #fff; font-family: inherit; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; transition: background .14s, opacity .14s; box-shadow: 0 2px 8px rgba(22,163,74,.22); }
    .btn-send:hover { background: #15803D; }
    .btn-send:disabled { opacity: .45; cursor: not-allowed; }
    .btn-send svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    
    #dsToast { position: fixed; bottom: 24px; right: 24px; z-index: 9999; display: flex; align-items: center; gap: 10px; padding: 12px 16px; border-radius: 12px; font-family: inherit; font-size: 13px; font-weight: 700; box-shadow: 0 10px 24px rgba(15,23,42,.15); max-width: 380px; opacity: 0; transform: translateY(10px); transition: opacity .22s ease, transform .22s ease; pointer-events: none; }
    #dsToast.show { opacity: 1; transform: none; }
    #dsToast.toast-success { background: #F0FDF4; border: 1px solid #ABEFC6; color: #15803D; }
    #dsToast.toast-error { background: #FFF1F2; border: 1px solid #FECACA; color: #B91C1C; }
    
    @media(max-width:1100px){
        .ds-main-grid { grid-template-columns: 1fr; }
        .ds-aside { grid-template-columns: repeat(2, 1fr); }
    }
    @media(max-width:860px){
        .ds-kpis { grid-template-columns: repeat(2, 1fr); }
        .ds-plan-row { grid-template-columns: 1fr; }
    }
    @media(max-width:600px){
        .ds-wrap { padding: 16px 14px 40px; }
        .ds-kpis, .ds-aside { grid-template-columns: 1fr; }
        .ds-header { flex-direction: column; gap: 14px; }
        .ds-header-right { flex-direction: column; width: 100%; }
        .ds-date-chip, .ds-wa-btn { width: 100%; justify-content: center; }
        .ds-kpi-value { font-size: 26px; }
        .modal-toolbar { flex-direction: column; align-items: stretch; }
        .filter-btns { flex-wrap: wrap; }
    }
</style>

<div class="ds-wrap">
    <!-- ENV Warning -->
    @if ($envWarning)
        <div class="env-warn">⚠️ {{ $envWarning }}</div>
    @endif

    <!-- Page Header -->
    <div class="ds-header">
        <div class="ds-header-left">
            <div class="ds-breadcrumb">Center Overview</div>
            <h1 class="ds-page-title">Renewal Dashboard</h1>
            <p class="ds-page-sub">Track PUC renewals, reminders, and customer activity in one workspace.</p>
        </div>
        <div class="ds-header-right">
            <div class="ds-date-chip">
                <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                {{ date('d M Y') }}
            </div>
            <a href="{{ route('customers.create') }}" class="ds-wa-btn" style="background:var(--blue);box-shadow:0 2px 8px rgba(37,99,235,.22);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Vehicle Record
            </a>
            <button type="button" id="dsOpenModal" class="ds-wa-btn">
                <svg viewBox="0 0 24 24" fill="currentColor" style="width:16px;height:16px;"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                Send Reminders
                @if ($attentionCount > 0)
                    <span class="ds-wa-badge">{{ $attentionCount }}</span>
                @endif
            </button>
        </div>
    </div>

    <!-- Alert Banner -->
    <div class="ds-alert {{ $focusTone }}">
        <div class="ds-alert-icon">
            @if($focusTone==='success')✅@elseif($focusTone==='danger')🚨@elseif($focusTone==='warning')⚠️@else📋@endif
        </div>
        <div class="ds-alert-body">
            <div class="ds-alert-title">{{ $focusTitle }}</div>
            <div class="ds-alert-text">{{ $focusText }}</div>
        </div>
        <div style="text-align:right;flex:0 0 auto;">
            <div style="font-size:28px;font-weight: 700;color:var(--black);letter-spacing:-.04em;line-height:1;">
                {{ $activePercent }}<span style="font-size:14px;font-weight:700;color:var(--muted);">%</span>
            </div>
            <div style="font-size:11px;color:var(--muted);font-weight:600;margin-top:3px;">Active health</div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="ds-kpis">
        <div class="ds-kpi default">
            <div class="ds-kpi-icon"><svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5z"/></svg></div>
            <div class="ds-kpi-label">Total Records</div>
            <div class="ds-kpi-value">{{ $totalRecords }}</div>
            <div class="ds-kpi-meta">{{ $monthAdded }} added this month</div>
        </div>
        <div class="ds-kpi success">
            <div class="ds-kpi-icon"><svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg></div>
            <div class="ds-kpi-label">Active Records</div>
            <div class="ds-kpi-value">{{ $activeRecords }}</div>
            <div class="ds-kpi-meta">{{ $activePercent }}% of all records healthy</div>
        </div>
        <div class="ds-kpi warning">
            <div class="ds-kpi-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></div>
            <div class="ds-kpi-label">Due Soon</div>
            <div class="ds-kpi-value">{{ $dueToday + $expiring7 }}</div>
            <div class="ds-kpi-meta">{{ $dueToday }} today · {{ $expiring7 }} next 7 days</div>
        </div>
        <div class="ds-kpi danger">
            <div class="ds-kpi-icon"><svg viewBox="0 0 24 24"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4M12 17h.01"/></svg></div>
            <div class="ds-kpi-label">Expired</div>
            <div class="ds-kpi-value">{{ $expiredRecords }}</div>
            <div class="ds-kpi-meta">Needs follow-up first</div>
        </div>
    </div>

    <!-- Monthly Records Trend Chart -->
    <div class="ds-card" style="margin-bottom:20px;">
        <div class="ds-card-head">
            <div>
                <div class="ds-card-title">Monthly Records Trend</div>
                <div class="ds-card-sub">New records added vs certificates expiring — last 6 months.</div>
            </div>
        </div>
        <div class="ds-card-body" style="padding-top:6px;padding-bottom:6px;">
            <div id="splineAreaChart"></div>
        </div>
    </div>

    <!-- Plan + Workload -->
    <div class="ds-plan-row">
        <div class="ds-plan-card">
            <div class="ds-plan-header">
                <div>
                    <div class="ds-plan-name">{{ $planName }} Plan</div>
                    <div class="ds-plan-sub">
                        @if ($isTrial)
                            Lifetime usage · contact admin to upgrade
                        @elseif ($planDaysLeft >= 0)
                            {{ $planDaysLeft === 99999 ? 'Trial · Lifetime' : $planDaysLeft . ' days remaining' }} · {{ ucfirst($billingCycle) }}
                        @else
                            Plan expired · contact admin
                        @endif
                    </div>
                </div>
                <a href="{{ url('/pricing') }}" class="ds-status-pill {{ $planDaysLeft >= 0 ? 'active' : 'trial' }}">
                    {{ $planDaysLeft >= 0 ? 'Active' : 'Review' }}
                </a>
            </div>
            <div class="ds-usage-meter">
                <div class="ds-usage-row">
                    <span class="ds-usage-label">Customer Records</span>
                    <span class="ds-usage-value">{{ $totalRecords }} / ∞</span>
                </div>
                <div class="ds-track">
                    <div class="ds-fill" style="width: {{ min(100, (int) round(($totalRecords / 100) * 100)) }}%"></div>
                </div>
            </div>
            <div class="ds-usage-meter" style="margin-top:10px;">
                <div class="ds-usage-row">
                    <span class="ds-usage-label">WhatsApp {{ $isTrial ? '(Total)' : '(This Month)' }}</span>
                    <span class="ds-usage-value">{{ $whatsAppUsed }} / {{ $whatsAppLimit > 0 ? $whatsAppLimit : '∞' }}</span>
                </div>
                <div class="ds-track">
                    <div class="ds-fill" style="width: {{ $whatsAppUsagePercent }}%"></div>
                </div>
            </div>
        </div>
        <div class="ds-plan-card">
            <div class="ds-plan-header">
                <div>
                    <div class="ds-plan-name">Today's Workload</div>
                    <div class="ds-plan-sub">Prioritise follow-ups by urgency.</div>
                </div>
            </div>
            <div class="ds-workload">
                <div class="ds-wl-row">
                    <span class="ds-wl-label">Expired — follow up</span>
                    <div class="ds-wl-bar">
                        <div class="ds-wl-fill" style="width: {{ dash_percent($expiredRecords, max(1, $totalRecords)) }}%; background: #EF4444;"></div>
                    </div>
                    <span class="ds-wl-count">{{ $expiredRecords }}</span>
                </div>
                <div class="ds-wl-row">
                    <span class="ds-wl-label">Due today</span>
                    <div class="ds-wl-bar">
                        <div class="ds-wl-fill" style="width: {{ dash_percent($dueToday, max(1, $totalRecords)) }}%; background: #F59E0B;"></div>
                    </div>
                    <span class="ds-wl-count">{{ $dueToday }}</span>
                </div>
                <div class="ds-wl-row">
                    <span class="ds-wl-label">Next 7 days</span>
                    <div class="ds-wl-bar">
                        <div class="ds-wl-fill" style="width: {{ dash_percent($expiring7, max(1, $totalRecords)) }}%;"></div>
                    </div>
                    <span class="ds-wl-count">{{ $expiring7 }}</span>
                </div>
                <div class="ds-wl-row">
                    <span class="ds-wl-label">Next 30 days</span>
                    <div class="ds-wl-bar">
                        <div class="ds-wl-fill" style="width: {{ dash_percent($expiring30, max(1, $totalRecords)) }}%;"></div>
                    </div>
                    <span class="ds-wl-count">{{ $expiring30 }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="ds-main-grid">
        <div>
            <!-- Priority Queue -->
            <div class="ds-card">
                <div class="ds-card-head">
                    <div>
                        <div class="ds-card-title">Renewal Priority Queue</div>
                        <div class="ds-card-sub">Expired and upcoming records by nearest expiry date.</div>
                    </div>
                    <span class="ds-chip">{{ $attentionCount }} need attention</span>
                </div>
                <div class="ds-card-body">
                    @if ($priorityRows->isEmpty())
                        <div class="ds-empty">
                            <div class="ds-empty-icon">
                                <svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
                            </div>
                            <strong>No urgent renewals</strong>
                            <span>Expired and 7-day reminders will appear here automatically.</span>
                        </div>
                    @else
                        <div class="ds-table-wrap">
                            <table class="ds-table">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Vehicle</th>
                                        <th>Mobile</th>
                                        <th>Expiry</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($priorityRows as $record)
                                        @php
                                            [$statusLabel, $statusClass, $statusHelp] = dash_status_text((string)$record->expiry_date);
                                            $cn = trim((string)$record->customer_name);
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="ds-customer">
                                                    <div class="ds-avatar">{{ dash_initial($cn) }}</div>
                                                    <div>
                                                        <span class="ds-cust-name">{{ $cn ?: 'Customer' }}</span>
                                                        <span class="ds-cust-type">{{ $record->vehicle_type }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="ds-vehicle">{{ $waService->formatVehicleNumber($record->vehicle_number) }}</span>
                                            </td>
                                            <td>{{ $record->customer_mobile }}</td>
                                            <td>{{ dash_safe_date($record->expiry_date) }}</td>
                                            <td>
                                                <span class="ds-badge {{ $statusClass }}" title="{{ $statusHelp }}">{{ $statusLabel }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Records -->
            <div class="ds-card" style="margin-top:16px;">
                <div class="ds-card-head">
                    <div>
                        <div class="ds-card-title">Recent Customer Records</div>
                        <div class="ds-card-sub">Latest saved customer and vehicle details.</div>
                    </div>
                </div>
                <div class="ds-card-body">
                    @if ($recentRecords->isEmpty())
                        <div class="ds-empty">
                            <div class="ds-empty-icon">
                                <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                            </div>
                            <strong>No records yet</strong>
                            <span>Add customers from the sidebar to start tracking renewals.</span>
                        </div>
                    @else
                        <div class="ds-table-wrap">
                            <table class="ds-table">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Vehicle</th>
                                        <th>Mobile</th>
                                        <th>Expiry</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentRecords as $record)
                                        @php
                                            [$statusLabel, $statusClass] = reminder_badge((string)$record->expiry_date);
                                            $cn = trim((string)$record->customer_name);
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="ds-customer">
                                                    <div class="ds-avatar">{{ dash_initial($cn) }}</div>
                                                    <div>
                                                        <span class="ds-cust-name">{{ $cn ?: 'Customer' }}</span>
                                                        <span class="ds-cust-type">{{ $record->vehicle_type }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="ds-vehicle">{{ $waService->formatVehicleNumber($record->vehicle_number) }}</span>
                                            </td>
                                            <td>{{ $record->customer_mobile }}</td>
                                            <td>{{ dash_safe_date($record->expiry_date) }}</td>
                                            <td>
                                                <span class="ds-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Sidebar -->
        <aside class="ds-aside">
            <!-- Donut Chart -->
            <div class="ds-card">
                <div class="ds-card-head">
                    <div>
                        <div class="ds-card-title">Records Overview</div>
                        <div class="ds-card-sub">Distribution of PUC certificates.</div>
                    </div>
                </div>
                <div class="ds-card-body" style="padding-top:5px;">
                    <div id="recordsChart"></div>
                </div>
            </div>

            <!-- WhatsApp Health -->
            <div class="ds-card">
                <div class="ds-card-head">
                    <div>
                        <div class="ds-card-title">WhatsApp Health</div>
                        <div class="ds-card-sub">Reminder delivery performance.</div>
                    </div>
                </div>
                <div class="ds-card-body">
                    <div class="ds-metrics">
                        <div class="ds-metric">
                            <div class="ds-metric-val">{{ $whatsAppLogs }}</div>
                            <div class="ds-metric-lbl">Total logs</div>
                        </div>
                        <div class="ds-metric">
                            <div class="ds-metric-val" style="color:#15803D">{{ $whatsAppSent }}</div>
                            <div class="ds-metric-lbl">Sent</div>
                        </div>
                        <div class="ds-metric">
                            <div class="ds-metric-val" style="color:#D97706">{{ $whatsAppPending }}</div>
                            <div class="ds-metric-lbl">Pending</div>
                        </div>
                        <div class="ds-metric">
                            <div class="ds-metric-val" style="color:#DC2626">{{ $whatsAppFailed }}</div>
                            <div class="ds-metric-lbl">Failed</div>
                        </div>
                    </div>
                    <div class="ds-usage-meter">
                        <div class="ds-usage-row">
                            <span class="ds-usage-label">Delivery rate</span>
                            <span class="ds-usage-value">{{ $deliveryRate }}%</span>
                        </div>
                        <div class="ds-track">
                            <div class="ds-fill" style="width: {{ $deliveryRate }}%; background: #22C55E;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Entries -->
            <div class="ds-card">
                <div class="ds-card-head">
                    <div>
                        <div class="ds-card-title">Monthly Entries</div>
                        <div class="ds-card-sub">Records added in last 6 months.</div>
                    </div>
                </div>
                <div class="ds-card-body">
                    <div class="ds-chart">
                        @foreach ($monthlyLabels as $i => $label)
                            <div class="ds-bar-row">
                                <span class="ds-bar-lbl">{{ $label }}</span>
                                <div class="ds-bar-track">
                                    <div class="ds-bar-fill" style="width: {{ dash_percent((int)$monthlyCounts[$i], $maxMonthCount) }}%"></div>
                                </div>
                                <span class="ds-bar-val">{{ $monthlyCounts[$i] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Vehicle Type split -->
            <div class="ds-card">
                <div class="ds-card-head">
                    <div>
                        <div class="ds-card-title">Vehicle Type Split</div>
                        <div class="ds-card-sub">Record category breakdown.</div>
                    </div>
                </div>
                <div class="ds-card-body">
                    @if ($vehicleTypes->isEmpty())
                        <div class="ds-empty">
                            <div class="ds-empty-icon">
                                <svg viewBox="0 0 24 24"><path d="M3 7h18M5 7l2 12h10l2-12"/></svg>
                            </div>
                            <strong>No vehicle data</strong>
                            <span>Vehicle split will appear after records are added.</span>
                        </div>
                    @else
                        <div class="ds-type-list">
                            @foreach ($vehicleTypes as $type)
                                <div class="ds-type-row">
                                    <span>{{ $type->vehicle_type }}</span>
                                    <b>{{ $type->total }}</b>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </aside>
    </div>
</div>

<!-- WhatsApp Reminder Modal -->
<div id="dsOverlay">
    <div id="dsModal">
        <div class="modal-header">
            <div style="display:flex;align-items:center;gap:12px;">
                <div class="modal-icon">
                    <svg viewBox="0 0 24 24" fill="#16A34A" style="width:22px;height:22px;"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                </div>
                <div>
                    <div class="modal-title">Send Renewal Reminders</div>
                    <div class="modal-sub">Meta WhatsApp Cloud API · Select customers to notify</div>
                </div>
            </div>
            <button id="dsCloseModal" class="modal-close" aria-label="Close">✕</button>
        </div>
        <div class="modal-tabs">
            <button class="modal-tab active" data-tab="needs">Needs Reminder <span class="tab-count red">{{ count($reminderNeeds) }}</span></button>
            <button class="modal-tab" data-tab="sent">Sent (30 days) <span class="tab-count green">{{ count($reminderSent) }}</span></button>
        </div>
        <div class="modal-toolbar" id="dsToolbar">
            <div class="modal-search">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input id="dsSearch" type="text" placeholder="Search name, mobile, vehicle…">
            </div>
            <div class="filter-btns">
                <button class="filter-btn f-all" data-filter="all">All</button>
                <button class="filter-btn" data-filter="expired">Expired</button>
                <button class="filter-btn" data-filter="expiring">Expiring</button>
            </div>
            <!-- Language Dropdown -->
            <div style="margin-left:auto; display:flex; align-items:center; gap:8px;">
                <label for="dsLang" style="font-size:12px; font-weight:700; color:var(--text);">Remind in:</label>
                <select id="dsLang" style="height:36px; padding:0 10px; border-radius:8px; border:1px solid var(--line); font-family:inherit; font-size:12.5px; font-weight:600; outline:none; cursor:pointer;">
                    <option value="en">English (Default)</option>
                    <option value="guj">Gujarati (ગુજરાતી)</option>
                </select>
            </div>
            <label class="sel-all-label" style="margin-left:14px;"><input type="checkbox" id="dsSelectAll"> Select all</label>
        </div>
        <div id="dsNeedsPanel" class="modal-list">
            @if ($reminderNeeds->isEmpty())
                <div style="text-align:center;padding:40px 24px;">
                    <div style="font-size:40px;opacity:.3;margin-bottom:12px;">✅</div>
                    <div style="font-size:15px;font-weight: 700;color:var(--text);margin-bottom:6px;">All clear!</div>
                    <div style="font-size:13px;color:var(--muted);font-weight:500;">No expired or expiring records right now.</div>
                </div>
            @else
                @foreach ($reminderNeeds as $rr)
                    @php
                        $rn = trim((string)$rr->customer_name);
                        $rn = $rn !== '' ? $rn : 'Customer';
                        $ri = strtoupper(substr($rn, 0, 1));
                        $rdays = (int) \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($rr->expiry_date), false);
                        $rstatus = $rdays < 0 ? 'expired' : 'expiring';
                        $rlabel = $rdays < 0 ? 'Expired' : ($rdays === 0 ? 'Due Today' : 'Expiring');
                        $rdaysTxt = $rdays < 0 ? abs($rdays) . 'd overdue' : ($rdays === 0 ? 'Today' : $rdays . 'd left');
                        $badgeCls = $rdays < 0 ? 'danger' : 'warning';
                    @endphp
                    <div class="modal-row" data-id="{{ $rr->id }}" data-status="{{ $rstatus }}" data-name="{{ mb_strtolower($rn) }}" data-mobile="{{ $rr->customer_mobile }}" data-vehicle="{{ mb_strtolower((string)$rr->vehicle_number) }}">
                        <input type="checkbox" class="pdrm-cb" data-id="{{ $rr->id }}">
                        <div class="ds-avatar">{{ $ri }}</div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:13.5px;font-weight:700;color:var(--black);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $rn }}</div>
                            <div style="font-size:12px;color:var(--muted);font-weight:600;margin-top:2px;">{{ $rr->customer_mobile }} · {{ $rr->vehicle_type }}</div>
                        </div>
                        <span class="ds-vehicle" style="font-size:12px;">{{ $waService->formatVehicleNumber($rr->vehicle_number) }}</span>
                        <div style="text-align:right;flex:0 0 auto;">
                            <span class="ds-badge {{ $badgeCls }}">{{ $rlabel }}</span>
                            <div style="font-size:11px;color:var(--muted);margin-top:3px;font-weight:600;">{{ $rdaysTxt }}</div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
        <div id="dsSentPanel" class="modal-list" style="display:none;">
            @if ($reminderSent->isEmpty())
                <div style="text-align:center;padding:40px 24px;">
                    <div style="font-size:40px;opacity:.3;margin-bottom:12px;">📭</div>
                    <div style="font-size:15px;font-weight: 700;color:var(--text);margin-bottom:6px;">No sent logs</div>
                    <div style="font-size:13px;color:var(--muted);font-weight:500;">Messages sent from this dashboard appear here for 30 days.</div>
                </div>
            @else
                @foreach ($reminderSent as $sl)
                    @php
                        $sn = trim((string)($sl->vehicleRecord->customer_name ?? 'Customer'));
                        $si = strtoupper(substr($sn, 0, 1));
                        $st = $sl->sent_at ? \Carbon\Carbon::parse($sl->sent_at)->format('d M Y, h:i A') : '-';
                    @endphp
                    <div class="modal-row" style="cursor:default;">
                        <div class="ds-avatar" style="background:#F0FDF4;border-color:#ABEFC6;color:#15803D;">{{ $si }}</div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:13.5px;font-weight:700;color:var(--black);">{{ $sn }}</div>
                            <div style="font-size:12px;color:var(--muted);font-weight:600;margin-top:2px;">{{ $sl->customer_mobile }} · Sent {{ $st }}</div>
                        </div>
                        @if (!empty($sl->vehicleRecord->vehicle_number))
                            <span class="ds-vehicle" style="font-size:12px;">{{ $waService->formatVehicleNumber($sl->vehicleRecord->vehicle_number) }}</span>
                        @endif
                        <span class="ds-badge success">✓ Sent</span>
                    </div>
                @endforeach
            @endif
        </div>
        <div class="modal-footer">
            <div class="modal-sel-count"><b id="dsSelCount">0</b> selected</div>
            <div style="display:flex;gap:10px;">
                <button class="btn-cancel" id="dsCancelBtn">Cancel</button>
                <button class="btn-send" id="dsSendBtn" disabled>
                    <svg viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    Send Selected
                </button>
            </div>
        </div>
    </div>
</div>

<div id="dsToast"><span id="dsToastMsg"></span></div>

@push('scripts')
<script>
(function(){
    var overlay=document.getElementById('dsOverlay'),openBtn=document.getElementById('dsOpenModal'),
        closeBtn=document.getElementById('dsCloseModal'),cancelBtn=document.getElementById('dsCancelBtn'),
        sendBtn=document.getElementById('dsSendBtn'),selCount=document.getElementById('dsSelCount'),
        selAll=document.getElementById('dsSelectAll'),search=document.getElementById('dsSearch'),
        toolbar=document.getElementById('dsToolbar'),needs=document.getElementById('dsNeedsPanel'),
        sent=document.getElementById('dsSentPanel'),toast=document.getElementById('dsToast'),
        toastMsg=document.getElementById('dsToastMsg'),selected={},activeFilter='all',toastTimer;

    function reset(){
        selected={};updateCount();
        document.querySelectorAll('.pdrm-cb').forEach(function(cb){cb.checked=false;});
        if(selAll){selAll.checked=false;selAll.indeterminate=false;}
        sendBtn.disabled=true;
        sendBtn.innerHTML='<svg viewBox="0 0 24 24" style="width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg> Send Selected';
        if(search)search.value='';
        activeFilter='all';
        document.querySelectorAll('.filter-btn').forEach(function(b){b.className='filter-btn';});
        var ab=document.querySelector('.filter-btn[data-filter="all"]');if(ab)ab.classList.add('f-all');
        document.querySelectorAll('.modal-tab').forEach(function(t){t.classList.remove('active');});
        var nt=document.querySelector('.modal-tab[data-tab="needs"]');if(nt)nt.classList.add('active');
        needs.style.display='block';sent.style.display='none';toolbar.style.display='flex';
        document.querySelectorAll('.modal-row').forEach(function(r){r.classList.remove('hidden');});
    }
    function open(){reset();overlay.classList.add('open');}
    function close(){overlay.classList.remove('open');}
    openBtn&&openBtn.addEventListener('click',open);
    closeBtn.addEventListener('click',close);
    cancelBtn.addEventListener('click',close);
    overlay.addEventListener('click',function(e){if(e.target===overlay)close();});
    document.addEventListener('keydown',function(e){if(e.key==='Escape')close();});
    document.querySelectorAll('.modal-tab').forEach(function(tab){
        tab.addEventListener('click',function(){
            document.querySelectorAll('.modal-tab').forEach(function(t){t.classList.remove('active');});
            this.classList.add('active');var t=this.dataset.tab;
            needs.style.display=t==='needs'?'block':'none';
            sent.style.display=t==='sent'?'block':'none';
            toolbar.style.display=t==='needs'?'flex':'none';
        });
    });
    document.querySelectorAll('.filter-btn').forEach(function(btn){
        btn.addEventListener('click',function(){
            document.querySelectorAll('.filter-btn').forEach(function(b){b.className='filter-btn';});
            activeFilter=this.dataset.filter;
            var cls={all:'f-all',expired:'f-expired',expiring:'f-expiring'}[activeFilter]||'f-all';
            this.classList.add(cls);applyFilter();
        });
    });
    function applyFilter(){
        var q=search?search.value.trim().toLowerCase():'';
        document.querySelectorAll('.modal-row[data-id]').forEach(function(row){
            var mf=activeFilter==='all'||row.dataset.status===activeFilter;
            var ms=!q||row.dataset.name.indexOf(q)!==-1||row.dataset.mobile.indexOf(q)!==-1||row.dataset.vehicle.indexOf(q)!==-1;
            row.classList.toggle('hidden',!(mf&&ms));
        });syncSelAll();
    }
    search&&search.addEventListener('input',applyFilter);
    function updateCount(){var n=Object.keys(selected).length;selCount.textContent=n;sendBtn.disabled=n===0;}
    function syncSelAll(){
        var vis=Array.from(document.querySelectorAll('.modal-row[data-id]:not(.hidden) .pdrm-cb'));
        if(!vis.length){selAll.checked=false;selAll.indeterminate=false;return;}
        var chk=vis.filter(function(cb){return selected[cb.dataset.id];}).length;
        selAll.checked=chk===vis.length;selAll.indeterminate=chk>0&&chk<vis.length;
    }
    needs.addEventListener('change',function(e){
        if(!e.target.classList.contains('pdrm-cb'))return;
        var id=e.target.dataset.id;
        if(e.target.checked)selected[id]=true;else delete selected[id];
        updateCount();syncSelAll();
    });
    selAll&&selAll.addEventListener('change',function(){
        document.querySelectorAll('.modal-row[data-id]:not(.hidden) .pdrm-cb').forEach(function(cb){
            cb.checked=selAll.checked;
            if(selAll.checked)selected[cb.dataset.id]=true;else delete selected[cb.dataset.id];
        });updateCount();
    });
    sendBtn.addEventListener('click',function(){
        var ids=Object.keys(selected);if(!ids.length)return;
        sendBtn.disabled=true;
        sendBtn.innerHTML='⏳ Sending '+ids.length+' reminder'+(ids.length>1?'s':'')+'…';
        
        var fd=new FormData();
        fd.append('_token', '{{ csrf_token() }}');
        fd.append('lang', document.getElementById('dsLang').value);
        ids.forEach(function(id){fd.append('record_ids[]',id);});

        fetch('{{ route("dashboard.bulk-whatsapp") }}',{method:'POST',body:fd})
            .then(function(r){return r.json();})
            .then(function(data){
                close();
                showToast(data.success,data.message);
                setTimeout(function(){ window.location.reload(); }, 1200);
            })
            .catch(function(){close();showToast(false,'❌ Network error. Could not reach server.');});
    });
    function showToast(success,msg){
        clearTimeout(toastTimer);
        toast.className='show '+(success?'toast-success':'toast-error');
        toastMsg.textContent=msg;
        toastTimer=setTimeout(function(){toast.className='';},5000);
    }
})();
</script>

<script>
document.addEventListener("DOMContentLoaded",function(){
    if(document.querySelector("#recordsChart")){
        new ApexCharts(document.querySelector("#recordsChart"),{
            series:[{{ (int)$activeRecords }}, {{ (int)($dueToday+$expiring7) }}, {{ (int)$expiredRecords }}],
            chart:{type:'donut',height:280,fontFamily:'inherit'},
            labels:['Active','Due Soon','Expired'],
            colors:['#22c55e','#f59e0b','#ef4444'],
            plotOptions:{pie:{donut:{size:'75%',labels:{show:true,name:{show:true,fontSize:'13px',color:'#64748b',fontWeight:600},value:{show:true,fontSize:'22px',fontWeight:800,color:'#0f172a',formatter:function(val){return val;}},total:{show:true,showAlways:true,label:'Total',fontSize:'13px',color:'#64748b',fontWeight:600,formatter:function(w){return w.globals.seriesTotals.reduce(function(a,b){return a+b;},0);}}}}}},
            dataLabels:{enabled:false},stroke:{width:0},
            legend:{position:'bottom',fontSize:'13px',fontWeight:600,markers:{radius:12},itemMargin:{horizontal:10,vertical:5}}
        }).render();
    }
    if(document.querySelector("#splineAreaChart")){
        new ApexCharts(document.querySelector("#splineAreaChart"),{
            chart:{type:'area',height:280,fontFamily:'inherit',toolbar:{show:false},zoom:{enabled:false},animations:{enabled:true,speed:700}},
            series:[{name:'New Records',data:{!! json_encode($monthlyCounts) !!}},{name:'Expiring',data:{!! json_encode($expiringCounts) !!}}],
            colors:['#2563EB','#22C55E'],
            stroke:{curve:'smooth',width:2.5},
            fill:{type:'gradient',gradient:{shadeIntensity:1,opacityFrom:0.28,opacityTo:0.02,stops:[0,90,100]}},
            xaxis:{categories:{!! json_encode($monthlyLabels) !!},labels:{style:{colors:'#94A3B8',fontSize:'12px',fontWeight:600}},axisBorder:{show:false},axisTicks:{show:false}},
            yaxis:{min:0,tickAmount:4,labels:{style:{colors:'#94A3B8',fontSize:'12px',fontWeight:600}}},
            grid:{borderColor:'#F1F5F9',strokeDashArray:4,xaxis:{lines:{show:false}}},
            legend:{show:true,position:'top',horizontalAlign:'right',fontSize:'12px',fontWeight:600,labels:{colors:'#475569'},markers:{radius:12},itemMargin:{horizontal:10}},
            dataLabels:{enabled:false},
            tooltip:{theme:'light'}
        }).render();
    }
});
</script>
@endpush
@endsection
