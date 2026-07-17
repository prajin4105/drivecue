@extends('layouts.dashboard')

@section('title', 'Vehicle Records')
@section('page-title', 'Vehicle Records')
@section('page-subtitle', 'Search, filter, and manage all saved PUC customer and vehicle records.')

@section('content')
@inject('waService', 'App\Services\WhatsAppService')

@php
if (!function_exists('cust_status_pill')) {
    function cust_status_pill(string $expiryDate): array {
        $days = (int) \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($expiryDate), false);
        if ($days < 0) return ['Expired', 'customer-status-expired'];
        if ($days === 0) return ['Today', 'customer-status-expiring'];
        if ($days <= 7) return ['Due Soon', 'customer-status-expiring'];
        return ['Active', 'customer-status-active'];
    }
}
@endphp

<style>
:root {
    --cust-black: #0B1020;
    --cust-text: #111827;
    --cust-muted: #697386;
    --cust-blue: #2563EB;
    --cust-blue-dark: #1E40AF;
    --cust-blue-soft: #EFF6FF;
    --cust-bg: #F5F7FB;
    --cust-white: #FFFFFF;
    --cust-border: #E2E8F0;
    --cust-border-dark: #CBD5E1;
    --cust-shadow: 0 18px 44px rgba(15, 23, 42, 0.08);
    --cust-shadow-soft: 0 8px 24px rgba(15, 23, 42, 0.06);
}

.customers-page { background: var(--cust-bg); color: var(--cust-text); }
.customers-page * { box-spacing: border-box; }
.customers-page .page-head { margin-bottom: 20px; }

.customers-toolbar-card {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 18px;
    align-items: center;
    margin-bottom: 18px;
    padding: 18px;
    border: 1px solid var(--cust-border);
    border-radius: 22px;
    background: var(--cust-white);
    box-shadow: var(--cust-shadow-soft);
}

.customers-toolbar-title { display: flex; align-items: center; gap: 12px; min-width: 0; }

.customers-toolbar-icon {
    width: 44px; height: 44px;
    display: inline-flex; align-items: center; justify-content: center; flex: 0 0 auto;
    border-radius: 15px; background: var(--cust-blue-soft); color: var(--cust-blue); border: 1px solid #DBEAFE;
}
.customers-toolbar-icon svg { width: 21px; height: 21px; stroke: currentColor; stroke-width: 2.2; fill: none; stroke-linecap: round; stroke-linejoin: round; }
.customers-toolbar-title strong { display: block; color: var(--cust-black); font-size: 1.02rem; font-weight: 700; letter-spacing: -0.025em; line-height: 1.15; }
.customers-toolbar-title span { display: block; margin-top: 3px; color: var(--cust-muted); font-size: .82rem; font-weight: 760; line-height: 1.45; }

.customers-stats-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 18px; }

.customer-stat-card {
    position: relative; display: flex; align-items: center; gap: 13px; min-height: 104px;
    padding: 18px; border: 1px solid var(--cust-border); border-radius: 22px;
    background: var(--cust-white); box-shadow: var(--cust-shadow-soft); overflow: hidden;
    text-decoration: none !important;
    transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
}
.customer-stat-card::before { content: ""; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: var(--stat-color, var(--cust-blue)); }
.customer-stat-card:hover { transform: translateY(-2px); border-color: #BFDBFE; box-shadow: var(--cust-shadow); }

.customer-stat-icon { width: 48px; height: 48px; display: inline-flex; align-items: center; justify-content: center; flex: 0 0 auto; border-radius: 16px; background: var(--cust-blue-soft); color: var(--stat-color, var(--cust-blue)); border: 1px solid #DBEAFE; }
.customer-stat-icon svg { width: 22px; height: 22px; stroke: currentColor; stroke-width: 2.25; fill: none; stroke-linecap: round; stroke-linejoin: round; }
.customer-stat-content b { display: block; color: var(--cust-black); font-size: 1.9rem; font-weight: 700; line-height: 1; letter-spacing: -0.055em; }
.customer-stat-content span { display: block; margin-top: 6px; color: var(--cust-muted); font-size: .82rem; font-weight: 820; }

.customer-filter-card {
    display: grid; grid-template-columns: minmax(220px, 1fr) 180px auto auto;
    gap: 12px; align-items: center; margin-bottom: 18px; padding: 15px;
    border: 1px solid var(--cust-border); border-radius: 22px; background: var(--cust-white); box-shadow: var(--cust-shadow-soft);
}
.customer-search-wrap, .customer-select-wrap { position: relative; }
.customer-search-wrap svg, .customer-select-wrap svg { position: absolute; left: 15px; top: 50%; width: 18px; height: 18px; transform: translateY(-50%); color: #64748B; stroke: currentColor; stroke-width: 2.2; fill: none; stroke-linecap: round; stroke-linejoin: round; pointer-events: none; }
.customer-filter-card input, .customer-filter-card select { width: 100%; height: 48px; border: 1px solid var(--cust-border); border-radius: 16px; background: #F8FAFC; color: var(--cust-black); outline: none; padding: 0 14px 0 44px; font-size: .9rem; font-weight: 780; transition: .18s ease; }
.customer-filter-card select { padding-right: 14px; appearance: none; }
.customer-filter-card input::placeholder { color: #94A3B8; font-weight: 760; }
.customer-filter-card input:focus, .customer-filter-card select:focus { border-color: #93C5FD; background: var(--cust-white); box-shadow: 0 0 0 4px rgba(37,99,235,.10); }

.customer-btn { min-height: 48px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 18px; border-radius: 16px; border: 1px solid transparent; text-decoration: none !important; cursor: pointer; font-size: .9rem; font-weight: 920; line-height: 1; white-space: nowrap; transition: .18s ease; }
.customer-btn svg { width: 17px; height: 17px; stroke: currentColor; stroke-width: 2.3; fill: none; stroke-linecap: round; stroke-linejoin: round; }
.customer-btn-primary { background: var(--cust-blue); color: #FFFFFF !important; box-shadow: 0 12px 24px rgba(37,99,235,.22); }
.customer-btn-primary:hover { background: var(--cust-blue-dark); transform: translateY(-1px); }
.customer-btn-light { background: #FFFFFF; color: var(--cust-black) !important; border-color: var(--cust-border); }
.customer-btn-light:hover { border-color: #BFDBFE; color: var(--cust-blue) !important; }

.customer-table-card { border: 1px solid var(--cust-border); border-radius: 24px; background: var(--cust-white); box-shadow: var(--cust-shadow); overflow: hidden; }
.customer-table-top { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 20px 22px; border-bottom: 1px solid var(--cust-border); background: #FFFFFF; }
.customer-table-top h2 { margin: 0; color: var(--cust-black); font-size: 1.13rem; line-height: 1.15; font-weight: 700; letter-spacing: -0.035em; }
.customer-table-top p { margin: 5px 0 0; color: var(--cust-muted); font-size: .84rem; line-height: 1.4; font-weight: 760; }

.customer-table-scroll { overflow-x: auto; }
.customer-record-table { width: 100%; min-width: 930px; border-collapse: separate; border-spacing: 0; }
.customer-record-table th { padding: 13px 20px; background: #F8FAFC; color: #7C8798; border-bottom: 1px solid var(--cust-border); font-size: .72rem; font-weight: 700; letter-spacing: .075em; line-height: 1; text-transform: uppercase; text-align: left; white-space: nowrap; }
.customer-record-table td { padding: 16px 20px; border-bottom: 1px solid #EDF2F7; color: #334155; font-size: .9rem; font-weight: 760; vertical-align: middle; }
.customer-record-table tbody tr:last-child td { border-bottom: 0; }
.customer-record-table tbody tr:hover { background: #F8FBFF; }

.customer-person-cell { display: flex; align-items: center; gap: 12px; min-width: 230px; }
.customer-avatar { width: 44px; height: 44px; display: inline-flex; align-items: center; justify-content: center; flex: 0 0 44px; border-radius: 15px; background: var(--cust-blue-soft); color: var(--cust-blue); border: 1px solid #DBEAFE; font-size: .95rem; line-height: 1; font-weight: 700; text-transform: uppercase; }
.customer-person-name { display: block; color: var(--cust-black); font-size: .94rem; line-height: 1.2; font-weight: 700; letter-spacing: -0.02em; }
.customer-person-meta { display: block; margin-top: 4px; color: var(--cust-muted); font-size: .78rem; line-height: 1.25; font-weight: 760; }

.customer-vehicle-badge { display: inline-flex; align-items: center; min-height: 34px; padding: 0 12px; border-radius: 999px; background: #F1F5F9; color: var(--cust-black); border: 1px solid var(--cust-border); font-size: .82rem; line-height: 1; letter-spacing: .045em; font-weight: 700; white-space: nowrap; }

.customer-expiry-main { display: block; color: var(--cust-black); font-size: .9rem; font-weight: 920; line-height: 1.2; white-space: nowrap; }
.customer-expiry-sub { display: block; margin-top: 4px; color: var(--cust-muted); font-size: .76rem; font-weight: 760; white-space: nowrap; }

.customer-status-pill { display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-height: 34px; padding: 0 12px; border-radius: 999px; border: 1px solid; font-size: .78rem; line-height: 1; font-weight: 700; white-space: nowrap; }
.customer-status-pill::before { content: ""; width: 7px; height: 7px; border-radius: 999px; background: currentColor; }
.customer-status-active   { color: #166534; background: #F0FDF4; border-color: #BBF7D0; }
.customer-status-expiring { color: #A16207; background: #FFFBEB; border-color: #FDE68A; }
.customer-status-expired  { color: #B91C1C; background: #FEF2F2; border-color: #FECACA; }

.customer-actions { display: flex; align-items: center; gap: 8px; flex-wrap: nowrap; }
.customer-action-btn { min-height: 36px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 0 11px; border-radius: 12px; border: 1px solid var(--cust-border); background: #FFFFFF; color: var(--cust-black) !important; text-decoration: none !important; font-size: .78rem; font-weight: 920; line-height: 1; white-space: nowrap; transition: .18s ease; }
.customer-action-btn svg { width: 14px; height: 14px; }
.customer-action-btn:hover { border-color: #BFDBFE; color: var(--cust-blue) !important; background: #F8FBFF; }
.customer-action-whatsapp { color: #166534 !important; background: #F0FDF4; border-color: #BBF7D0; }
.customer-action-danger { color: #B91C1C !important; background: #FEF2F2; border-color: #FECACA; }

.customer-empty { padding: 42px 22px; text-align: center; }
.customer-empty-icon { width: 62px; height: 62px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 14px; border-radius: 22px; background: var(--cust-blue-soft); color: var(--cust-blue); border: 1px solid #DBEAFE; }
.customer-empty-icon svg { width: 28px; height: 28px; stroke: currentColor; stroke-width: 2.1; fill: none; stroke-linecap: round; stroke-linejoin: round; }
.customer-empty strong { display: block; color: var(--cust-black); font-size: 1.08rem; font-weight: 700; letter-spacing: -0.025em; }
.customer-empty p { max-width: 440px; margin: 8px auto 18px; color: var(--cust-muted); font-size: .9rem; line-height: 1.65; font-weight: 720; }

@media (max-width: 1180px) {
    .customers-stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .customer-filter-card { grid-template-columns: minmax(220px, 1fr) 180px auto; }
}
@media (max-width: 760px) {
    .customers-toolbar-card, .customer-table-top { grid-template-columns: 1fr; align-items: stretch; }
    .customers-toolbar-card { display: block; }
    .customers-toolbar-card .customer-btn { width: 100%; margin-top: 14px; }
    .customers-stats-grid { grid-template-columns: 1fr; }
    .customer-filter-card { grid-template-columns: 1fr; }
    .customer-filter-card .customer-btn, .customer-table-top .customer-btn { width: 100%; }
}

/* ── WhatsApp Modal ── */
#wa-modal-overlay { display: none; position: fixed; inset: 0; background: rgba(11,16,32,0.55); backdrop-filter: blur(4px); z-index: 9998; align-items: center; justify-content: center; }
#wa-modal-overlay.open { display: flex; }
#wa-modal { background: #fff; border-radius: 24px; padding: 28px 28px 24px; max-width: 440px; width: calc(100% - 32px); box-shadow: 0 24px 64px rgba(11,16,32,.18); position: relative; animation: waModalIn .22s ease; }
@keyframes waModalIn { from { opacity: 0; transform: translateY(12px) scale(.97); } to { opacity: 1; transform: none; } }

.wa-modal-head { display: flex; align-items: center; gap: 13px; margin-bottom: 18px; }
.wa-modal-icon { width: 48px; height: 48px; border-radius: 16px; background: #F0FDF4; border: 1px solid #BBF7D0; display: inline-flex; align-items: center; justify-content: center; flex: 0 0 auto; }
.wa-modal-icon svg { width: 24px; height: 24px; }
.wa-modal-head strong { display: block; color: #0B1020; font-size: 1.05rem; font-weight: 700; letter-spacing: -0.02em; }
.wa-modal-head span { display: block; color: #697386; font-size: .82rem; margin-top: 3px; font-weight: 760; }

.wa-modal-info { background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 16px; padding: 14px 16px; margin-bottom: 20px; display: flex; flex-direction: column; gap: 9px; }
.wa-modal-info-row { display: flex; align-items: center; justify-content: space-between; font-size: .84rem; }
.wa-modal-info-row span:first-child { color: #697386; font-weight: 780; }
.wa-modal-info-row span:last-child { color: #0B1020; font-weight: 700; }

.wa-modal-actions { display: flex; gap: 10px; }
.wa-modal-btn { flex: 1; min-height: 46px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; border-radius: 14px; border: 1px solid transparent; font-size: .9rem; font-weight: 920; cursor: pointer; transition: .18s ease; line-height: 1; }
.wa-modal-btn svg { width: 16px; height: 16px; }
.wa-modal-btn-send { background: #16A34A; color: #fff; box-shadow: 0 8px 20px rgba(22,163,74,.22); }
.wa-modal-btn-send:hover:not(:disabled) { background: #15803D; transform: translateY(-1px); }
.wa-modal-btn-send:disabled { opacity: .65; cursor: not-allowed; }
.wa-modal-btn-cancel { background: #fff; color: #0B1020; border-color: #E2E8F0; }
.wa-modal-btn-cancel:hover { border-color: #BFDBFE; color: #2563EB; }

/* ── Toast ── */
#wa-toast { position: fixed; bottom: 28px; right: 28px; z-index: 9999; display: flex; align-items: center; gap: 12px; padding: 14px 18px; border-radius: 16px; font-size: .88rem; font-weight: 820; box-shadow: 0 12px 36px rgba(11,16,32,.16); max-width: 380px; opacity: 0; transform: translateY(12px); transition: opacity .25s ease, transform .25s ease; pointer-events: none; }
#wa-toast.show { opacity: 1; transform: none; pointer-events: auto; }
#wa-toast.toast-success { background: #F0FDF4; border: 1.5px solid #BBF7D0; color: #166534; }
#wa-toast.toast-error   { background: #FEF2F2; border: 1.5px solid #FECACA; color: #B91C1C; }
#wa-toast svg { width: 20px; height: 20px; flex: 0 0 auto; stroke: currentColor; stroke-width: 2.2; fill: none; stroke-linecap: round; stroke-linejoin: round; }

/* ── Import Modal ── */
#import-modal-overlay { display: none; position: fixed; inset: 0; background: rgba(11,16,32,0.55); backdrop-filter: blur(4px); z-index: 9998; align-items: center; justify-content: center; }
#import-modal-overlay.open { display: flex; }
#import-modal { background: #fff; border-radius: 24px; padding: 28px; max-width: 520px; width: calc(100% - 32px); box-shadow: 0 24px 64px rgba(11,16,32,.18); position: relative; animation: importModalIn .22s ease; max-height: 90vh; display: flex; flex-direction: column; }
@keyframes importModalIn { from { opacity: 0; transform: translateY(12px) scale(.97); } to { opacity: 1; transform: none; } }

.import-modal-scroll { overflow-y: auto; padding-right: 4px; margin-right: -4px; flex: 1; }
.import-modal-head { display: flex; align-items: center; gap: 13px; margin-bottom: 18px; }
.import-modal-icon { width: 48px; height: 48px; border-radius: 16px; background: var(--cust-blue-soft); border: 1px solid #DBEAFE; display: inline-flex; align-items: center; justify-content: center; flex: 0 0 auto; color: var(--cust-blue); }
.import-modal-icon svg { width: 24px; height: 24px; stroke: currentColor; stroke-width: 2.2; fill: none; stroke-linecap: round; stroke-linejoin: round; }
.import-modal-head strong { display: block; color: #0B1020; font-size: 1.05rem; font-weight: 700; letter-spacing: -0.02em; }
.import-modal-head span { display: block; color: #697386; font-size: .82rem; margin-top: 3px; font-weight: 760; }

.import-dropzone { border: 2px dashed #CBD5E1; border-radius: 16px; padding: 24px 16px; text-align: center; cursor: pointer; transition: border-color .18s ease, background-color .18s ease; background: #F8FAFC; margin-bottom: 16px; }
.import-dropzone:hover, .import-dropzone.dragover { border-color: var(--cust-blue); background: var(--cust-blue-soft); }
.import-dropzone svg { width: 36px; height: 36px; color: #64748B; margin-bottom: 8px; stroke: currentColor; stroke-width: 2; fill: none; stroke-linecap: round; stroke-linejoin: round; }
.import-dropzone strong { display: block; font-size: .9rem; color: #0B1020; font-weight: 920; }
.import-dropzone span { display: block; font-size: .78rem; color: #697386; margin-top: 4px; font-weight: 760; }
.import-dropzone input[type="file"] { display: none; }

.import-instructions { background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 16px; padding: 14px; margin-bottom: 16px; font-size: .8rem; font-weight: 760; color: #334155; line-height: 1.5; }
.import-instructions h4 { margin: 0 0 6px 0; font-size: .84rem; font-weight: 700; color: #0B1020; }
.import-instructions ul { margin: 0; padding-left: 18px; }
.import-instructions li { margin-bottom: 4px; }

.import-selected-file { display: none; align-items: center; justify-content: space-between; background: var(--cust-blue-soft); border: 1px solid #DBEAFE; border-radius: 14px; padding: 10px 14px; margin-bottom: 16px; font-size: .84rem; font-weight: 820; color: var(--cust-blue); }
.import-selected-file-info { display: flex; align-items: center; gap: 8px; }
.import-selected-file-info svg { width: 16px; height: 16px; stroke: currentColor; stroke-width: 2.2; fill: none; stroke-linecap: round; stroke-linejoin: round; }
.import-selected-file-remove { cursor: pointer; color: #EF4444; font-weight: 700; font-size: 1.1rem; border: none; background: none; padding: 0 4px; display: inline-flex; align-items: center; }

.import-results { display: none; margin-top: 16px; border-radius: 16px; padding: 14px; border: 1px solid var(--cust-border); font-size: .82rem; }
.import-results.success { background: #F0FDF4; border-color: #BBF7D0; color: #166534; }
.import-results.error { background: #FEF2F2; border-color: #FECACA; color: #B91C1C; }
.import-results-summary { font-weight: 700; margin-bottom: 6px; }
.import-results-errors { max-height: 120px; overflow-y: auto; font-family: monospace; font-size: .75rem; margin-top: 8px; padding-top: 8px; border-top: 1px solid rgba(0,0,0,0.06); }
.import-results-errors div { margin-bottom: 4px; line-height: 1.4; color: #B91C1C; }

.import-modal-actions { display: flex; gap: 10px; margin-top: 16px; }
.import-modal-btn { flex: 1; min-height: 46px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; border-radius: 14px; border: 1px solid transparent; font-size: .9rem; font-weight: 920; cursor: pointer; transition: .18s ease; line-height: 1; }
.import-modal-btn svg { width: 16px; height: 16px; }
.import-modal-btn-upload { background: var(--cust-blue); color: #fff; box-shadow: 0 8px 20px rgba(37,99,235,.22); }
.import-modal-btn-upload:hover:not(:disabled) { background: var(--cust-blue-dark); transform: translateY(-1px); }
.import-modal-btn-upload:disabled { opacity: .65; cursor: not-allowed; }
.import-modal-btn-cancel { background: #fff; color: #0B1020; border-color: #E2E8F0; }
.import-modal-btn-cancel:hover { border-color: #BFDBFE; color: #2563EB; }
</style>

<div class="customers-page">
    @if(session('success'))
        <div class="alert alert-success" style="background:#F0FDF4; border:1px solid #ABEFC6; color:#15803D; padding:14px 18px; border-radius:16px; font-size:13.5px; font-weight:600; margin-bottom:18px; display:flex; align-items:center; gap:8px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning" style="background:#FFFBEB; border:1px solid #FDE68A; color:#B45309; padding:14px 18px; border-radius:16px; font-size:13.5px; font-weight:600; margin-bottom:18px; display:flex; align-items:center; gap:8px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            {{ session('warning') }}
        </div>
    @endif

    @if(session('danger'))
        <div class="alert alert-danger" style="background:#FEF2F2; border:1px solid #FECACA; color:#B91C1C; padding:14px 18px; border-radius:16px; font-size:13.5px; font-weight:600; margin-bottom:18px; display:flex; align-items:center; gap:8px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            {{ session('danger') }}
        </div>
    @endif

    @if (!$waService->checkQuota(auth()->id())['allowed'])
        <div style="display:flex;align-items:flex-start;gap:12px;padding:14px 18px;border-radius:16px;background:#FFF1F1;border:1.5px solid #FECDCA;color:#B42318;font-size:13.5px;font-weight:780;margin-bottom:18px;">
            <svg viewBox="0 0 24 24" style="width:20px;height:20px;stroke:currentColor;stroke-width:2.2;fill:none;stroke-linecap:round;stroke-linejoin:round;flex:0 0 auto;margin-top:1px;"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
            <span>⚠️ <strong>WhatsApp limit reached.</strong> {{ $waService->checkQuota(auth()->id())['reason'] }} <a href="{{ url('/pricing') }}" style="color:#B42318;font-weight: 700;text-decoration:underline;">View Plans →</a></span>
        </div>
    @endif

    <div class="customers-toolbar-card">
        <div class="customers-toolbar-title">
            <span class="customers-toolbar-icon">
                <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </span>
            <div>
                <strong>Customer and Vehicle Directory</strong>
                <span>Keep all customer mobiles, vehicle numbers and PUC expiry details in one organized place.</span>
            </div>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <button type="button" class="customer-btn customer-btn-light" id="import-csv-trigger-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                Import Excel/CSV
            </button>
            <a class="customer-btn customer-btn-primary" href="{{ route('customers.create') }}">
                <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Add Customer
            </a>
        </div>
    </div>

    <div class="customers-stats-grid">
        <a href="{{ route('customers.index') }}" class="customer-stat-card" style="--stat-color:#2563EB;">
            <span class="customer-stat-icon"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="2"></rect><rect x="14" y="3" width="7" height="7" rx="2"></rect><rect x="14" y="14" width="7" height="7" rx="2"></rect><rect x="3" y="14" width="7" height="7" rx="2"></rect></svg></span>
            <span class="customer-stat-content"><b>{{ $totalAll }}</b><span>Total Records</span></span>
        </a>
        <a href="{{ route('customers.index', ['status' => 'active']) }}" class="customer-stat-card" style="--stat-color:#16A34A;">
            <span class="customer-stat-icon"><svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"></path></svg></span>
            <span class="customer-stat-content"><b>{{ $totalActive }}</b><span>Active Records</span></span>
        </a>
        <a href="{{ route('customers.index', ['status' => 'expiring']) }}" class="customer-stat-card" style="--stat-color:#D97706;">
            <span class="customer-stat-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg></span>
            <span class="customer-stat-content"><b>{{ $totalExpiring }}</b><span>Expiring in 7 Days</span></span>
        </a>
        <a href="{{ route('customers.index', ['status' => 'expired']) }}" class="customer-stat-card" style="--stat-color:#DC2626;">
            <span class="customer-stat-icon"><svg viewBox="0 0 24 24"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg></span>
            <span class="customer-stat-content"><b>{{ $totalExpired }}</b><span>Expired Records</span></span>
        </a>
    </div>

    <form class="customer-filter-card" method="get" action="{{ route('customers.index') }}">
        <div class="customer-search-wrap">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>
            <input name="q" placeholder="Search by customer, mobile or vehicle number" value="{{ $q }}">
        </div>
        <div class="customer-select-wrap">
            <svg viewBox="0 0 24 24"><path d="M3 6h18"></path><path d="M7 12h10"></path><path d="M10 18h4"></path></svg>
            <select name="status">
                <option value="">All Status</option>
                <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                <option value="expiring" {{ $status === 'expiring' ? 'selected' : '' }}>Expiring 7 Days</option>
                <option value="expired" {{ $status === 'expired' ? 'selected' : '' }}>Expired</option>
            </select>
        </div>
        <button class="customer-btn customer-btn-primary" type="submit">
            <svg viewBox="0 0 24 24"><path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"></path></svg>
            Filter
        </button>
        @if ($q !== '' || $status !== '')
            <a href="{{ route('customers.index') }}" class="customer-btn customer-btn-light">Clear</a>
        @endif
    </form>

    <div class="customer-table-card">
        <div class="customer-table-top">
            <div>
                <h2>Vehicle Records</h2>
                <p>{{ $records->total() }} record{{ $records->total() !== 1 ? 's' : '' }} found.</p>
            </div>
            <a href="{{ route('customers.create') }}" class="customer-btn customer-btn-light">
                <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Add Customer
            </a>
        </div>

        <div class="customer-table-scroll">
            <table class="customer-record-table">
                <thead>
                    <tr>
                        <th>Customer / Contact</th>
                        <th>Vehicle Details</th>
                        <th>PUC Certificate No.</th>
                        <th>Validity Date</th>
                        <th>Days Left</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $r)
                        @php
                            $badge = cust_status_pill($r->expiry_date->toDateString());
                            $formattedVehicle = $waService->formatVehicleNumber($r->vehicle_number);
                            $initial = strtoupper(substr(trim($r->customer_name ?: 'C'), 0, 1));
                            $days = (int) \Carbon\Carbon::today()->diffInDays($r->expiry_date, false);
                        @endphp
                        <tr>
                            <td>
                                <div class="customer-person-cell">
                                    <span class="customer-avatar">{{ $initial }}</span>
                                    <div>
                                        <strong class="customer-person-name">{{ $r->customer_name ?: 'Customer' }}</strong>
                                        <span class="customer-person-meta">{{ $r->customer_mobile }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="customer-vehicle-badge">{{ $formattedVehicle }}</span>
                                <div style="font-size:11.5px; color:var(--cust-muted); font-weight:600; margin-top:4px;">
                                    {{ $r->vehicle_type }} · {{ $r->fuel_type ?: 'Unknown' }}
                                </div>
                            </td>
                            <td>
                                <span style="font-family:monospace; font-weight:700;">{{ $r->puc_certificate_number ?: '-' }}</span>
                            </td>
                            <td>
                                <strong class="customer-expiry-main">{{ $r->expiry_date->format('d M Y') }}</strong>
                                <span class="customer-expiry-sub">Issued: {{ $r->issue_date->format('d M Y') }}</span>
                            </td>
                            <td>
                                @if ($days < 0)
                                    <span style="color:#dc2626; font-weight:700;">{{ abs($days) }} days overdue</span>
                                @elseif ($days === 0)
                                    <span style="color:#ca8a04; font-weight:700;">Expires Today</span>
                                @else
                                    <span style="color:#475569; font-weight:700;">{{ $days }} days left</span>
                                @endif
                            </td>
                            <td>
                                <span class="customer-status-pill {{ $badge[1] }}">{{ $badge[0] }}</span>
                            </td>
                            <td>
                                <div class="customer-actions" style="justify-content: flex-end;">
                                    <button class="customer-action-btn customer-action-whatsapp wa-send-btn" 
                                            data-id="{{ $r->id }}"
                                            data-name="{{ $r->customer_name ?: 'Customer' }}"
                                            data-phone="{{ $r->customer_mobile }}"
                                            data-vehicle="{{ $formattedVehicle }}"
                                            data-expiry="{{ $r->expiry_date->format('d M Y') }}"
                                            data-status="{{ $badge[0] }}">
                                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                                        Remind
                                    </button>
                                    <a class="customer-action-btn" href="{{ route('customers.edit', $r->id) }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                        Edit
                                    </a>
                                    <form action="{{ route('customers.destroy', $r->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this record?');" style="display:inline-block; margin:0;">
                                        @csrf
                                        <button class="customer-action-btn customer-action-danger" type="submit">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="customer-empty">
                                    <span class="customer-empty-icon">
                                        <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                                    </span>
                                    <strong>No Records Found</strong>
                                    <p>Start adding customer vehicle PUC validity details to configure automated WhatsApp renewal messages.</p>
                                    <a class="customer-btn customer-btn-primary" href="{{ route('customers.create') }}">Add Customer Record</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($records->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid var(--cust-border); display: flex; align-items: center; justify-content: space-between; background: #FFFFFF; font-size:13.5px; font-weight:600;">
                <div style="color: var(--cust-muted);">
                    Showing {{ $records->firstItem() }} to {{ $records->lastItem() }} of {{ $records->total() }} records
                </div>
                <div style="display: flex; gap: 8px;">
                    @if ($records->onFirstPage())
                        <button class="customer-btn customer-btn-light" disabled style="opacity: 0.5; cursor: not-allowed;">Previous</button>
                    @else
                        <a href="{{ $records->previousPageUrl() }}" class="customer-btn customer-btn-light">Previous</a>
                    @endif

                    @if ($records->hasMorePages())
                        <a href="{{ $records->nextPageUrl() }}" class="customer-btn customer-btn-light">Next</a>
                    @else
                        <button class="customer-btn customer-btn-light" disabled style="opacity: 0.5; cursor: not-allowed;">Next</button>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<!-- ─── WhatsApp Sending Confirmation Modal ─── -->
<div id="wa-modal-overlay">
    <div id="wa-modal">
        <div class="wa-modal-head">
            <span class="wa-modal-icon">
                <svg viewBox="0 0 24 24" fill="currentColor" style="color:#16A34A;width:24px;height:24px;"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
            </span>
            <div>
                <strong>Send WhatsApp Reminder</strong>
                <span>Confirm outbound details</span>
            </div>
        </div>

        <div class="wa-modal-info">
            <div class="wa-modal-info-row">
                <span>Customer</span>
                <span id="wa-modal-name">-</span>
            </div>
            <div class="wa-modal-info-row">
                <span>Mobile</span>
                <span id="wa-modal-phone">-</span>
            </div>
            <div class="wa-modal-info-row">
                <span>Vehicle</span>
                <span id="wa-modal-vehicle">-</span>
            </div>
            <div class="wa-modal-info-row">
                <span>Expiry Date</span>
                <span id="wa-modal-expiry">-</span>
            </div>
            <div class="wa-modal-info-row">
                <span>Status</span>
                <span id="wa-modal-status">-</span>
            </div>
        </div>

        <div class="wa-modal-actions">
            <button type="button" class="wa-modal-btn wa-modal-btn-cancel" id="wa-modal-cancel">Cancel</button>
            <button type="button" class="wa-modal-btn wa-modal-btn-send" id="wa-modal-confirm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
                Send Now
            </button>
        </div>
    </div>
</div>

<!-- ─── WhatsApp Toast Notification ─── -->
<div id="wa-toast">
    <svg viewBox="0 0 24 24" fill="none" id="wa-toast-icon"></svg>
    <span id="wa-toast-msg"></span>
</div>

<!-- ─── CSV Import Modal ─── -->
<div id="import-modal-overlay">
    <div id="import-modal">
        <div class="import-modal-scroll">
            <div class="import-modal-head">
                <span class="import-modal-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                </span>
                <div>
                    <strong>Import Excel/CSV File</strong>
                    <span>Import customer records in bulk</span>
                </div>
            </div>

            <!-- Drag & Drop Zone -->
            <div class="import-dropzone" id="csv-dropzone">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                <strong>Drag and drop your CSV file here</strong>
                <span>or click to browse from folders</span>
                <input type="file" id="csv-file-input" accept=".csv, .txt">
            </div>

            <!-- Selected File info -->
            <div class="import-selected-file" id="import-file-display">
                <div class="import-selected-file-info">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    <span id="import-filename">filename.csv</span>
                </div>
                <button type="button" class="import-selected-file-remove" id="import-file-remove">&times;</button>
            </div>

            <!-- Instructions -->
            <div class="import-instructions">
                <h4>CSV Columns Required:</h4>
                <ul>
                    <li>Column 1: <strong>Customer Name</strong> (Optional)</li>
                    <li>Column 2: <strong>Mobile Number</strong> (Required, 10 digits)</li>
                    <li>Column 3: <strong>Vehicle Number</strong> (Required, e.g. GJ03AB1234)</li>
                    <li>Column 4: <strong>Vehicle Type</strong> (Bike, Car, Auto, Truck, Bus, Other)</li>
                    <li>Column 5: <strong>Fuel Type</strong> (Petrol, Diesel, CNG, LPG, Hybrid, Electric)</li>
                    <li>Column 6: <strong>Certificate No</strong> (Optional)</li>
                    <li>Column 7: <strong>Issue Date</strong> (YYYY-MM-DD)</li>
                    <li>Column 8: <strong>Expiry Date</strong> (YYYY-MM-DD)</li>
                    <li>Column 9: <strong>Notes</strong> (Optional)</li>
                </ul>
            </div>

            <!-- Loading Indicator -->
            <div id="import-loading-container" style="display:none; text-align:center; padding: 24px 0;">
                <div style="display:inline-block; width: 34px; height: 34px; border: 3px solid rgba(37,99,235,0.15); border-top-color: var(--cust-blue); border-radius:50%; animation: importSpin 0.75s linear infinite;"></div>
                <p style="margin-top: 10px; color: var(--cust-muted); font-size: 13px; font-weight:700;">Uploading and parsing data. Please wait...</p>
            </div>

            <!-- Import results -->
            <div class="import-results" id="import-results-container">
                <div id="import-results-msg" style="font-weight:700;"></div>
                <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top:12px; font-weight:700; text-align:center;">
                    <div style="background:#F0FDF4; padding:8px; border-radius:10px; border:1px solid #BBF7D0; color:#15803D;">
                        <span style="display:block; font-size:18px;" id="stat-inserted">0</span>
                        <span style="font-size:11px; color:#166534;">Added</span>
                    </div>
                    <div style="background:#EFF6FF; padding:8px; border-radius:10px; border:1px solid #DBEAFE; color:#1D4ED8;">
                        <span style="display:block; font-size:18px;" id="stat-updated">0</span>
                        <span style="font-size:11px; color:#1E40AF;">Updated</span>
                    </div>
                    <div style="background:#FEF2F2; padding:8px; border-radius:10px; border:1px solid #FECACA; color:#B91C1C;">
                        <span style="display:block; font-size:18px;" id="stat-failed">0</span>
                        <span style="font-size:11px; color:#991B1B;">Skipped</span>
                    </div>
                </div>

                <div id="import-errors-section" style="display:none; margin-top: 12px;">
                    <div style="font-weight:700; color: #B91C1C; margin-bottom: 6px;">Errors / Warnings:</div>
                    <div class="import-results-errors" id="import-results-errors"></div>
                </div>
            </div>

        </div>

        <div class="import-modal-actions">
            <button type="button" class="import-modal-btn import-modal-btn-cancel" id="import-modal-close-btn">Close</button>
            <button type="button" class="import-modal-btn import-modal-btn-upload" id="import-modal-submit-btn" disabled>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                Upload & Import
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // ─── WhatsApp AJAX Modal Script ───
    const overlay    = document.getElementById('wa-modal-overlay');
    const cancelBtn  = document.getElementById('wa-modal-cancel');
    const confirmBtn = document.getElementById('wa-modal-confirm');
    const toast      = document.getElementById('wa-toast');
    const toastMsg   = document.getElementById('wa-toast-msg');
    const toastIcon  = document.getElementById('wa-toast-icon');

    let activeRecordId = null;
    let toastTimer     = null;

    document.querySelectorAll('.wa-send-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            activeRecordId = this.dataset.id;
            document.getElementById('wa-modal-name').textContent    = this.dataset.name;
            document.getElementById('wa-modal-phone').textContent   = this.dataset.phone;
            document.getElementById('wa-modal-vehicle').textContent = this.dataset.vehicle;
            document.getElementById('wa-modal-expiry').textContent  = this.dataset.expiry;
            document.getElementById('wa-modal-status').textContent  = this.dataset.status;
            overlay.classList.add('open');
        });
    });

    function closeModal() {
        overlay.classList.remove('open');
        activeRecordId = null;
        confirmBtn.disabled = false;
        confirmBtn.innerHTML =
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;">' +
            '<line x1="22" y1="2" x2="11" y2="13"></line>' +
            '<polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg> Send Now';
    }

    cancelBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', function (e) { if (e.target === overlay) closeModal(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });

    confirmBtn.addEventListener('click', function () {
        if (!activeRecordId) return;
        confirmBtn.disabled  = true;
        confirmBtn.innerHTML = '⏳ Sending…';

        const formData = new FormData();
        formData.append('record_ids[]', activeRecordId);
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route('dashboard.bulk-whatsapp') }}', { 
            method: 'POST', 
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function (res) { return res.json(); })
        .then(function (data) { 
            closeModal(); 
            showToast(data.success, data.message); 
        })
        .catch(function () { 
            closeModal(); 
            showToast(false, 'Network error. Please try again.'); 
        });
    });

    function showToast(success, message) {
        clearTimeout(toastTimer);
        toast.className = 'show ' + (success ? 'toast-success' : 'toast-error');
        toastMsg.textContent = message;
        toastIcon.innerHTML = success
            ? '<polyline points="20 6 9 17 4 12"></polyline>'
            : '<line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>';
        toastTimer = setTimeout(function () { toast.className = ''; }, 4500);
    }

    // ─── Excel/CSV Import Module ───
    const triggerBtn = document.getElementById('import-csv-trigger-btn');
    const importOverlay = document.getElementById('import-modal-overlay');
    const importCloseBtn = document.getElementById('import-modal-close-btn');
    const importSubmitBtn = document.getElementById('import-modal-submit-btn');
    const dropzone = document.getElementById('csv-dropzone');
    const fileInput = document.getElementById('csv-file-input');
    const fileDisplay = document.getElementById('import-file-display');
    const filenameSpan = document.getElementById('import-filename');
    const fileRemoveBtn = document.getElementById('import-file-remove');
    const resultsContainer = document.getElementById('import-results-container');
    const resultsMsg = document.getElementById('import-results-msg');
    const resultsErrors = document.getElementById('import-results-errors');

    let selectedFile = null;
    let shouldRefreshOnClose = false;

    // Show modal
    triggerBtn.addEventListener('click', function () {
        importOverlay.classList.add('open');
    });

    // Close modal function
    function closeImportModal() {
        importOverlay.classList.remove('open');
        resetImportModal();
        if (shouldRefreshOnClose) {
            window.location.reload();
        }
    }

    importCloseBtn.addEventListener('click', closeImportModal);
    importOverlay.addEventListener('click', function (e) { if (e.target === importOverlay) closeImportModal(); });
    
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && importOverlay.classList.contains('open')) {
            closeImportModal();
        }
    });

    // File Drag & Drop Events
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('dragover');
        }, false);
    });

    dropzone.addEventListener('drop', function (e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files.length > 0) {
            handleFileSelect(files[0]);
        }
    });

    dropzone.addEventListener('click', function () {
        fileInput.click();
    });

    fileInput.addEventListener('change', function () {
        if (this.files.length > 0) {
            handleFileSelect(this.files[0]);
        }
    });

    fileRemoveBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        resetFileSelection();
    });

    function handleFileSelect(file) {
        if (file.name.toLowerCase().endsWith('.csv') || file.name.toLowerCase().endsWith('.txt')) {
            selectedFile = file;
            filenameSpan.textContent = file.name + ' (' + formatBytes(file.size) + ')';
            dropzone.style.display = 'none';
            fileDisplay.style.display = 'flex';
            importSubmitBtn.disabled = false;
            resultsContainer.style.display = 'none';
            resultsContainer.className = 'import-results';
        } else {
            alert('Invalid file format. Please select a .csv file.');
            resetFileSelection();
        }
    }

    function resetFileSelection() {
        selectedFile = null;
        fileInput.value = '';
        dropzone.style.display = 'block';
        fileDisplay.style.display = 'none';
        importSubmitBtn.disabled = true;
    }

    function resetImportModal() {
        if (!shouldRefreshOnClose) {
            resetFileSelection();
            resultsContainer.style.display = 'none';
            importSubmitBtn.style.display = 'inline-flex';
            importSubmitBtn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg> Upload & Import';
            importSubmitBtn.disabled = true;
            dropzone.style.display = 'block';
            document.querySelector('.import-instructions').style.display = 'block';
            document.getElementById('import-loading-container').style.display = 'none';
            importCloseBtn.textContent = 'Close';
            importCloseBtn.className = 'import-modal-btn import-modal-btn-cancel';
        }
    }

    function formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Submit / Upload via Ajax
    importSubmitBtn.addEventListener('click', function () {
        if (!selectedFile) return;

        dropzone.style.display = 'none';
        document.querySelector('.import-instructions').style.display = 'none';
        fileDisplay.style.display = 'none';
        importSubmitBtn.style.display = 'none';
        
        const loadingContainer = document.getElementById('import-loading-container');
        loadingContainer.style.display = 'block';

        const formData = new FormData();
        formData.append('csv_file', selectedFile);
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route('customers.import') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            loadingContainer.style.none;
            resultsContainer.style.display = 'block';
            resultsErrors.innerHTML = '';
            document.getElementById('import-errors-section').style.display = 'none';

            if (data.success) {
                resultsContainer.style.borderColor = '#BBF7D0';
                resultsContainer.style.background = '#FFFFFF';
                resultsMsg.innerHTML = '<span style="color: #15803D;">✅ Import Completed Successfully!</span>';
                shouldRefreshOnClose = true;
                
                document.getElementById('stat-inserted').textContent = data.inserted || 0;
                document.getElementById('stat-updated').textContent = data.updated || 0;
                document.getElementById('stat-failed').textContent = data.skipped || 0;

                if (data.errors && data.errors.length > 0) {
                    document.getElementById('import-errors-section').style.display = 'block';
                    data.errors.forEach(err => {
                        const div = document.createElement('div');
                        div.style.color = '#B91C1C';
                        div.style.marginBottom = '4px';
                        div.textContent = '⚠️ ' + err;
                        resultsErrors.appendChild(div);
                    });
                }
                
                importCloseBtn.textContent = 'Refresh & Close';
                importCloseBtn.className = 'import-modal-btn import-modal-btn-upload';
            } else {
                dropzone.style.display = 'block';
                document.querySelector('.import-instructions').style.display = 'block';
                fileDisplay.style.display = 'flex';
                importSubmitBtn.style.display = 'inline-flex';
                importSubmitBtn.disabled = false;
                importSubmitBtn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg> Upload & Import';

                resultsContainer.style.borderColor = '#FECACA';
                resultsContainer.style.background = '#FEF2F2';
                resultsMsg.textContent = 'Import Failed: ' + data.message;
                resultsMsg.style.color = '#B91C1C';
            }
        })
        .catch(err => {
            loadingContainer.style.display = 'none';
            dropzone.style.display = 'block';
            document.querySelector('.import-instructions').style.display = 'block';
            fileDisplay.style.display = 'flex';
            importSubmitBtn.style.display = 'inline-flex';
            importSubmitBtn.disabled = false;
            importSubmitBtn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg> Upload & Import';

            resultsContainer.style.display = 'block';
            resultsContainer.style.borderColor = '#FECACA';
            resultsContainer.style.background = '#FEF2F2';
            resultsMsg.textContent = 'An error occurred during import: ' + err.message;
            resultsMsg.style.color = '#B91C1C';
        });
    });
});
</script>
@endsection
