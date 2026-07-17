@extends('layouts.dashboard')

@section('title', 'Profile')

@section('page-title', 'Profile')
@section('page-subtitle', 'Manage your account details, center details, and security settings.')

@section('content')
<style>
    /* ===== PROFILE STYLES (MATCHES THE DESIGN SYSTEM) ===== */
    .pf-wrap { max-width: 100%; padding: 0 0 28px; }

    /* Page header override */
    .pf-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 24px; flex-wrap: wrap; }
    .pf-breadcrumb { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: var(--blue); letter-spacing: .04em; text-transform: uppercase; margin-bottom: 8px; }
    .pf-breadcrumb::before { content: ''; display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: var(--blue); }
    .pf-page-title { font-size: clamp(20px, 2.8vw, 28px); font-weight: 700; color: var(--black); letter-spacing: -.03em; line-height: 1.1; }
    .pf-page-sub { margin-top: 5px; font-size: 13.5px; color: var(--muted); font-weight: 500; }

    /* Hero card */
    .pf-hero {
        background: #fff; border: 1px solid var(--line);
        border-radius: 16px; padding: 20px 24px;
        box-shadow: 0 1px 3px rgba(15,23,42,.07); margin-bottom: 20px;
        display: flex; align-items: center; gap: 18px; flex-wrap: wrap;
        position: relative; overflow: hidden;
    }
    .pf-hero::before {
        content: ''; position: absolute; inset: 0 0 auto 0;
        height: 3px; background: linear-gradient(90deg, var(--blue), var(--blue-dark));
    }
    .pf-hero-avatar {
        width: 68px; height: 68px; border-radius: 12px;
        background: var(--blue-lt); border: 1px solid var(--blue-bd);
        color: var(--blue); display: flex; align-items: center;
        justify-content: center; font-size: 26px; font-weight: 700;
        flex: 0 0 auto; overflow: hidden;
    }
    .pf-hero-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .pf-hero-info { flex: 1; min-width: 0; }
    .pf-hero-name { font-size: 20px; font-weight: 700; color: var(--black); letter-spacing: -.025em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pf-hero-sub { font-size: 13px; color: var(--muted); font-weight: 500; margin-top: 3px; }
    .pf-hero-pills { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .pf-pill {
        display: inline-flex; align-items: center; gap: 6px;
        height: 32px; padding: 0 12px; border-radius: 999px;
        font-size: 12px; font-weight: 700; white-space: nowrap;
        background: #fff; border: 1px solid var(--line);
        color: var(--text); box-shadow: 0 1px 2px rgba(15,23,42,.04);
    }
    .pf-pill svg { width: 13px; height: 13px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

    .pf-status-pill { display: inline-flex; align-items: center; gap: 6px; height: 32px; padding: 0 12px; border-radius: 999px; font-size: 12px; font-weight: 700; }
    .pf-status-pill::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .pf-status-pill.verified { background: #F0FDF4; color: #15803D; border: 1px solid #ABEFC6; }
    .pf-status-pill.pending  { background: #FFFBEB; color: #B45309; border: 1px solid #FDE68A; }

    /* Main layout */
    .pf-layout { display: grid; grid-template-columns: minmax(0,1fr) 320px; gap: 20px; align-items: start; }
    .pf-left { display: grid; gap: 16px; }

    /* Tab nav */
    .pf-tabs { display: flex; background: #fff; border: 1px solid var(--line); border-radius: 16px; padding: 4px; gap: 4px; box-shadow: 0 1px 2px rgba(15,23,42,.04); }
    .pf-tab {
        flex: 1; height: 38px; border: none; border-radius: 12px;
        background: none; font-family: inherit; font-size: 13px; font-weight: 700;
        color: var(--muted); cursor: pointer; display: flex; align-items: center;
        justify-content: center; gap: 7px; transition: all .15s; white-space: nowrap;
        padding: 0 10px;
    }
    .pf-tab:hover { background: var(--bg); color: var(--text); }
    .pf-tab.active { background: var(--blue); color: #fff; box-shadow: 0 1px 3px rgba(37,99,235,.22); }
    .pf-tab svg { width: 14px; height: 14px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

    /* Panel */
    .pf-panel { display: none; }
    .pf-panel.active { display: grid; gap: 16px; }

    /* Card */
    .pf-card {
        background: #fff; border: 1px solid var(--line);
        border-radius: 16px; box-shadow: 0 1px 3px rgba(15,23,42,.07); overflow: hidden;
        transition: box-shadow .15s;
    }
    .pf-card:hover { box-shadow: 0 10px 24px rgba(15,23,42,.07); }
    .pf-card-head {
        display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
        padding: 18px 20px; border-bottom: 1px solid var(--line);
    }
    .pf-card-title { font-size: 15px; font-weight: 700; color: var(--black); }
    .pf-card-sub   { font-size: 12.5px; color: var(--muted); font-weight: 500; margin-top: 3px; }
    .pf-card-icon {
        width: 38px; height: 38px; border-radius: 8px; flex: 0 0 auto;
        background: var(--blue-lt); border: 1px solid var(--blue-bd);
        color: var(--blue); display: flex; align-items: center; justify-content: center;
    }
    .pf-card-icon svg { width: 18px; height: 18px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .pf-card-body { padding: 20px; }

    /* Form */
    .pf-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .pf-form-group { margin-bottom: 15px; }
    .pf-form-group:last-child { margin-bottom: 0; }
    .pf-form-group label { display: block; margin-bottom: 7px; font-size: 12.5px; font-weight: 700; color: var(--text); }
    .pf-req { color: var(--blue); }
    .pf-input-wrap { position: relative; }
    .pf-input-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: var(--muted); pointer-events: none; }
    .pf-input-icon svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; display: block; }

    .pf-form-group input:not([type=file]),
    .pf-form-group textarea,
    .pf-form-group select {
        width: 100%; height: 44px; border-radius: 8px;
        border: 1px solid var(--line); background: #fff; color: var(--text);
        padding: 0 12px 0 38px; outline: none; font-family: inherit;
        font-size: 13.5px; font-weight: 600; transition: border-color .15s, box-shadow .15s;
        box-shadow: 0 1px 0 rgba(11, 16, 32, 0.03);
    }
    .pf-form-group textarea {
        height: auto !important;
        min-height: 90px !important;
        padding: 12px 12px 12px 38px !important;
        resize: vertical !important;
        line-height: 1.6 !important;
        display: block !important;
    }
    .pf-textarea-icon {
        top: 14px !important;
        transform: none !important;
    }
    .pf-form-group input::placeholder, .pf-form-group textarea::placeholder { color: var(--muted); font-weight: 500; }
    .pf-form-group input:focus, .pf-form-group textarea:focus, .pf-form-group select:focus {
        border-color: var(--blue-bd); box-shadow: 0 0 0 3px rgba(37,99,235,.12);
    }
    .pf-form-group input:disabled { background: var(--bg); color: var(--muted); cursor: not-allowed; }
    .pf-input-wrap:focus-within .pf-input-icon { color: var(--blue); }
    .pf-hint { margin-top: 6px; font-size: 12px; color: var(--muted); font-weight: 500; line-height: 1.4; }

    /* Avatar upload */
    .pf-avatar-upload { display: flex; align-items: center; gap: 14px; }
    .pf-avatar-preview {
        width: 64px; height: 64px; border-radius: 10px; flex: 0 0 auto;
        background: var(--blue-lt); border: 1px solid var(--blue-bd);
        color: var(--blue); display: flex; align-items: center; justify-content: center;
        font-size: 24px; font-weight: 700; overflow: hidden;
    }
    .pf-avatar-preview img { width: 100%; height: 100%; object-fit: cover; }
    .pf-avatar-right { flex: 1; }
    .pf-upload-btn {
        display: inline-flex; align-items: center; gap: 7px;
        height: 36px; padding: 0 14px; border-radius: 8px;
        background: var(--blue-lt); border: 1px solid var(--blue-bd);
        color: var(--blue); font-family: inherit; font-size: 13px; font-weight: 700;
        cursor: pointer; transition: background .14s;
    }
    .pf-upload-btn:hover { background: rgba(37,99,235,.12); }
    .pf-upload-btn svg { width: 14px; height: 14px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .pf-upload-name { font-size: 12px; color: var(--muted); margin-top: 5px; font-weight: 500; }
    input[type=file]#profile_image { display: none; }

    /* Password toggle */
    .pf-pw-toggle {
        position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
        width: 30px; height: 30px; border: none; border-radius: 6px;
        background: var(--bg); color: var(--muted); cursor: pointer;
        display: flex; align-items: center; justify-content: center; transition: background .14s;
    }
    .pf-pw-toggle:hover { background: var(--line); }
    .pf-pw-toggle svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .pf-pw-toggle .ico-off { display: none; }
    .pf-pw-toggle.show .ico-on { display: none; }
    .pf-pw-toggle.show .ico-off { display: block; }

    /* Strength bar */
    .pf-strength { margin-top: 8px; }
    .pf-strength-bar { height: 5px; border-radius: 999px; background: var(--bg); overflow: hidden; margin-bottom: 5px; }
    .pf-strength-fill { height: 100%; border-radius: 999px; width: 0; transition: width .25s, background .25s; }
    .pf-strength-fill.s1 { width: 25%; background: #EF4444; }
    .pf-strength-fill.s2 { width: 50%; background: #F59E0B; }
    .pf-strength-fill.s3 { width: 75%; background: #D97706; }
    .pf-strength-fill.s4 { width: 100%; background: #16A34A; }
    .pf-strength-label { font-size: 11.5px; font-weight: 700; color: var(--muted); }
    .pf-strength-label.s1 { color: #EF4444; }
    .pf-strength-label.s2 { color: #F59E0B; }
    .pf-strength-label.s3 { color: #D97706; }
    .pf-strength-label.s4 { color: #16A34A; }

    /* Buttons */
    .pf-btn-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-top: 6px; }
    .pf-btn {
        height: 42px; border-radius: 8px; display: inline-flex;
        align-items: center; justify-content: center; gap: 7px; padding: 0 18px;
        font-family: inherit; font-size: 13px; font-weight: 700; cursor: pointer;
        transition: all .14s; white-space: nowrap; border: 1px solid transparent;
    }
    .pf-btn-primary { background: var(--blue); color: #fff; border-color: var(--blue); box-shadow: 0 2px 8px rgba(37,99,235,.22); }
    .pf-btn-primary:hover { background: var(--blue-dark); box-shadow: 0 4px 12px rgba(37,99,235,.3); transform: translateY(-1px); }
    .pf-btn-ghost { background: #fff; color: var(--text); border-color: var(--line); box-shadow: 0 1px 2px rgba(15,23,42,.04); }
    .pf-btn-ghost:hover { background: var(--bg); border-color: var(--muted); transform: translateY(-1px); }
    .pf-btn svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; flex: 0 0 auto; }

    /* Divider */
    .pf-divider { display: flex; align-items: center; gap: 10px; margin: 4px 0 15px; color: var(--muted); font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
    .pf-divider::before, .pf-divider::after { content: ''; flex: 1; height: 1px; background: var(--line); }

    /* Right sidebar */
    .pf-right { display: grid; gap: 16px; position: sticky; top: 96px; align-self: start; }

    /* Account card */
    .pf-account-head {
        background: var(--blue); padding: 18px 20px; position: relative; overflow: hidden;
    }
    .pf-account-head::after {
        content: ''; position: absolute; right: -40px; top: -40px;
        width: 120px; height: 120px; border-radius: 50%;
        background: rgba(255,255,255,.08); pointer-events: none;
    }
    .pf-account-head .pf-card-title { color: #fff; }
    .pf-account-head .pf-card-sub   { color: rgba(255,255,255,.75); }
    .pf-account-head .pf-card-icon  { background: rgba(255,255,255,.15); border-color: rgba(255,255,255,.2); color: #fff; }
    .pf-side-profile { display: flex; align-items: center; gap: 12px; padding: 14px; border-radius: 12px; background: var(--blue-lt); border: 1px solid var(--blue-bd); margin-bottom: 14px; }
    .pf-side-avatar { width: 46px; height: 46px; border-radius: 8px; background: var(--blue); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 700; flex: 0 0 auto; overflow: hidden; }
    .pf-side-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .pf-side-name { font-size: 14px; font-weight: 700; color: var(--black); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .pf-side-center { font-size: 12px; color: var(--muted); font-weight: 500; margin-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    /* Stats grid */
    .pf-stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px; }
    .pf-stat { padding: 12px; background: var(--bg); border: 1px solid var(--line); border-radius: 8px; }
    .pf-stat-val { font-size: 14px; font-weight: 700; color: var(--black); }
    .pf-stat-val.verified { color: #15803D; }
    .pf-stat-val.pending  { color: #B45309; }
    .pf-stat-lbl { font-size: 10px; color: var(--muted); font-weight: 700; margin-top: 4px; letter-spacing: .04em; text-transform: uppercase; }

    /* Info rows */
    .pf-info-list { display: grid; gap: 8px; }
    .pf-info-row { display: flex; align-items: center; gap: 10px; padding: 11px 12px; background: var(--bg); border: 1px solid var(--line); border-radius: 8px; }
    .pf-info-ico { width: 32px; height: 32px; border-radius: 8px; background: var(--blue-lt); border: 1px solid var(--blue-bd); color: var(--blue); display: flex; align-items: center; justify-content: center; flex: 0 0 auto; }
    .pf-info-ico svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .pf-info-lbl { font-size: 10px; color: var(--muted); font-weight: 700; letter-spacing: .05em; text-transform: uppercase; }
    .pf-info-val { font-size: 13px; font-weight: 700; color: var(--text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-top: 1px; }

    /* Security list */
    .pf-check-list { display: grid; gap: 8px; }
    .pf-check-item { display: flex; align-items: flex-start; gap: 9px; padding: 11px 12px; background: var(--bg); border: 1px solid var(--line); border-radius: 8px; font-size: 12.5px; color: var(--text); font-weight: 500; line-height: 1.5; }
    .pf-check-item svg { width: 15px; height: 15px; flex: 0 0 auto; margin-top: 2px; stroke: var(--blue); fill: none; stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round; }

    /* Alert Banner */
    .pf-alert { display: flex; align-items: flex-start; gap: 12px; padding: 14px 18px; border-radius: 12px; border: 1px solid; margin-bottom: 20px; font-size: 13.5px; font-weight: 600; line-height: 1.5; }
    .pf-alert.success { background: #F0FDF4; border-color: #ABEFC6; color: #15803D; }
    .pf-alert.danger  { background: #FFF1F2; border-color: #FECACA; color: #B91C1C; }
    .pf-alert svg { width: 17px; height: 17px; flex: 0 0 auto; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; margin-top: 1px; }

    /* Responsive */
    @media (max-width: 960px) {
        .pf-layout { grid-template-columns: 1fr; }
        .pf-right { position: static; }
    }
    @media (max-width: 700px) {
        .pf-wrap { padding: 16px 14px 40px; }
        .pf-form-grid { grid-template-columns: 1fr; gap: 0; }
        .pf-tabs { overflow-x: auto; }
        .pf-tab { font-size: 12px; padding: 0 8px; }
        .pf-btn { width: 100%; }
        .pf-btn-row { flex-direction: column; }
        .pf-hero { flex-direction: column; align-items: flex-start; }
    }
</style>

<div class="pf-wrap">
    <!-- Flash Messages / Validation Errors -->
    @if (session()->has('success'))
        <div class="pf-alert success" role="alert">
            <svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="pf-alert danger" role="alert">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
            <div style="flex:1;">
                <ul style="margin:0; padding-left:16px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Page Header -->
    <div class="pf-header">
        <div>
            <div class="pf-breadcrumb">Account Settings</div>
            <h1 class="pf-page-title">Profile</h1>
            <p class="pf-page-sub">Manage your account, center details and security.</p>
        </div>
        <div class="pf-header-right">
            <a href="{{ route('dashboard') }}" class="pf-btn pf-btn-ghost" style="height:36px;font-size:12.5px;">
                <svg viewBox="0 0 24 24"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
                Dashboard
            </a>
        </div>
    </div>

    <!-- Hero -->
    <div class="pf-hero">
        <div class="pf-hero-avatar">
            <img src="{{ $profileImage }}" alt="Profile" onerror="this.style.display='none'; document.getElementById('pfHeroInitial').style.display='flex';" style="{{ $profileImage ? '' : 'display:none;' }}">
            <span id="pfHeroInitial" style="{{ $profileImage ? 'display:none;' : 'display:flex; width: 100%; height: 100%; align-items: center; justify-content: center;' }}">{{ $initial }}</span>
        </div>
        <div class="pf-hero-info">
            <div class="pf-hero-name">{{ $ownerName }}</div>
            <div class="pf-hero-sub">{{ $centerName }} &middot; Center Owner</div>
        </div>
        <div class="pf-hero-pills">
            <span class="pf-pill">
                <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.8 19.8 0 0 1 3 5.18 2 2 0 0 1 5 3h3a2 2 0 0 1 2 1.72c.12.9.33 1.77.62 2.6a2 2 0 0 1-.45 2.11L8.91 10.7a16 16 0 0 0 4.39 4.39l1.27-1.26a2 2 0 0 1 2.11-.45c.83.29 1.7.5 2.6.62A2 2 0 0 1 22 16.92z"/></svg>
                {{ $user->mobile }}
            </span>
            <span class="pf-status-pill {{ $isVerified ? 'verified' : 'pending' }}">
                {{ $isVerified ? 'Verified' : 'Not Verified' }}
            </span>
            <span class="pf-pill">{{ ucfirst($status) }}</span>
        </div>
    </div>

    <!-- Layout -->
    <div class="pf-layout">
        <!-- LEFT COLUMN -->
        <div class="pf-left">
            <!-- Tabs -->
            <div class="pf-tabs" role="tablist">
                <button class="pf-tab active" data-tab="profile" role="tab">
                    <svg viewBox="0 0 24 24"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
                    Edit Profile
                </button>
                <button class="pf-tab" data-tab="password" role="tab">
                    <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Password
                </button>
            </div>

            <!-- Panel: Edit Profile -->
            <div class="pf-panel active" id="tab-profile">
                <div class="pf-card">
                    <div class="pf-card-head">
                        <div>
                            <div class="pf-card-title">Edit Profile</div>
                            <div class="pf-card-sub">Update your name, center details and profile picture.</div>
                        </div>
                        <div class="pf-card-icon">
                            <svg viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                        </div>
                    </div>
                    <div class="pf-card-body">
                        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" autocomplete="off" novalidate id="pfProfileForm">
                            @csrf

                            <div class="pf-form-grid">
                                <div class="pf-form-group">
                                    <label for="first_name">First Name <span class="pf-req">*</span></label>
                                    <div class="pf-input-wrap">
                                        <span class="pf-input-icon"><svg viewBox="0 0 24 24"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg></span>
                                        <input id="first_name" name="first_name" required maxlength="80" value="{{ old('first_name', $user->first_name) }}" placeholder="First name">
                                    </div>
                                </div>
                                <div class="pf-form-group">
                                    <label for="last_name">Last Name <span class="pf-req">*</span></label>
                                    <div class="pf-input-wrap">
                                        <span class="pf-input-icon"><svg viewBox="0 0 24 24"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg></span>
                                        <input id="last_name" name="last_name" required maxlength="80" value="{{ old('last_name', $user->last_name) }}" placeholder="Last name">
                                    </div>
                                </div>
                            </div>

                            <div class="pf-form-group">
                                <label for="center_name">PUC Center Name <span class="pf-req">*</span></label>
                                <div class="pf-input-wrap">
                                    <span class="pf-input-icon"><svg viewBox="0 0 24 24"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg></span>
                                    <input id="center_name" name="center_name" required maxlength="120" value="{{ old('center_name', $user->center_name) }}" placeholder="Your PUC center name">
                                </div>
                            </div>

                            <div class="pf-form-group">
                                <label for="center_address">PUC Center Address <span class="pf-req">*</span></label>
                                <div class="pf-input-wrap">
                                    <span class="pf-input-icon pf-textarea-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"/><circle cx="12" cy="10" r="3"/></svg></span>
                                    <textarea id="center_address" name="center_address" required placeholder="Enter full center address (included in WhatsApp reminders)">{{ old('center_address', $user->center_address) }}</textarea>
                                </div>
                                <p class="pf-hint">Include landmark, city, and pincode so customers can easily navigate to your center.</p>
                            </div>

                            <div class="pf-form-group">
                                <label for="pf_mobile_display">Mobile Number</label>
                                <div class="pf-input-wrap">
                                    <span class="pf-input-icon"><svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.8 19.8 0 0 1 3 5.18 2 2 0 0 1 5 3h3a2 2 0 0 1 2 1.72c.12.9.33 1.77.62 2.6a2 2 0 0 1-.45 2.11L8.91 10.7a16 16 0 0 0 4.39 4.39l1.27-1.26a2 2 0 0 1 2.11-.45c.83.29 1.7.5 2.6.62A2 2 0 0 1 22 16.92z"/></svg></span>
                                    <input id="pf_mobile_display" value="{{ $user->mobile }}" disabled>
                                </div>
                                <p class="pf-hint">Mobile is locked — used for OTP login.</p>
                            </div>

                            <div class="pf-form-group">
                                <label>Profile Picture</label>
                                <div class="pf-avatar-upload">
                                    <div class="pf-avatar-preview" id="pfAvatarPreview">
                                        <img src="{{ $profileImage }}" alt="Avatar" id="pfAvatarImg" onerror="this.style.display='none'; document.getElementById('pfAvatarInitial').style.display='flex';" style="{{ $profileImage ? '' : 'display:none;' }}">
                                        <span id="pfAvatarInitial" style="{{ $profileImage ? 'display:none;' : 'display:flex; width: 100%; height: 100%; align-items: center; justify-content: center;' }}">{{ $initial }}</span>
                                    </div>
                                    <div class="pf-avatar-right">
                                        <input type="file" id="profile_image" name="profile_image" accept="image/*">
                                        <label for="profile_image" class="pf-upload-btn">
                                            <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                            Choose image
                                        </label>
                                        <div class="pf-upload-name" id="pfFileName">JPG or PNG · max 2MB</div>
                                    </div>
                                </div>
                            </div>

                            <div class="pf-btn-row">
                                <button type="submit" class="pf-btn pf-btn-primary">
                                    <svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                    Save Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Panel: Password -->
            <div class="pf-panel" id="tab-password">
                <div class="pf-card">
                    <div class="pf-card-head">
                        <div>
                            <div class="pf-card-title">Change Password</div>
                            <div class="pf-card-sub">Min. 8 characters, one uppercase, one number.</div>
                        </div>
                        <div class="pf-card-icon">
                            <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                    </div>
                    <div class="pf-card-body">
                        <form method="POST" action="{{ route('profile.password') }}" autocomplete="off" novalidate id="pfPasswordForm">
                            @csrf

                            <div class="pf-form-group">
                                <label for="current_password">Current Password <span class="pf-req">*</span></label>
                                <div class="pf-input-wrap">
                                    <span class="pf-input-icon"><svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
                                    <input id="current_password" type="password" name="current_password" placeholder="Enter current password" autocomplete="current-password" style="padding-right:44px;">
                                    <button type="button" class="pf-pw-toggle" data-target="current_password" aria-label="Toggle password visibility">
                                        <svg class="ico-on" viewBox="0 0 24 24"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <svg class="ico-off" viewBox="0 0 24 24"><path d="M3 3l18 18"/><path d="M10.58 10.58A2 2 0 0 0 12 14a2 2 0 0 0 1.42-.58"/><path d="M9.88 4.25A10.7 10.7 0 0 1 12 4c6.5 0 10 8 10 8a18 18 0 0 1-3.1 4.4"/><path d="M6.6 6.6A18 18 0 0 0 2 12s3.5 8 10 8a10.8 10.8 0 0 0 4.18-.83"/></svg>
                                    </button>
                                </div>
                            </div>

                            <div class="pf-divider">New Password</div>

                            <div class="pf-form-group">
                                <label for="new_password">New Password <span class="pf-req">*</span></label>
                                <div class="pf-input-wrap">
                                    <span class="pf-input-icon"><svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></span>
                                    <input id="new_password" type="password" name="new_password" placeholder="Minimum 8 characters" autocomplete="new-password" style="padding-right:44px;">
                                    <button type="button" class="pf-pw-toggle" data-target="new_password" aria-label="Toggle password visibility">
                                        <svg class="ico-on" viewBox="0 0 24 24"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <svg class="ico-off" viewBox="0 0 24 24"><path d="M3 3l18 18"/><path d="M10.58 10.58A2 2 0 0 0 12 14a2 2 0 0 0 1.42-.58"/><path d="M9.88 4.25A10.7 10.7 0 0 1 12 4c6.5 0 10 8 10 8a18 18 0 0 1-3.1 4.4"/><path d="M6.6 6.6A18 18 0 0 0 2 12s3.5 8 10 8a10.8 10.8 0 0 0 4.18-.83"/></svg>
                                    </button>
                                </div>
                                <div class="pf-strength" id="pfStrengthWrap" style="display:none;">
                                    <div class="pf-strength-bar"><div class="pf-strength-fill" id="pfStrengthFill"></div></div>
                                    <span class="pf-strength-label" id="pfStrengthLabel">Weak</span>
                                </div>
                                <p class="pf-hint">Must include an uppercase letter and a number.</p>
                            </div>

                            <div class="pf-form-group">
                                <label for="confirm_password">Confirm New Password <span class="pf-req">*</span></label>
                                <div class="pf-input-wrap">
                                    <span class="pf-input-icon"><svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg></span>
                                    <input id="confirm_password" type="password" name="confirm_password" placeholder="Re-enter new password" autocomplete="new-password" style="padding-right:44px;">
                                    <button type="button" class="pf-pw-toggle" data-target="confirm_password" aria-label="Toggle password visibility">
                                        <svg class="ico-on" viewBox="0 0 24 24"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <svg class="ico-off" viewBox="0 0 24 24"><path d="M3 3l18 18"/><path d="M10.58 10.58A2 2 0 0 0 12 14a2 2 0 0 0 1.42-.58"/><path d="M9.88 4.25A10.7 10.7 0 0 1 12 4c6.5 0 10 8 10 8a18 18 0 0 1-3.1 4.4"/><path d="M6.6 6.6A18 18 0 0 0 2 12s3.5 8 10 8a10.8 10.8 0 0 0 4.18-.83"/></svg>
                                    </button>
                                </div>
                                <p class="pf-hint" id="pfMatchHint" style="display:none;"></p>
                            </div>

                            <div class="pf-btn-row">
                                <button type="submit" class="pf-btn pf-btn-primary">
                                    <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
                                    Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN (SIDEBAR) -->
        <aside class="pf-right">
            <!-- Account Details Card -->
            <div class="pf-card">
                <div class="pf-account-head pf-card-head" style="border-bottom:1px solid rgba(255,255,255,.15);">
                    <div>
                        <div class="pf-card-title">Account Details</div>
                        <div class="pf-card-sub">Read-only account information.</div>
                    </div>
                    <div class="pf-card-icon">
                        <svg viewBox="0 0 24 24"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                </div>
                <div class="pf-card-body">
                    <div class="pf-side-profile">
                        <div class="pf-side-avatar">
                            <img src="{{ $profileImage }}" alt="Avatar" onerror="this.style.display='none'; document.getElementById('pfSideInitial').style.display='flex';" style="{{ $profileImage ? '' : 'display:none;' }}">
                            <span id="pfSideInitial" style="{{ $profileImage ? 'display:none;' : 'display:flex; width: 100%; height: 100%; align-items: center; justify-content: center;' }}">{{ $initial }}</span>
                        </div>
                        <div>
                            <div class="pf-side-name">{{ $ownerName }}</div>
                            <div class="pf-side-center">{{ $centerName }}</div>
                        </div>
                    </div>

                    <div class="pf-stats-grid">
                        <div class="pf-stat">
                            <div class="pf-stat-val">{{ $totalRecords }}</div>
                            <div class="pf-stat-lbl">Total Records</div>
                        </div>
                        <div class="pf-stat">
                            <div class="pf-stat-val">{{ $activeRecords }}</div>
                            <div class="pf-stat-lbl">Active Records</div>
                        </div>
                    </div>

                    <div class="pf-info-list">
                        <div class="pf-info-row">
                            <div class="pf-info-ico"><svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
                            <div style="min-width:0;flex:1;">
                                <div class="pf-info-lbl">Verified Status</div>
                                <div class="pf-info-val {{ $isVerified ? 'verified' : 'pending' }}" style="color: {{ $isVerified ? '#15803D' : '#B45309' }};">
                                    {{ $isVerified ? '✓ Verified Mobile' : '⚠ Action Required' }}
                                </div>
                            </div>
                        </div>
                        <div class="pf-info-row">
                            <div class="pf-info-ico"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></div>
                            <div style="min-width:0;flex:1;">
                                <div class="pf-info-lbl">Created At</div>
                                <div class="pf-info-val">{{ $createdAt }}</div>
                            </div>
                        </div>
                        <div class="pf-info-row">
                            <div class="pf-info-ico"><svg viewBox="0 0 24 24"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg></div>
                            <div style="min-width:0;flex:1;">
                                <div class="pf-info-lbl">Last Modified</div>
                                <div class="pf-info-val">{{ $updatedAt }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Highlights Card -->
            {{-- <div class="pf-card">
                <div class="pf-card-head">
                    <div>
                        <div class="pf-card-title">Security Settings</div>
                        <div class="pf-card-sub">General workspace constraints.</div>
                    </div>
                </div>
                <div class="pf-card-body">
                    <div class="pf-check-list">
                        <div class="pf-check-item">
                            <svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
                            <span>Passwords are encrypted using Argon2/Bcrypt hash values.</span>
                        </div>
                        <div class="pf-check-item">
                            <svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
                            <span>Mobile OTP challenge must complete before active access is granted.</span>
                        </div>
                        <div class="pf-check-item">
                            <svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
                            <span>Meta Cloud credentials can only be edited via .env declarations.</span>
                        </div>
                    </div>
                </div>
            </div> --}}
        </aside>
    </div>
</div>

@push('scripts')
<script>
(function(){
    // Tab switching
    var tabs = document.querySelectorAll('.pf-tab');
    var panels = document.querySelectorAll('.pf-panel');

    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            tabs.forEach(function(t) { t.classList.remove('active'); });
            panels.forEach(function(p) { p.classList.remove('active'); });

            tab.classList.add('active');
            var target = document.getElementById('tab-' + tab.dataset.tab);
            if (target) target.classList.add('active');
        });
    });

    // File name display & preview
    var fileInput = document.getElementById('profile_image');
    var fileNameDisp = document.getElementById('pfFileName');
    var avatarPreview = document.getElementById('pfAvatarPreview');

    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (fileInput.files && fileInput.files[0]) {
                var file = fileInput.files[0];
                fileNameDisp.textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';

                var reader = new FileReader();
                reader.onload = function(e) {
                    avatarPreview.innerHTML = '<img src="' + e.target.result + '" alt="Avatar" id="pfAvatarImg">';
                };
                reader.readAsDataURL(file);
            } else {
                fileNameDisp.textContent = 'JPG or PNG · max 2MB';
            }
        });
    }

    // Password visibility toggles
    document.querySelectorAll('.pf-pw-toggle').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var targetId = btn.dataset.target;
            var input = document.getElementById(targetId);
            if (input) {
                if (input.type === 'password') {
                    input.type = 'text';
                    btn.classList.add('show');
                } else {
                    input.type = 'password';
                    btn.classList.remove('show');
                }
            }
        });
    });

    // Password strength check
    var newPw = document.getElementById('new_password');
    var strengthWrap = document.getElementById('pfStrengthWrap');
    var strengthFill = document.getElementById('pfStrengthFill');
    var strengthLabel = document.getElementById('pfStrengthLabel');

    if (newPw) {
        newPw.addEventListener('input', function() {
            var val = newPw.value;
            if (val === '') {
                strengthWrap.style.display = 'none';
                return;
            }
            strengthWrap.style.display = 'block';

            var score = 0;
            if (val.length >= 8) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;

            var labels = {0: 'Weak', 1: 'Weak', 2: 'Medium', 3: 'Strong', 4: 'Very Strong'};
            var classes = {0: 's1', 1: 's1', 2: 's2', 3: 's3', 4: 's4'};

            strengthFill.className = 'pf-strength-fill ' + classes[score];
            strengthLabel.className = 'pf-strength-label ' + classes[score];
            strengthLabel.textContent = labels[score];
        });
    }

    // Confirm password match check
    var confirmPw = document.getElementById('confirm_password');
    var matchHint = document.getElementById('pfMatchHint');

    if (confirmPw && newPw) {
        function checkMatch() {
            if (confirmPw.value === '') {
                matchHint.style.display = 'none';
                return;
            }
            matchHint.style.display = 'block';
            if (confirmPw.value === newPw.value) {
                matchHint.style.color = '#15803D';
                matchHint.textContent = '✓ Passwords match';
            } else {
                matchHint.style.color = '#EF4444';
                matchHint.textContent = '✗ Passwords do not match';
            }
        }
        confirmPw.addEventListener('input', checkMatch);
        newPw.addEventListener('input', checkMatch);
    }
})();
</script>
@endpush
@endsection
