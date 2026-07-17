<?php

use App\Models\User;
use App\Services\OtpService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $mobile = '';

    /**
     * Send password reset OTP to mobile.
     */
    public function sendPasswordResetNotification(): void
    {
        $this->validate([
            'mobile' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
        ]);

        $otpService = app(OtpService::class);
        $mobileNormalized = $otpService->normalizeMobile($this->mobile);

        if (!$otpService->isValidMobile($mobileNormalized)) {
            $this->addError('mobile', 'Please enter a valid 10-digit mobile number.');
            return;
        }

        $user = User::where('mobile', $mobileNormalized)
            ->where('mobile_verified', true)
            ->where('status', 'active')
            ->first();

        if (!$user) {
            // Simulate success to prevent user enumeration
            session()->flash('success', 'If a verified account exists for that number, an OTP has been sent. Please check your SMS inbox.');
            $this->redirect(route('verify-otp', ['purpose' => 'forgot_password', 'mobile' => $mobileNormalized]), navigate: true);
            return;
        }

        try {
            $otp = $otpService->createOtp($mobileNormalized, 'forgot_password', request()->ip());
            
            $msg = 'OTP sent to your mobile number.';
            if (config('app.debug') || app()->environment('local')) {
                $msg = "OTP sent. Debug OTP: {$otp}";
            }
            
            session()->flash('success', $msg);
            $this->redirect(route('verify-otp', ['purpose' => 'forgot_password', 'mobile' => $mobileNormalized]), navigate: true);
        } catch (\Throwable $e) {
            $this->addError('mobile', $e->getMessage());
        }
    }
}; ?>
<div>

<style>
/* ─── Reset: override global style.css conflicts for this page ─────── */
.fp-page,
.fp-page * {
    box-sizing: border-box;
}

/* Force the outer <main> to have no padding/background */
.fp-page {
    margin: 0 !important;
    padding: 0 !important;
}

/* ─── Full-viewport split layout ────────────────────────────────────── */
.fp-wrap {
    display: grid;
    grid-template-columns: 1fr 480px;
    min-height: calc(100vh - 74px);
    width: 100%;
}

/* ─── LEFT PANEL ────────────────────────────────────────────────────── */
.fp-left {
    background: linear-gradient(145deg, #0B1020 0%, #162040 55%, #0e1b38 100%);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: flex-start;
    padding: 64px 60px;
    position: relative;
    overflow: hidden;
}

/* Decorative glows */
.fp-left::before {
    content: "";
    position: absolute;
    width: 520px;
    height: 520px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(36, 87, 230, 0.32) 0%, transparent 68%);
    top: -200px;
    right: -200px;
    pointer-events: none;
}

.fp-left::after {
    content: "";
    position: absolute;
    width: 360px;
    height: 360px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(36, 87, 230, 0.16) 0%, transparent 68%);
    bottom: -140px;
    left: -120px;
    pointer-events: none;
}

/* Dot pattern on dark panel */
.fp-left-dots {
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(255,255,255,0.030) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.030) 1px, transparent 1px);
    background-size: 38px 38px;
    pointer-events: none;
}

.fp-left-inner {
    position: relative;
    z-index: 1;
    max-width: 460px;
}

.fp-left-tag {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 14px;
    border-radius: 999px;
    background: rgba(36, 87, 230, 0.22);
    border: 1px solid rgba(79, 131, 255, 0.40);
    color: #93B4FF;
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    margin-bottom: 30px;
}

.fp-left-tag::before {
    content: "";
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #4F83FF;
    box-shadow: 0 0 0 4px rgba(79, 131, 255, 0.22);
    animation: fpPulse 2.4s ease infinite;
}

@keyframes fpPulse {
    0%, 100% { box-shadow: 0 0 0 4px rgba(79,131,255,0.22); }
    50%       { box-shadow: 0 0 0 8px rgba(79,131,255,0.06); }
}

.fp-left h1 {
    margin: 0 0 18px;
    color: #ffffff;
    font-size: clamp(30px, 3.2vw, 46px);
    font-weight: 700;
    line-height: 1.06;
    letter-spacing: -0.055em;
}

.fp-left h1 em {
    font-style: normal;
    color: #4F83FF;
}

.fp-left-desc {
    color: rgba(255, 255, 255, 0.58);
    font-size: 15px;
    line-height: 1.74;
    font-weight: 500;
    margin: 0 0 36px;
}

/* Step list */
.fp-steps {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.fp-step {
    display: flex;
    align-items: center;
    gap: 14px;
}

.fp-step-num {
    width: 36px;
    height: 36px;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 14px;
    font-weight: 700;
    background: rgba(36, 87, 230, 0.28);
    border: 1px solid rgba(79, 131, 255, 0.38);
    color: #93B4FF;
    transition: background 0.2s;
}

.fp-step-text {
    color: rgba(255, 255, 255, 0.68);
    font-size: 14.5px;
    font-weight: 600;
    line-height: 1.4;
}

/* ─── RIGHT PANEL ────────────────────────────────────────────────────── */
.fp-right {
    background: #ffffff;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 52px 48px;
    border-left: 1px solid rgba(11, 16, 32, 0.09);
    position: relative;
}

/* Subtle top-right decoration */
.fp-right::before {
    content: "";
    position: absolute;
    top: 0;
    right: 0;
    width: 200px;
    height: 200px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(36, 87, 230, 0.06) 0%, transparent 70%);
    pointer-events: none;
}

/* ─── Eyebrow pill ───────────────────────────────────────────────────── */
.fp-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 6px 12px;
    border-radius: 999px;
    background: rgba(36, 87, 230, 0.08);
    border: 1px solid rgba(36, 87, 230, 0.20);
    color: #2457E6;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    margin-bottom: 14px;
    width: fit-content;
}

.fp-eyebrow::before {
    content: "";
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
}

/* ─── Heading ───────────────────────────────────────────────────────── */
.fp-h2 {
    margin: 0 0 8px;
    color: #0B1020;
    font-size: 27px;
    font-weight: 700;
    letter-spacing: -0.045em;
    line-height: 1.1;
}

.fp-subtitle {
    margin: 0 0 26px;
    color: rgba(11, 16, 32, 0.55);
    font-size: 14.5px;
    line-height: 1.65;
    font-weight: 550;
}

/* ─── Horizontal rule ───────────────────────────────────────────────── */
.fp-hr {
    height: 1px;
    background: rgba(11, 16, 32, 0.08);
    margin: 0 0 24px;
}

/* ─── Error alert ───────────────────────────────────────────────────── */
.fp-alert {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 13px 14px;
    border-radius: 12px;
    font-size: 13.5px;
    font-weight: 700;
    line-height: 1.5;
    margin-bottom: 18px;
    background: rgba(220, 38, 38, 0.06);
    border: 1px solid rgba(220, 38, 38, 0.22);
    color: #991B1B;
}

.fp-alert svg {
    flex-shrink: 0;
    width: 17px;
    height: 17px;
    stroke: currentColor;
    stroke-width: 2.2;
    fill: none;
    stroke-linecap: round;
    stroke-linejoin: round;
    margin-top: 1px;
}

/* ─── Form label ─────────────────────────────────────────────────────── */
.fp-label {
    display: block;
    margin-bottom: 8px;
    color: #0B1020;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.01em;
}

/* ─── Input wrapper with icon ──────────────────────────────────────── */
.fp-field-wrap {
    position: relative;
    width: 100%;
}

.fp-field-icon {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    color: rgba(11, 16, 32, 0.38);
    transition: color 0.18s;
}

.fp-field-icon svg {
    width: 18px !important;
    height: 18px !important;
    stroke: currentColor !important;
    stroke-width: 2.1 !important;
    fill: none !important;
    stroke-linecap: round !important;
    stroke-linejoin: round !important;
    display: block;
}

.fp-field {
    display: block !important;
    width: 100% !important;
    height: 54px !important;
    padding: 0 16px 0 48px !important;
    border-radius: 13px !important;
    border: 1.5px solid rgba(11, 16, 32, 0.12) !important;
    background: #F8FAFF !important;
    color: #0B1020 !important;
    font-size: 15px !important;
    font-weight: 700 !important;
    font-family: inherit !important;
    outline: none !important;
    box-shadow: none !important;
    transition: border-color 0.18s, box-shadow 0.18s, background 0.18s !important;
    -webkit-appearance: none !important;
    appearance: none !important;
}

.fp-field::placeholder {
    color: rgba(11, 16, 32, 0.32) !important;
    font-weight: 500 !important;
    font-size: 14px !important;
}

.fp-field:focus {
    border-color: rgba(36, 87, 230, 0.60) !important;
    box-shadow: 0 0 0 4px rgba(36, 87, 230, 0.10) !important;
    background: #ffffff !important;
    outline: none !important;
}

.fp-field-wrap:focus-within .fp-field-icon {
    color: #2457E6;
}

/* ─── Submit button ─────────────────────────────────────────────────── */
.fp-btn {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 9px !important;
    width: 100% !important;
    height: 54px !important;
    margin-top: 6px !important;
    padding: 0 20px !important;
    border: none !important;
    border-radius: 13px !important;
    background: #2457E6 !important;
    color: #ffffff !important;
    font-size: 15px !important;
    font-weight: 700 !important;
    font-family: inherit !important;
    cursor: pointer !important;
    transition: background 0.18s, transform 0.18s, box-shadow 0.18s !important;
    box-shadow: 0 12px 32px rgba(36, 87, 230, 0.30) !important;
    letter-spacing: -0.01em !important;
}

.fp-btn svg {
    width: 17px;
    height: 17px;
    stroke: currentColor;
    stroke-width: 2.4;
    fill: none;
    stroke-linecap: round;
    stroke-linejoin: round;
    flex-shrink: 0;
    transition: transform 0.18s;
}

.fp-btn:hover:not(:disabled) {
    background: #1a47cc !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 18px 40px rgba(36, 87, 230, 0.38) !important;
}

.fp-btn:hover svg { transform: translateX(3px); }

.fp-btn:disabled {
    opacity: 0.65 !important;
    cursor: not-allowed !important;
    transform: none !important;
}

/* Spinner */
.fp-spin {
    display: none;
    width: 18px;
    height: 18px;
    border: 2.5px solid rgba(255, 255, 255, 0.30);
    border-top-color: #ffffff;
    border-radius: 50%;
    animation: fpSpin 0.65s linear infinite;
    flex-shrink: 0;
}

@keyframes fpSpin { to { transform: rotate(360deg); } }

.fp-btn.is-loading .fp-btn-label { display: none !important; }
.fp-btn.is-loading .fp-spin      { display: block !important; }

/* ─── Info note ──────────────────────────────────────────────────────── */
.fp-note {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    margin-top: 16px;
    padding: 12px 14px;
    border-radius: 12px;
    background: rgba(36, 87, 230, 0.06);
    border: 1px solid rgba(36, 87, 230, 0.16);
    color: rgba(11, 16, 32, 0.55);
    font-size: 12.5px;
    line-height: 1.55;
    font-weight: 500;
}

.fp-note svg {
    flex-shrink: 0;
    width: 15px;
    height: 15px;
    stroke: #2457E6;
    stroke-width: 2.2;
    fill: none;
    stroke-linecap: round;
    stroke-linejoin: round;
    margin-top: 1px;
}

/* ─── Back link ──────────────────────────────────────────────────────── */
.fp-back {
    margin-top: 22px;
    text-align: center;
    font-size: 13.5px;
    font-weight: 600;
    color: rgba(11, 16, 32, 0.52);
}

.fp-back a {
    color: #2457E6 !important;
    text-decoration: none !important;
    font-weight: 700;
}

.fp-back a:hover { text-decoration: underline !important; }

/* ─── Responsive ─────────────────────────────────────────────────────── */
@media (max-width: 900px) {
    .fp-wrap {
        grid-template-columns: 1fr;
    }

    .fp-left {
        display: none;
    }

    .fp-right {
        padding: 48px 32px;
        border-left: none;
        min-height: calc(100vh - 74px);
    }
}

@media (max-width: 480px) {
    .fp-right {
        padding: 36px 20px;
    }

    .fp-h2 {
        font-size: 23px;
    }
}
</style>

<div class="fp-page">
    <div class="fp-wrap">

        <!-- LEFT PANEL -->
        <div class="fp-left">
            <div class="fp-left-dots"></div>
            <div class="fp-left-inner">
                <span class="fp-left-tag">Password Reset</span>
                <h1>Forgot your<br><em>password?</em></h1>

                <p class="fp-left-desc">
                    No worries! Enter your registered mobile number and we'll send you a one-time OTP to securely reset your password.
                </p>

                <div class="fp-steps">
                    <div class="fp-step">
                        <span class="fp-step-num">1</span>
                        <span class="fp-step-text">Enter your registered mobile number</span>
                    </div>
                    <div class="fp-step">
                        <span class="fp-step-num">2</span>
                        <span class="fp-step-text">Enter the OTP sent to your phone</span>
                    </div>
                    <div class="fp-step">
                        <span class="fp-step-num">3</span>
                        <span class="fp-step-text">Set your new secure password</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT PANEL -->
        <div class="fp-right">
            <span class="fp-eyebrow">Reset Password</span>
            <h2 class="fp-h2">Forgot your password?</h2>
            <p class="fp-subtitle">
                Enter your registered mobile number below. We'll send you an OTP to reset your password.
            </p>

            <div class="fp-hr"></div>

            @if ($errors->has('mobile'))
                <div class="fp-alert" role="alert">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                    <span>{{ $errors->first('mobile') }}</span>
                </div>
            @endif

            <form wire:submit="sendPasswordResetNotification" id="fpForm" novalidate autocomplete="off">
                <div style="margin-bottom:20px;">
                    <label class="fp-label" for="fp_mobile">Mobile Number</label>
                    <div class="fp-field-wrap">
                        <span class="fp-field-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.8 19.8 0 0 1 3 5.18 2 2 0 0 1 5 3h3a2 2 0 0 1 2 1.72c.12.9.33 1.77.62 2.6a2 2 0 0 1-.45 2.11L8.91 10.7a16 16 0 0 0 4.39 4.39l1.27-1.26a2 2 0 0 1 2.11-.45c.83.29 1.7.5 2.6.62A2 2 0 0 1 22 16.92z"/>
                            </svg>
                        </span>
                        <input
                            wire:model="mobile"
                            id="fp_mobile"
                            class="fp-field"
                            type="tel"
                            name="mobile"
                            required
                            maxlength="10"
                            inputmode="numeric"
                            pattern="[6-9][0-9]{9}"
                            placeholder="Enter 10-digit mobile number"
                        >
                    </div>
                </div>

                <button class="fp-btn" type="submit" id="fpBtn" wire:loading.attr="disabled">
                    <span class="fp-btn-label" style="display:inline-flex;align-items:center;gap:8px;" wire:loading.remove wire:target="sendPasswordResetNotification">
                        Send OTP
                        <svg viewBox="0 0 24 24"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                    </span>
                    <span class="fp-spin" wire:loading wire:target="sendPasswordResetNotification" style="display:block;"></span>
                </button>

                <div class="fp-note">
                    <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    OTP valid for 5 minutes. Max 3 requests per 10 minutes.
                </div>
            </form>

            <p class="fp-back">
                Remember your password? <a href="{{ route('login') }}" wire:navigate>Sign in here</a>
            </p>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('fp_mobile');
    if (!input) return;

    input.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 10);
    });
});
</script>
</div>
