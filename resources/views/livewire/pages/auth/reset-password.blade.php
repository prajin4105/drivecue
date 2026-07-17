<?php

use App\Models\User;
use App\Services\OtpService;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    #[Url]
    public string $mobile = '';

    public string $password = '';
    public string $password_confirmation = '';

    public function mount(): void
    {
        $mobileNormalized = app(OtpService::class)->normalizeMobile($this->mobile);
        $sessionMobile = session('password_reset_mobile');
        $sessionVerified = session('password_reset_verified');

        if (empty($this->mobile) || $mobileNormalized !== $sessionMobile || !$sessionVerified) {
            session()->flash('error', 'Unauthorized access. Please verify your mobile number first.');
            $this->redirect(route('password.request'), navigate: true);
        }
    }

    /**
     * Reset the user's password.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $otpService = app(OtpService::class);
        $mobileNormalized = $otpService->normalizeMobile($this->mobile);

        $user = User::where('mobile', $mobileNormalized)->first();

        if (!$user) {
            $this->addError('password', 'User account not found. Please contact support.');
            return;
        }

        // Update password and clear temp session variables
        $user->update([
            'password' => Hash::make($this->password),
        ]);

        session()->forget(['password_reset_mobile', 'password_reset_verified']);

        session()->flash('success', '🎉 Password updated successfully! Please login with your new password.');
        $this->redirect(route('login'), navigate: true);
    }
}; ?>
<div>

<style>
/* ─── Reset: override global style.css conflicts for this page ─────── */
.rp-page,
.rp-page * {
    box-sizing: border-box;
}

/* Force the outer <main> to have no padding/background */
.rp-page {
    margin: 0 !important;
    padding: 0 !important;
}

/* ─── Full-viewport split layout ────────────────────────────────────── */
.rp-wrap {
    display: grid;
    grid-template-columns: 1fr 480px;
    min-height: calc(100vh - 74px);
    width: 100%;
}

/* ─── LEFT PANEL ────────────────────────────────────────────────────── */
.rp-left {
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
.rp-left::before {
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

.rp-left::after {
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
.rp-left-dots {
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(255,255,255,0.030) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.030) 1px, transparent 1px);
    background-size: 38px 38px;
    pointer-events: none;
}

.rp-left-inner {
    position: relative;
    z-index: 1;
    max-width: 460px;
}

.rp-left-tag {
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

.rp-left-tag::before {
    content: "";
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #4F83FF;
    box-shadow: 0 0 0 4px rgba(79, 131, 255, 0.22);
    animation: rpPulse 2.4s ease infinite;
}

@keyframes rpPulse {
    0%, 100% { box-shadow: 0 0 0 4px rgba(79,131,255,0.22); }
    50%       { box-shadow: 0 0 0 8px rgba(79,131,255,0.06); }
}

.rp-left h1 {
    margin: 0 0 18px;
    color: #ffffff;
    font-size: clamp(30px, 3.2vw, 46px);
    font-weight: 700;
    line-height: 1.06;
    letter-spacing: -0.055em;
}

.rp-left h1 em {
    font-style: normal;
    color: #4F83FF;
}

.rp-left-desc {
    color: rgba(255, 255, 255, 0.58);
    font-size: 15px;
    line-height: 1.74;
    font-weight: 500;
    margin: 0 0 36px;
}

/* Step list */
.rp-steps {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.rp-step {
    display: flex;
    align-items: center;
    gap: 14px;
}

.rp-step-num {
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

.rp-step-num.done {
    background: rgba(5, 150, 105, 0.20);
    border-color: rgba(5, 150, 105, 0.40);
    color: #34D399;
}

.rp-step-text {
    color: rgba(255, 255, 255, 0.68);
    font-size: 14.5px;
    font-weight: 600;
    line-height: 1.4;
}

/* ─── RIGHT PANEL ────────────────────────────────────────────────────── */
.rp-right {
    background: #ffffff;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 52px 48px;
    border-left: 1px solid rgba(11, 16, 32, 0.09);
    position: relative;
}

/* Subtle top-right decoration */
.rp-right::before {
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
.rp-eyebrow {
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

.rp-eyebrow::before {
    content: "";
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
}

/* ─── Heading ───────────────────────────────────────────────────────── */
.rp-h2 {
    margin: 0 0 8px;
    color: #0B1020;
    font-size: 27px;
    font-weight: 700;
    letter-spacing: -0.045em;
    line-height: 1.1;
}

.rp-subtitle {
    margin: 0 0 26px;
    color: rgba(11, 16, 32, 0.55);
    font-size: 14.5px;
    line-height: 1.65;
    font-weight: 550;
}

.rp-mobile-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: 999px;
    background: rgba(36, 87, 230, 0.08);
    border: 1px solid rgba(36, 87, 230, 0.20);
    color: #2457E6;
    font-size: 13px;
    font-weight: 700;
}

/* ─── Horizontal rule ───────────────────────────────────────────────── */
.rp-hr {
    height: 1px;
    background: rgba(11, 16, 32, 0.08);
    margin: 0 0 24px;
}

/* ─── Error alert ───────────────────────────────────────────────────── */
.rp-alert {
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

.rp-alert svg {
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
.rp-label {
    display: block;
    margin-bottom: 8px;
    color: #0B1020;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.01em;
}

/* ─── Input wrapper with icon ──────────────────────────────────────── */
.rp-field-wrap {
    position: relative;
    width: 100%;
}

.rp-field-icon {
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

.rp-field-icon svg {
    width: 18px !important;
    height: 18px !important;
    stroke: currentColor !important;
    stroke-width: 2.1 !important;
    fill: none !important;
    stroke-linecap: round !important;
    stroke-linejoin: round !important;
    display: block;
}

.rp-field {
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

.rp-field::placeholder {
    color: rgba(11, 16, 32, 0.32) !important;
    font-weight: 500 !important;
    font-size: 14px !important;
}

.rp-field:focus {
    border-color: rgba(36, 87, 230, 0.60) !important;
    box-shadow: 0 0 0 4px rgba(36, 87, 230, 0.10) !important;
    background: #ffffff !important;
    outline: none !important;
}

.rp-field-wrap:focus-within .rp-field-icon {
    color: #2457E6;
}

.rp-password-input input {
    padding-right: 55px !important;
}

.rp-password-toggle {
    position: absolute;
    top: 50%;
    right: 8px;
    transform: translateY(-50%);
    width: 39px;
    height: 39px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 0;
    border-radius: 13px;
    color: #2457E6;
    background: rgba(36, 87, 230, 0.08);
    cursor: pointer;
    transition: 0.2s ease;
    z-index: 10;
}

.rp-password-toggle:hover {
    color: #ffffff;
    background: #2457E6;
}

.rp-password-toggle svg {
    width: 19px;
    height: 19px;
    display: block;
    stroke: currentColor;
    stroke-width: 2.2;
    fill: none;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.rp-password-toggle .eye-off-icon { display: none !important; }
.rp-password-toggle.is-visible .eye-icon { display: none !important; }
.rp-password-toggle.is-visible .eye-off-icon { display: block !important; }

/* ─── Submit button ─────────────────────────────────────────────────── */
.rp-btn {
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

.rp-btn svg {
    width: 17px;
    height: 17px;
    stroke: currentColor;
    stroke-width: 2.4;
    fill: none;
    stroke-linecap: round;
    stroke-linejoin: round;
    flex-shrink: 0;
}

.rp-btn:hover:not(:disabled) {
    background: #1a47cc !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 18px 40px rgba(36, 87, 230, 0.38) !important;
}

.rp-btn:disabled {
    opacity: 0.65 !important;
    cursor: not-allowed !important;
    transform: none !important;
}

/* Spinner */
.rp-spin {
    display: none;
    width: 18px;
    height: 18px;
    border: 2.5px solid rgba(255, 255, 255, 0.30);
    border-top-color: #ffffff;
    border-radius: 50%;
    animation: rpSpin 0.65s linear infinite;
    flex-shrink: 0;
}

@keyframes rpSpin { to { transform: rotate(360deg); } }

.rp-btn.is-loading .rp-btn-label { display: none !important; }
.rp-btn.is-loading .rp-spin      { display: block !important; }

/* ─── Info note ──────────────────────────────────────────────────────── */
.rp-note {
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

.rp-note svg {
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

/* ─── Responsive ─────────────────────────────────────────────────────── */
@media (max-width: 900px) {
    .rp-wrap {
        grid-template-columns: 1fr;
    }

    .rp-left {
        display: none;
    }

    .rp-right {
        padding: 48px 32px;
        border-left: none;
        min-height: calc(100vh - 74px);
    }
}

@media (max-width: 480px) {
    .rp-right {
        padding: 36px 20px;
    }

    .rp-h2 {
        font-size: 23px;
    }
}
</style>

<div class="rp-page" x-data="{ showPassword: false, showConfirmPassword: false }">
    <div class="rp-wrap">

        <!-- LEFT PANEL -->
        <div class="rp-left">
            <div class="rp-left-dots"></div>
            <div class="rp-left-inner">
                <span class="rp-left-tag">Password Reset</span>
                <h1>Reset your<br><em>password.</em></h1>

                <p class="rp-left-desc">
                    Setup a new secure password for your account to protect your center records and workspace data.
                </p>

                <div class="rp-steps">
                    <div class="rp-step">
                        <span class="rp-step-num done">✓</span>
                        <span class="rp-step-text">Mobile number entered</span>
                    </div>
                    <div class="rp-step">
                        <span class="rp-step-num done">✓</span>
                        <span class="rp-step-text">OTP verified successfully</span>
                    </div>
                    <div class="rp-step">
                        <span class="rp-step-num active">3</span>
                        <span class="rp-step-text">Set your new password</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT PANEL -->
        <div class="rp-right">
            <span class="rp-eyebrow">Set Password</span>
            <h2 class="rp-h2">Create new password</h2>
            <p class="rp-subtitle">
                Set a new password for account associated with mobile
                <span class="rp-mobile-chip">{{ $mobile }}</span>
            </p>

            <div class="rp-hr"></div>

            @if ($errors->any())
                <div class="rp-alert" role="alert">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                    <div>
                        <ul style="margin:0;padding-left:15px;list-style-type:disc;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form wire:submit="resetPassword" id="rpForm" novalidate autocomplete="off">
                <div style="margin-bottom:20px;">
                    <label class="rp-label" for="rp_password">New Password</label>
                    <div class="rp-field-wrap rp-password-input">
                        <span class="rp-field-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </span>
                        <input
                            wire:model="password"
                            id="rp_password"
                            class="rp-field"
                            :type="showPassword ? 'text' : 'password'"
                            name="password"
                            required
                            minlength="6"
                            placeholder="Enter new password (min 6 chars)"
                        >
                        <button class="rp-password-toggle" type="button" @click="showPassword = !showPassword" aria-label="Show password" :class="{ 'is-visible': showPassword }">
                            <svg class="eye-icon" viewBox="0 0 24 24" width="19" height="19" fill="none"
                                 stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg class="eye-off-icon" viewBox="0 0 24 24" width="19" height="19" fill="none"
                                 stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                                <path d="M10.73 10.73A3 3 0 0 0 12 15a3 3 0 0 0 2.09-.81"/>
                                <line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div style="margin-bottom:24px;">
                    <label class="rp-label" for="rp_confirm">Confirm Password</label>
                    <div class="rp-field-wrap rp-password-input">
                        <span class="rp-field-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </span>
                        <input
                            wire:model="password_confirmation"
                            id="rp_confirm"
                            class="rp-field"
                            :type="showConfirmPassword ? 'text' : 'password'"
                            name="password_confirmation"
                            required
                            minlength="6"
                            placeholder="Confirm new password"
                        >
                        <button class="rp-password-toggle" type="button" @click="showConfirmPassword = !showConfirmPassword" aria-label="Show confirm password" :class="{ 'is-visible': showConfirmPassword }">
                            <svg class="eye-icon" viewBox="0 0 24 24" width="19" height="19" fill="none"
                                 stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg class="eye-off-icon" viewBox="0 0 24 24" width="19" height="19" fill="none"
                                 stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                                <path d="M10.73 10.73A3 3 0 0 0 12 15a3 3 0 0 0 2.09-.81"/>
                                <line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button class="rp-btn" type="submit" id="rpBtn" wire:loading.attr="disabled">
                    <span class="rp-btn-label" style="display:inline-flex;align-items:center;gap:8px;" wire:loading.remove wire:target="resetPassword">
                        Update Password
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                    </span>
                    <span class="rp-spin" wire:loading wire:target="resetPassword" style="display:block;"></span>
                </button>

                <div class="rp-note">
                    <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Your session will be authenticated automatically after reset.
                </div>
            </form>
        </div>

    </div>
</div></div>
