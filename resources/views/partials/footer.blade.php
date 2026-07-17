@php
    $logoUrl = asset('logo/logo1.png');
    $hasLogo = file_exists(public_path('logo/logo1.png')) || file_exists(public_path('logo/logo.png'));
    if (!file_exists(public_path('logo/logo1.png')) && file_exists(public_path('logo/logo.png'))) {
        $logoUrl = asset('logo/logo.png');
    }
@endphp

<style>
/* ===== PUC Setu Footer - White / Black / Blue Theme ===== */
#pucFooter,
#pucFooter * {
    box-sizing: border-box;
}

#pucFooter {
    --ft-black: #0B1020;
    --ft-blue: #2457E6;
    --ft-white: #FFFFFF;
    --ft-soft: #F6F8FF;
    --ft-line: rgba(11,16,32,0.10);
    --ft-muted: rgba(11,16,32,0.62);

    position: relative;
    overflow: hidden;
    color: var(--ft-black);
    background:
        radial-gradient(circle at 10% 0%, rgba(36,87,230,0.10), transparent 30%),
        radial-gradient(circle at 92% 12%, rgba(36,87,230,0.08), transparent 34%),
        linear-gradient(180deg, #FFFFFF 0%, #F7F9FF 100%);
    border-top: 1px solid rgba(11,16,32,0.08);
    font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
}

#pucFooter::before {
    content: "";
    position: absolute;
    inset: 0 0 auto 0;
    height: 4px;
    background: linear-gradient(90deg, transparent 0%, var(--ft-blue) 22%, #0B1020 50%, var(--ft-blue) 78%, transparent 100%);
    opacity: 0.95;
}

#pucFooter::after {
    content: "";
    position: absolute;
    inset: 0;
    pointer-events: none;
    background-image:
        linear-gradient(rgba(11,16,32,0.035) 1px, transparent 1px),
        linear-gradient(90deg, rgba(11,16,32,0.035) 1px, transparent 1px);
    background-size: 42px 42px;
    mask-image: linear-gradient(180deg, rgba(0,0,0,0.55), transparent 70%);
}

#pucFooter .footer-orb-1,
#pucFooter .footer-orb-2 {
    position: absolute;
    border-radius: 999px;
    pointer-events: none;
    z-index: 0;
}

#pucFooter .footer-orb-1 {
    width: 420px;
    height: 420px;
    left: -190px;
    bottom: -240px;
    background: radial-gradient(circle, rgba(36,87,230,0.13), transparent 68%);
}

#pucFooter .footer-orb-2 {
    width: 360px;
    height: 360px;
    right: -160px;
    top: -160px;
    background: radial-gradient(circle, rgba(11,16,32,0.10), transparent 70%);
}

/* Main footer area */
#pucFooter .footer-main {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: minmax(0, 1.55fr) minmax(150px, 0.55fr) minmax(150px, 0.55fr) minmax(230px, 0.75fr);
    gap: 34px 28px;
    padding: 52px 0 38px;
    align-items: flex-start;
}

/* Brand column */
#pucFooter .footer-brand-link {
    display: inline-flex;
    align-items: center;
    gap: 14px;
    text-decoration: none !important;
    margin-bottom: 17px;
    color: inherit;
}

#pucFooter .footer-logo-box {
    width: 58px;
    height: 54px;
    border-radius: 18px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--ft-white);
    border: 1px solid rgba(11,16,32,0.10);
    box-shadow: 0 14px 34px rgba(11,16,32,0.10), inset 0 1px 0 rgba(255,255,255,0.90);
    overflow: hidden;
    flex-shrink: 0;
}

#pucFooter .footer-logo-img {
    display: block;
    height: 38px;
    width: auto;
    max-width: 48px;
    object-fit: contain;
}

#pucFooter .footer-logo-fallback {
    width: 54px;
    height: 54px;
    border-radius: 18px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 23px;
    font-weight: 700;
    color: var(--ft-white);
    flex-shrink: 0;
    background: linear-gradient(135deg, var(--ft-black) 0%, var(--ft-blue) 100%);
    box-shadow: 0 16px 34px rgba(36,87,230,0.25);
}

#pucFooter .footer-brand-text-wrap {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

#pucFooter .footer-brand-name {
    color: var(--ft-black);
    font-size: 22px;
    font-weight: 700;
    letter-spacing: -0.045em;
    line-height: 1;
}

#pucFooter .footer-brand-tag {
    color: rgba(11,16,32,0.50);
    font-size: 9.5px;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    line-height: 1;
}

#pucFooter .footer-desc {
    color: var(--ft-muted);
    font-size: 13.8px;
    line-height: 1.74;
    max-width: 460px;
    margin: 0 0 20px;
    font-weight: 560;
}

#pucFooter .footer-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 9px;
}

#pucFooter .footer-chip {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    min-height: 32px;
    padding: 7px 12px;
    border-radius: 999px;
    color: rgba(11,16,32,0.72);
    background: var(--ft-white);
    border: 1px solid rgba(11,16,32,0.09);
    box-shadow: 0 10px 24px rgba(11,16,32,0.055);
    font-size: 11.5px;
    font-weight: 700;
    line-height: 1;
    transition: 0.22s ease;
}

#pucFooter .footer-chip:hover {
    color: var(--ft-blue);
    border-color: rgba(36,87,230,0.22);
    box-shadow: 0 14px 30px rgba(36,87,230,0.10);
    transform: translateY(-2px);
}

#pucFooter .footer-chip-dot {
    width: 7px;
    height: 7px;
    border-radius: 999px;
    background: var(--ft-blue);
    box-shadow: 0 0 0 4px rgba(36,87,230,0.13);
    flex-shrink: 0;
}

/* Link columns */
#pucFooter .footer-col h4 {
    margin: 0 0 15px;
    color: var(--ft-black);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.105em;
    text-transform: uppercase;
    line-height: 1;
}

#pucFooter .footer-col-links {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

#pucFooter .footer-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: rgba(11,16,32,0.62);
    text-decoration: none !important;
    font-size: 13.6px;
    font-weight: 780;
    line-height: 1.35;
    transition: 0.22s ease;
}

#pucFooter .footer-link::before {
    content: "";
    width: 5px;
    height: 5px;
    border-radius: 999px;
    background: rgba(36,87,230,0.55);
    flex-shrink: 0;
    transition: 0.22s ease;
}

#pucFooter .footer-link:hover {
    color: var(--ft-blue);
    transform: translateX(4px);
}

#pucFooter .footer-link:hover::before {
    background: var(--ft-blue);
    box-shadow: 0 0 0 4px rgba(36,87,230,0.12);
}

/* Status card */
#pucFooter .footer-status-card {
    padding: 18px;
    border-radius: 22px;
    background:
        radial-gradient(circle at 85% 0%, rgba(36,87,230,0.12), transparent 45%),
        var(--ft-white);
    border: 1px solid rgba(11,16,32,0.10);
    box-shadow: 0 18px 42px rgba(11,16,32,0.08);
}

#pucFooter .footer-status-row {
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--ft-black);
    font-size: 13px;
    font-weight: 930;
    margin-bottom: 12px;
}

#pucFooter .footer-status-indicator {
    width: 9px;
    height: 9px;
    border-radius: 999px;
    background: var(--ft-blue);
    box-shadow: 0 0 0 5px rgba(36,87,230,0.14);
    flex-shrink: 0;
    animation: pucFooterPulse 2.4s ease infinite;
}

@keyframes pucFooterPulse {
    0%, 100% { box-shadow: 0 0 0 5px rgba(36,87,230,0.14); }
    50% { box-shadow: 0 0 0 8px rgba(36,87,230,0.05); }
}

#pucFooter .footer-status-meta {
    color: rgba(11,16,32,0.58);
    font-size: 12px;
    font-weight: 720;
    line-height: 1.62;
    margin: 0;
}

/* Bottom bar */
#pucFooter .footer-bottom {
    position: relative;
    z-index: 1;
    background: var(--ft-black);
    border-top: 1px solid rgba(255,255,255,0.10);
}

#pucFooter .footer-bottom-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    min-height: 58px;
    flex-wrap: wrap;
    color: rgba(255,255,255,0.62);
    font-size: 12px;
    font-weight: 760;
}

#pucFooter .footer-bottom-links {
    display: inline-flex;
    align-items: center;
    gap: 18px;
    flex-wrap: wrap;
}

#pucFooter .footer-bottom-links a {
    color: rgba(255,255,255,0.62);
    text-decoration: none !important;
    transition: color 0.18s ease;
}

#pucFooter .footer-bottom-links a:hover {
    color: var(--ft-white);
}

#pucFooter .footer-made-with {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: rgba(255,255,255,0.58);
    font-size: 12px;
}

#pucFooter .footer-heart {
    color: var(--ft-blue);
    font-size: 13px;
}

/* Responsive */
@media (max-width: 1080px) {
    #pucFooter .footer-main {
        grid-template-columns: minmax(0, 1.5fr) minmax(140px, 0.5fr) minmax(140px, 0.5fr);
        gap: 30px 24px;
    }

    #pucFooter .footer-status-card-col {
        grid-column: 1 / -1;
    }

    #pucFooter .footer-status-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
    }

    #pucFooter .footer-status-card .footer-status-row {
        margin-bottom: 0;
        min-width: max-content;
    }
}

@media (max-width: 820px) {
    #pucFooter .footer-main {
        grid-template-columns: 1fr 1fr;
        gap: 28px 22px;
        padding: 42px 0 30px;
    }

    #pucFooter .footer-brand-col {
        grid-column: 1 / -1;
    }

    #pucFooter .footer-bottom-inner {
        min-height: auto;
        flex-direction: column;
        align-items: flex-start;
        padding: 16px 0;
    }
}

@media (max-width: 520px) {
    #pucFooter .footer-main {
        grid-template-columns: 1fr;
        gap: 24px;
        padding: 34px 0 26px;
    }

    #pucFooter .footer-brand-link {
        align-items: flex-start;
    }

    #pucFooter .footer-bottom-links {
        gap: 12px;
        font-size: 11px;
    }

    #pucFooter .footer-status-card {
        flex-direction: column;
        align-items: flex-start;
    }

    #pucFooter .footer-status-card .footer-status-row {
        margin-bottom: 0;
        min-width: 0;
    }
}
</style>

<footer id="pucFooter">
    <div class="footer-orb-1"></div>
    <div class="footer-orb-2"></div>

    <div class="container">
        <div class="footer-main">
            <!-- Brand & About -->
            <div class="footer-brand-col">
                <a class="footer-brand-link" href="{{ url('/') }}" aria-label="Drive Cue Home">
                    @if ($hasLogo)
                        <span class="footer-logo-box">
                            <img src="{{ $logoUrl }}" alt="Drive Cue" class="footer-logo-img" loading="lazy" decoding="async">
                        </span>
                    @else
                        <span class="footer-logo-fallback">P</span>
                    @endif

                    <span class="footer-brand-text-wrap">
                        <span class="footer-brand-name">Drive Cue</span>
                        <span class="footer-brand-tag">Renewal Management Platform</span>
                    </span>
                </a>

                <p class="footer-desc">
                    A powerful SaaS platform for PUC centers — manage customer records, track certificate expiry dates,
                    send WhatsApp reminders automatically, and monitor your center's performance in real time.
                </p>

                <div class="footer-chips">
                    <span class="footer-chip"><span class="footer-chip-dot"></span>Secure Access</span>
                    <span class="footer-chip"><span class="footer-chip-dot"></span>Smart Records</span>
                    <span class="footer-chip"><span class="footer-chip-dot"></span>WhatsApp Alerts</span>
                    <span class="footer-chip"><span class="footer-chip-dot"></span>Pro Workflow</span>
                </div>
            </div>

            <!-- Product links -->
            <div class="footer-col">
                <h4>Product</h4>
                <div class="footer-col-links">
                    <a href="{{ url('/pricing') }}" class="footer-link">Pricing Plans</a>
                    <a href="{{ route('register') }}" class="footer-link">Create Account</a>
                    <a href="{{ route('login') }}" class="footer-link">Center Login</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="footer-link">Dashboard</a>
                    @endauth
                </div>
            </div>

            <!-- Support links -->
            <div class="footer-col">
                <h4>Support</h4>
                <div class="footer-col-links">
                    <a href="{{ url('/contact') }}" class="footer-link">Contact Us</a>
                    <a href="{{ url('/privacy') }}" class="footer-link">Privacy Policy</a>
                    <a href="{{ url('/terms') }}" class="footer-link">Terms of Use</a>
                </div>
            </div>

            <!-- Status Card -->
            <div class="footer-col footer-status-card-col">
                <h4>Platform Status</h4>
                <div class="footer-status-card">
                    <div class="footer-status-row">
                        <span class="footer-status-indicator"></span>
                        All Systems Operational
                    </div>
                    <p class="footer-status-meta">
                        PUC center data is securely hosted and monitored 24/7.
                        WhatsApp reminders and records are processed in real-time.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Bottom Bar -->
    <div class="footer-bottom">
        <div class="container footer-bottom-inner">
            <span>© {{ date('Y') }} Drive Cue. All rights reserved.</span>

            <div class="footer-bottom-links">
                <a href="{{ url('/privacy') }}">Privacy</a>
                <a href="{{ url('/terms') }}">Terms</a>
                <a href="{{ url('/contact') }}">Contact</a>
            </div>

            <span class="footer-made-with">
                Built for professional PUC operations <span class="footer-heart">♥</span>
            </span>
        </div>
    </div>
</footer>

<script>window.PUC_BASE = "{{ url('/') }}/";</script>
<script src="{{ asset('assets/js/payment.js') }}"></script>
<script src="{{ asset('assets/js/app.js') }}"></script>
