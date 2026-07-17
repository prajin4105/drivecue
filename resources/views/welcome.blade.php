@extends('layouts.app')

@section('title', 'Smart PUC Renewal Management Software')

@section('content')
<style>
/* ─── Index Page Base ─── */
body { background:#FFFFFF; }
#hp *, #hp *::before, #hp *::after { box-sizing:border-box; }
#hp { font-family:'Inter',ui-sans-serif,sans-serif; color:#0B1020; background:#FFFFFF; }

/* ─── HERO ─── */
#hp .hero-section {
    position:relative; overflow:hidden;
    padding:72px 0 64px;
    background:
        radial-gradient(circle at 82% 12%, rgba(36,87,230,0.14), transparent 36%),
        linear-gradient(180deg,#FFFFFF 0%,#F7FAFF 100%);
    border-bottom:1px solid rgba(11,16,32,0.08);
}
#hp .hero-section::before {
    content:""; position:absolute;
    width:600px; height:600px; border-radius:50%;
    top:-200px; right:-180px;
    background:radial-gradient(circle,rgba(36,87,230,0.12),transparent 70%);
    pointer-events:none;
}
#hp .hero-section::after {
    content:""; position:absolute;
    inset:0; pointer-events:none;
    background:
        radial-gradient(circle at 8% 80%,rgba(36,87,230,0.08),transparent 35%),
        linear-gradient(180deg,transparent 60%,rgba(255,255,255,0.22));
}
#hp .hero-inner {
    position:relative; z-index:2;
    display:grid; grid-template-columns:1fr 480px;
    gap:52px; align-items:center;
}
#hp .hero-badge {
    display:inline-flex; align-items:center; gap:8px;
    padding:8px 14px; border-radius:999px;
    background:#FFFFFF; border:1px solid rgba(36,87,230,0.18);
    color:#334155; font-size:12.5px; font-weight:700; margin-bottom:20px;
    letter-spacing:0.04em; text-transform:uppercase;
    box-shadow:0 8px 22px rgba(11,16,32,0.06);
}
#hp .hero-badge::before {
    content:""; width:7px; height:7px; border-radius:50%; flex-shrink:0;
    background:#2457E6; box-shadow:0 0 0 4px rgba(36,87,230,0.14);
    animation:heroPulse 2.2s ease infinite;
}
@keyframes heroPulse {
    0%,100%{ box-shadow:0 0 0 4px rgba(36,87,230,0.14); }
    50%{ box-shadow:0 0 0 7px rgba(36,87,230,0.06); }
}
#hp .hero-h1 {
    font-size:clamp(36px,4.4vw,64px); line-height:1.04;
    letter-spacing:-0.065em; font-weight: 700; color:#0B1020; margin:0 0 16px;
}
#hp .hero-h1 span {
    background:none;
    -webkit-background-clip:initial; background-clip:initial; color:#2457E6;
}
#hp .hero-lead {
    color:rgba(11,16,32,0.68); font-size:17px;
    line-height:1.72; font-weight:500; margin:0 0 28px; max-width:580px;
}
#hp .hero-btns { display:flex; gap:12px; flex-wrap:wrap; }
#hp .h-btn {
    display:inline-flex; align-items:center; gap:8px; min-height:50px;
    padding:0 24px; border-radius:13px; font-weight:700; font-size:15px;
    text-decoration:none !important; transition:all 0.22s ease; border:1.5px solid transparent;
    letter-spacing:-0.01em; cursor:pointer;
}
#hp .h-btn-primary {
    background:#2457E6;
    color:#FFFFFF !important; border-color:transparent;
    box-shadow:0 8px 24px rgba(36,87,230,0.28);
}
#hp .h-btn-primary:hover { transform:translateY(-2px); box-shadow:0 14px 36px rgba(36,87,230,0.34); }
#hp .h-btn-outline {
    background:#FFFFFF; color:#0B1020 !important;
    border-color:rgba(11,16,32,0.14);
    box-shadow:0 8px 20px rgba(11,16,32,0.05);
}
#hp .h-btn-outline:hover { background:#F8FAFF; transform:translateY(-2px); border-color:rgba(36,87,230,0.28); }
#hp .hero-chips { display:flex; gap:10px; flex-wrap:wrap; margin-top:24px; }
#hp .hero-chip {
    display:inline-flex; align-items:center; gap:6px;
    padding:6px 12px; border-radius:999px;
    background:#FFFFFF; border:1px solid rgba(11,16,32,0.10);
    color:rgba(11,16,32,0.62); font-size:12.5px; font-weight:600;
    box-shadow:0 7px 18px rgba(11,16,32,0.05);
}
#hp .hero-chip b { color:#2457E6; }

/* Dashboard Mock (right side of hero) */
#hp .dash-mock {
    position:relative; border-radius:22px; overflow:hidden;
    background:#FFFFFF; border:1px solid rgba(11,16,32,0.10);
    backdrop-filter:blur(20px);
    box-shadow:0 32px 80px rgba(11,16,32,0.12), inset 0 1px 0 rgba(255,255,255,0.90);
}
#hp .dash-mock-top {
    padding:14px 16px; background:#FFFFFF;
    border-bottom:1px solid rgba(11,16,32,0.08);
    display:flex; align-items:center; gap:10px;
}
#hp .mock-dots { display:flex; gap:6px; }
#hp .mock-dot {
    width:10px; height:10px; border-radius:50%;
}
#hp .mock-dot:nth-child(1){ background:#2457E6; }
#hp .mock-dot:nth-child(2){ background:#0B1020; opacity:.22; }
#hp .mock-dot:nth-child(3){ background:#0B1020; opacity:.12; }
#hp .mock-top-title {
    flex:1; text-align:center; color:rgba(11,16,32,0.52);
    font-size:11.5px; font-weight:700; letter-spacing:0.04em;
}
#hp .dash-mock-body { padding:16px; display:grid; gap:10px; }

/* Mock Stat Row */
#hp .mock-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; }
#hp .mock-stat {
    padding:12px; border-radius:13px;
    background:#F8FAFF; border:1px solid rgba(11,16,32,0.08);
}
#hp .mock-stat b { display:block; color:#0B1020; font-size:20px; font-weight: 700; letter-spacing:-0.04em; line-height:1; }
#hp .mock-stat span { display:block; color:rgba(11,16,32,0.54); font-size:10.5px; font-weight:600; margin-top:5px; }

/* Mock Bar Chart */
#hp .mock-chart { background:#FFFFFF; border:1px solid rgba(11,16,32,0.08); border-radius:13px; padding:12px; }
#hp .mock-chart-label { color:rgba(11,16,32,0.52); font-size:10px; font-weight:700; letter-spacing:0.06em; text-transform:uppercase; margin-bottom:10px; }
#hp .mock-bars { display:flex; align-items:flex-end; gap:6px; height:52px; }
#hp .mock-bar {
    flex:1; border-radius:5px 5px 0 0;
    background:linear-gradient(180deg,rgba(36,87,230,0.78),rgba(36,87,230,0.45));
    animation:barGrow 1s ease forwards; transform-origin:bottom;
}
@keyframes barGrow { from{ transform:scaleY(0); } to{ transform:scaleY(1); } }

/* Mock Table */
#hp .mock-table { background:#FFFFFF; border:1px solid rgba(11,16,32,0.08); border-radius:13px; overflow:hidden; }
#hp .mock-th { display:grid; grid-template-columns:1fr 80px 70px; gap:8px; padding:8px 12px; border-bottom:1px solid rgba(11,16,32,0.08); background:#F8FAFF; }
#hp .mock-th span { color:rgba(11,16,32,0.48); font-size:9.5px; font-weight:700; letter-spacing:0.06em; text-transform:uppercase; }
#hp .mock-row { display:grid; grid-template-columns:1fr 80px 70px; gap:8px; padding:8px 12px; border-bottom:1px solid rgba(11,16,32,0.06); align-items:center; }
#hp .mock-row:last-child { border-bottom:0; }
#hp .mock-name { display:flex; align-items:center; gap:7px; }
#hp .mock-av {
    width:22px; height:22px; border-radius:7px; flex-shrink:0;
    background:#2457E6;
    display:grid; place-items:center; color:#FFFFFF; font-size:9px; font-weight: 700;
}
#hp .mock-txt { color:#0B1020; font-size:11px; font-weight:700; }
#hp .mock-veh { color:rgba(11,16,32,0.50); font-size:10px; font-weight:700; letter-spacing:0.03em; }
#hp .mock-badge {
    display:inline-block; padding:3px 7px; border-radius:999px; font-size:9.5px; font-weight:700;
}
#hp .mock-badge.warn { background:rgba(36,87,230,0.10); color:#2457E6; border:1px solid rgba(36,87,230,0.18); }
#hp .mock-badge.ok   { background:rgba(36,87,230,0.10); color:#2457E6; border:1px solid rgba(36,87,230,0.18); }
#hp .mock-badge.exp  { background:rgba(11,16,32,0.06); color:#0B1020; border:1px solid rgba(11,16,32,0.10); }

/* Live Indicator */
#hp .mock-live {
    display:flex; align-items:center; gap:6px;
    background:rgba(36,87,230,0.10); border:1px solid rgba(36,87,230,0.18);
    padding:6px 12px; border-radius:999px; font-size:10.5px; font-weight:700; color:#2457E6;
}
#hp .mock-live::before {
    content:""; width:6px; height:6px; border-radius:50%; flex-shrink:0;
    background:#2457E6; animation:heroPulse 2s ease infinite;
}

/* ─── HOW IT WORKS ─── */
#hp .section { padding:80px 0; }
#hp .section-white { background:#FFFFFF; }
#hp .section-gray  { background:#FFFFFF; }
#hp .section-dark  {
    background:#FFFFFF;
    color:#0B1020;
}
#hp .sec-eyebrow {
    display:inline-flex; align-items:center; gap:7px;
    padding:6px 13px; border-radius:999px;
    background:#FFFFFF; border:1px solid rgba(36,87,230,0.18);
    color:#2457E6; font-size:11.5px; font-weight:700; letter-spacing:0.05em; text-transform:uppercase;
    margin-bottom:16px;
    box-shadow:0 8px 22px rgba(11,16,32,0.05);
}
#hp .section-dark .sec-eyebrow { background:#FFFFFF; border-color:rgba(36,87,230,0.18); color:#2457E6; box-shadow:0 8px 22px rgba(11,16,32,0.05); }
#hp .sec-h2 {
    font-size:clamp(26px,3.2vw,44px); color:#0B1020;
    line-height:1.10; letter-spacing:-0.055em; font-weight: 700; margin:0 0 12px;
}
#hp .section-dark .sec-h2 { color:#0B1020; }
#hp .sec-sub { color:rgba(11,16,32,0.62); font-size:16px; line-height:1.70; font-weight:500; margin:0; max-width:660px; }
#hp .section-dark .sec-sub { color:rgba(11,16,32,0.62); }
#hp .sec-head { margin-bottom:44px; }
#hp .sec-head.center { text-align:center; }
#hp .sec-head.center .sec-sub { margin:0 auto; }

/* Steps Grid */
#hp .steps-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
#hp .step-card {
    padding:24px; border-radius:20px;
    background:#FFFFFF; border:1px solid rgba(11,16,32,0.08);
    box-shadow:0 2px 8px rgba(11,16,32,0.04),0 8px 22px rgba(11,16,32,0.04);
    transition:all 0.22s ease; position:relative; overflow:hidden;
}
#hp .step-card::before {
    content:""; position:absolute; inset:0 0 auto 0; height:3px;
    background:#2457E6;
}
#hp .step-card:hover { transform:translateY(-4px); box-shadow:0 12px 40px rgba(11,16,32,0.10); }
#hp .step-no {
    width:44px; height:44px; border-radius:14px;
    display:grid; place-items:center; font-size:18px; font-weight: 700;
    color:#FFFFFF; margin-bottom:14px;
    background:#2457E6;
    box-shadow:0 8px 20px rgba(36,87,230,0.24);
}
#hp .step-card h3 { color:#0B1020; font-size:17px; font-weight: 700; letter-spacing:-0.03em; margin:0 0 8px; }
#hp .step-card p  { color:rgba(11,16,32,0.62); font-size:14px; line-height:1.65; font-weight:500; margin:0; }

/* ─── FEATURES ─── */
#hp .feat-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:18px; }
#hp .feat-card {
    padding:26px; border-radius:20px;
    background:#FFFFFF; border:1px solid rgba(11,16,32,0.08);
    box-shadow:0 2px 8px rgba(11,16,32,0.04),0 8px 22px rgba(11,16,32,0.04);
    transition:all 0.22s ease;
}
#hp .feat-card:hover { transform:translateY(-4px); box-shadow:0 12px 40px rgba(11,16,32,0.10); border-color:rgba(36,87,230,0.20); }
#hp .feat-icon {
    width:50px; height:50px; border-radius:15px;
    display:grid; place-items:center; margin-bottom:15px;
    background:#2457E6;
    color:#FFFFFF; box-shadow:0 8px 20px rgba(36,87,230,0.24);
}
#hp .feat-icon svg { width:22px; height:22px; stroke:currentColor; stroke-width:2; fill:none; stroke-linecap:round; stroke-linejoin:round; }
#hp .feat-card h3 { color:#0B1020; font-size:17px; font-weight: 700; letter-spacing:-0.03em; margin:0 0 8px; }
#hp .feat-card p  { color:rgba(11,16,32,0.62); font-size:14px; line-height:1.65; font-weight:500; margin:0; }

/* ─── METRICS STRIP ─── */
#hp .metrics-strip { display:grid; grid-template-columns:repeat(4,1fr); gap:1px; background:rgba(11,16,32,0.08); border-radius:20px; overflow:hidden; margin-top:44px; border:1px solid rgba(11,16,32,0.10); box-shadow:0 18px 44px rgba(11,16,32,0.08); }
#hp .metric-block { padding:28px 20px; background:#FFFFFF; text-align:center; }
#hp .metric-block b { display:block; color:#0B1020; font-size:clamp(28px,3vw,40px); font-weight: 700; letter-spacing:-0.055em; line-height:1; }
#hp .metric-block span { display:block; color:rgba(11,16,32,0.62); font-size:13px; font-weight:600; margin-top:8px; }

/* ─── PRICING ─── */
#hp .price-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:18px; }
#hp .price-card {
    padding:28px; border-radius:22px;
    background:#FFFFFF; border:1px solid rgba(11,16,32,0.08);
    box-shadow:0 2px 8px rgba(11,16,32,0.04),0 8px 22px rgba(11,16,32,0.04);
    position:relative; overflow:hidden; transition:all 0.22s ease;
}
#hp .price-card::before {
    content:""; position:absolute; inset:0 0 auto 0; height:4px;
    background:#2457E6;
}
#hp .price-card.popular { border-color:rgba(36,87,230,0.26); box-shadow:0 8px 36px rgba(36,87,230,0.14); transform:translateY(-4px); }
#hp .price-label { font-size:12px; font-weight:700; color:#2457E6; letter-spacing:0.06em; text-transform:uppercase; margin-bottom:10px; }
#hp .price-name  { font-size:22px; color:#0B1020; font-weight: 700; letter-spacing:-0.04em; margin-bottom:6px; }
#hp .price-desc  { color:rgba(11,16,32,0.62); font-size:13.5px; line-height:1.60; font-weight:500; margin-bottom:20px; }
#hp .price-price { margin-bottom:20px; }
#hp .price-price b { font-size:40px; color:#0B1020; font-weight: 700; letter-spacing:-0.06em; }
#hp .price-price span { color:rgba(11,16,32,0.62); font-size:13.5px; font-weight:600; }
#hp .price-features { list-style:none; padding:0; margin:0 0 22px; display:grid; gap:9px; }
#hp .price-features li { display:flex; gap:9px; color:rgba(11,16,32,0.78); font-size:14px; font-weight:600; align-items:flex-start; }
#hp .price-features li::before { content:"✓"; color:#2457E6; font-weight: 700; flex-shrink:0; }

/* ─── CTA SECTION ─── */
#hp .cta-box {
    border-radius:28px; padding:56px 48px;
    background:#FFFFFF;
    border:1px solid rgba(11,16,32,0.10);
    box-shadow:0 24px 70px rgba(11,16,32,0.10);
    display:grid; grid-template-columns:1fr auto; gap:28px; align-items:center;
    position:relative; overflow:hidden;
}
#hp .cta-box::before {
    content:""; position:absolute; width:280px; height:280px;
    border-radius:50%; right:-100px; top:-120px; background:rgba(36,87,230,0.10);
}
#hp .cta-box::after {
    content:""; position:absolute; width:200px; height:200px;
    border-radius:50%; left:-80px; bottom:-80px; background:rgba(36,87,230,0.06);
}
#hp .cta-box h2 { position:relative; z-index:1; color:#0B1020; font-size:clamp(24px,3vw,40px); letter-spacing:-0.05em; font-weight: 700; margin:0 0 10px; }
#hp .cta-box p  { position:relative; z-index:1; color:rgba(11,16,32,0.62); font-size:16px; line-height:1.68; font-weight:500; margin:0; max-width:680px; }
#hp .cta-btns   { position:relative; z-index:1; display:flex; flex-direction:column; gap:10px; }
#hp .h-btn-white { background:#2457E6; color:#FFFFFF !important; box-shadow:0 10px 28px rgba(36,87,230,0.22); white-space:nowrap; }
#hp .h-btn-ghost { background:#FFFFFF; color:#0B1020 !important; border-color:rgba(11,16,32,0.14); box-shadow:0 10px 28px rgba(11,16,32,0.06); white-space:nowrap; }

/* ─── RESPONSIVE ─── */
@media (max-width:1100px) {
    #hp .hero-inner { grid-template-columns:1fr 400px; gap:36px; }
    #hp .steps-grid { grid-template-columns:repeat(2,1fr); }
    #hp .metrics-strip { grid-template-columns:repeat(2,1fr); }
}
@media (max-width:960px) {
    #hp .hero-inner, #hp .cta-box { grid-template-columns:1fr; }
    #hp .feat-grid { grid-template-columns:repeat(2,1fr); }
    #hp .price-grid { grid-template-columns:1fr; max-width:480px; margin:0 auto; }
    #hp .price-card.popular { transform:none; }
    #hp .cta-btns { flex-direction:row; }
    #hp .dash-mock { max-width:520px; margin:0 auto; }
}
@media (max-width:700px) {
    #hp .hero-section { padding:52px 0 48px; }
    #hp .section { padding:56px 0; }
    #hp .hero-h1 { font-size:clamp(32px,10vw,44px); }
    #hp .feat-grid, #hp .steps-grid { grid-template-columns:1fr; }
    #hp .metrics-strip { grid-template-columns:1fr 1fr; }
    #hp .hero-btns, #hp .cta-btns { flex-direction:column; }
    #hp .h-btn { width:100%; justify-content:center; }
    #hp .cta-box { padding:32px 24px; }
    #hp .cta-box h2 { font-size:26px; }
}
</style>

<div id="hp">

    <!-- ═══════════════ HERO ═══════════════ -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-inner">
                <div>
                    <div class="hero-badge">PUC Center Management Software</div>
                    <h1 class="hero-h1">
                        Manage PUC Records.<br>
                        <span>Track Expiry. Send Reminders.</span>
                    </h1>
                    <p class="hero-lead">
                        Drive Cue is a professional renewal management platform built exclusively for PUC centers.
                        Add customers, track certificate expiry, prepare WhatsApp reminders — all from one clean dashboard.
                    </p>
                    <div class="hero-btns">
                        <a href="{{ route('register') }}" class="h-btn h-btn-primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            Start Free Trial
                        </a>
                        <a href="{{ url('/pricing') }}" class="h-btn h-btn-outline">View Plans</a>
                    </div>
                    <div class="hero-chips">
                        <span class="hero-chip"><b>✓</b> No technical knowledge needed</span>
                        <span class="hero-chip"><b>✓</b> Works on mobile & desktop</span>
                        <span class="hero-chip"><b>✓</b> Secure login per center</span>
                    </div>
                </div>

                <!-- Live Dashboard Mock -->
                <div class="dash-mock">
                    <div class="dash-mock-top">
                        <div class="mock-dots">
                            <div class="mock-dot"></div>
                            <div class="mock-dot"></div>
                            <div class="mock-dot"></div>
                        </div>
                        <div class="mock-top-title">Drive Cue Dashboard</div>
                        <div class="mock-live">Live</div>
                    </div>
                    <div class="dash-mock-body">
                        <!-- Stats -->
                        <div class="mock-stats">
                            <div class="mock-stat">
                                <b>248</b>
                                <span>Total Records</span>
                            </div>
                            <div class="mock-stat">
                                <b>34</b>
                                <span>Expiring Soon</span>
                            </div>
                            <div class="mock-stat">
                                <b>91%</b>
                                <span>Active PUC</span>
                            </div>
                        </div>
                        <!-- Bar Chart -->
                        <div class="mock-chart">
                            <div class="mock-chart-label">Monthly Activity</div>
                            <div class="mock-bars">
                                <div class="mock-bar" style="height:40%;animation-delay:.1s"></div>
                                <div class="mock-bar" style="height:65%;animation-delay:.2s"></div>
                                <div class="mock-bar" style="height:50%;animation-delay:.3s"></div>
                                <div class="mock-bar" style="height:80%;animation-delay:.4s"></div>
                                <div class="mock-bar" style="height:60%;animation-delay:.5s"></div>
                                <div class="mock-bar" style="height:100%;animation-delay:.6s;background:linear-gradient(180deg,#2457E6,rgba(36,87,230,0.55))"></div>
                            </div>
                        </div>
                        <!-- Table -->
                        <div class="mock-table">
                            <div class="mock-th">
                                <span>Customer</span>
                                <span>Vehicle</span>
                                <span>Status</span>
                            </div>
                            <div class="mock-row">
                                <div class="mock-name">
                                    <div class="mock-av">R</div>
                                    <div><div class="mock-txt">Ramesh Patel</div><div class="mock-veh">GJ03AB1234</div></div>
                                </div>
                                <span class="mock-veh">4-Wheeler</span>
                                <span class="mock-badge warn">Expiring</span>
                            </div>
                            <div class="mock-row">
                                <div class="mock-name">
                                    <div class="mock-av">S</div>
                                    <div><div class="mock-txt">Suresh Shah</div><div class="mock-veh">GJ05CD5678</div></div>
                                </div>
                                <span class="mock-veh">2-Wheeler</span>
                                <span class="mock-badge ok">Active</span>
                            </div>
                            <div class="mock-row">
                                <div class="mock-name">
                                    <div class="mock-av">M</div>
                                    <div><div class="mock-txt">Meena Joshi</div><div class="mock-veh">GJ01EF9012</div></div>
                                </div>
                                <span class="mock-veh">4-Wheeler</span>
                                <span class="mock-badge exp">Expired</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ═══════════════ HOW IT WORKS ═══════════════ -->
    <section class="section section-white">
        <div class="container">
            <div class="sec-head center">
                <div class="sec-eyebrow">Simple 4-Step Process</div>
                <h2 class="sec-h2">Get started in minutes, run your center professionally.</h2>
                <p class="sec-sub">No complicated setup. Just log in, add your customers, and start tracking PUC certificates immediately.</p>
            </div>
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-no">1</div>
                    <h3>Create Account</h3>
                    <p>Register your PUC center with your mobile number. Verify via OTP and get instant access to your dashboard.</p>
                </div>
                <div class="step-card">
                    <div class="step-no">2</div>
                    <h3>Add Customer Records</h3>
                    <p>Enter customer name, mobile number, vehicle number, certificate date and expiry date in a clean digital form.</p>
                </div>
                <div class="step-card">
                    <div class="step-no">3</div>
                    <h3>Track Expiry Alerts</h3>
                    <p>Your dashboard automatically shows expired, expiring in 7 days, and active PUC certificate records at a glance.</p>
                </div>
                <div class="step-card">
                    <div class="step-no">4</div>
                    <h3>Send Reminders</h3>
                    <p>Prepare a professional WhatsApp reminder message for each customer with one click — complete with vehicle details.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════ FEATURES ═══════════════ -->
    <section class="section section-gray">
        <div class="container">
            <div class="sec-head center">
                <div class="sec-eyebrow">Platform Features</div>
                <h2 class="sec-h2">Everything a modern PUC center needs.</h2>
                <p class="sec-sub">Built specifically for Indian PUC centers. Manage all your vehicle records, expiry tracking and customer communication from one place.</p>
            </div>
            <div class="feat-grid">
                <div class="feat-card">
                    <div class="feat-icon">
                        <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <h3>Customer & Vehicle Records</h3>
                    <p>Store customer name, mobile number, vehicle number, fuel type, certificate number and all expiry details in a searchable database.</p>
                </div>
                <div class="feat-card">
                    <div class="feat-icon">
                        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                    <h3>Smart Expiry Tracking</h3>
                    <p>Automatically see which PUC certificates have expired, are expiring within 7 days, or are still active — without any manual work.</p>
                </div>
                <div class="feat-card">
                    <div class="feat-icon">
                        <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    </div>
                    <h3>WhatsApp Reminder Tool</h3>
                    <p>Generate ready-to-send WhatsApp reminder messages for any customer with vehicle number and expiry date pre-filled automatically.</p>
                </div>
                <div class="feat-card">
                    <div class="feat-icon">
                        <svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    </div>
                    <h3>Business Reports</h3>
                    <p>View monthly record trends, total customers, renewal follow-up status and center performance from your professional reports dashboard.</p>
                </div>
                <div class="feat-card">
                    <div class="feat-icon">
                        <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <h3>Secure Center Login</h3>
                    <p>Each center gets a private, secure login. Mobile OTP verification ensures only authorized staff can access customer records.</p>
                </div>
                <div class="feat-card">
                    <div class="feat-icon">
                        <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
                    </div>
                    <h3>Message History Logs</h3>
                    <p>Track all WhatsApp reminders sent — see which customers were contacted, when, and the status of each reminder message.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════ METRICS ═══════════════ -->
    <section class="section section-dark">
        <div class="container">
            <div class="sec-head center">
                <div class="sec-eyebrow">Why PUC Centers Choose Us</div>
                <h2 class="sec-h2">Built for speed. Designed for professionalism.</h2>
                <p class="sec-sub">Drive Cue helps centers move from paper registers and WhatsApp notes to a clean, professional digital workflow — instantly.</p>
            </div>
            <div class="metrics-strip">
                <div class="metric-block">
                    <b>1 Click</b>
                    <span>To prepare customer reminder</span>
                </div>
                <div class="metric-block">
                    <b>7 Days</b>
                    <span>Advance expiry alert window</span>
                </div>
                <div class="metric-block">
                    <b>100%</b>
                    <span>Secure & mobile-verified access</span>
                </div>
                <div class="metric-block">
                    <b>Fast</b>
                    <span>Record entry under 60 seconds</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════ PRICING ═══════════════ -->
    <section class="section section-white">
        <div class="container">
            <div class="sec-head center">
                <div class="sec-eyebrow">Simple Pricing</div>
                <h2 class="sec-h2">Affordable plans for every PUC center.</h2>
                <p class="sec-sub">Start free, upgrade as your center grows. No hidden charges. Cancel anytime.</p>
            </div>
            <div class="price-grid">
                <div class="price-card">
                    <div class="price-label">Starter</div>
                    <div class="price-name">Basic Plan</div>
                    <div class="price-desc">Perfect for small PUC centers just getting started with digital records.</div>
                    <div class="price-price"><b>Free</b> <span>/ forever</span></div>
                    <ul class="price-features">
                        <li>Up to 100 customer records</li>
                        <li>Basic expiry tracking</li>
                        <li>WhatsApp message preparation</li>
                        <li>Secure center login</li>
                    </ul>
                    <a href="{{ route('register') }}" class="h-btn h-btn-outline" style="color:#0B1020!important;border-color:rgba(11,16,32,0.14);background:#FFFFFF;width:100%;justify-content:center;">Get Started Free</a>
                </div>
                <div class="price-card popular">
                    <div class="price-label">Most Popular</div>
                    <div class="price-name">Professional</div>
                    <div class="price-desc">For growing centers that need unlimited records and full reporting features.</div>
                    <div class="price-price"><b>₹499</b> <span>/ month</span></div>
                    <ul class="price-features">
                        <li>Unlimited customer records</li>
                        <li>Smart expiry dashboard</li>
                        <li>WhatsApp logs &amp; history</li>
                        <li>Monthly business reports</li>
                        <li>Priority support</li>
                    </ul>
                    <a href="{{ route('register') }}" class="h-btn h-btn-primary" style="width:100%;justify-content:center;">Start Free Trial</a>
                </div>
                <div class="price-card">
                    <div class="price-label">Enterprise</div>
                    <div class="price-name">Custom Plan</div>
                    <div class="price-desc">For multi-location PUC businesses with advanced requirements and admin control.</div>
                    <div class="price-price"><b>Custom</b></div>
                    <ul class="price-features">
                        <li>Multi-center management</li>
                        <li>Admin control panel</li>
                        <li>Custom record limits</li>
                        <li>Dedicated support</li>
                        <li>Custom integrations</li>
                    </ul>
                    <a href="{{ url('/contact') }}" class="h-btn h-btn-outline" style="color:#0B1020!important;border-color:rgba(11,16,32,0.14);background:#FFFFFF;width:100%;justify-content:center;">Contact Us</a>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════ CTA ═══════════════ -->
    <section class="section section-gray">
        <div class="container">
            <div class="cta-box">
                <div>
                    <h2>Ready to manage your PUC center professionally?</h2>
                    <p>Join PUC centers across India who are using Drive Cue to track expiry, send reminders and grow their renewal business every day.</p>
                </div>
                <div class="cta-btns">
                    <a href="{{ route('register') }}" class="h-btn h-btn-white">Create Free Account</a>
                    <a href="{{ url('/contact') }}" class="h-btn h-btn-ghost">Contact Support</a>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
