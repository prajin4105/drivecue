<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Services\OtpService;

new #[Layout('layouts.app')] class extends Component
{
    public string $first_name = '';
    public string $last_name = '';
    public string $center_name = '';
    public string $center_address = '';
    public string $mobile = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $this->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'center_name' => ['required', 'string', 'max:180'],
            'center_address' => ['required', 'string', 'max:500'],
            'mobile' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $otpService = app(OtpService::class);
        $mobileNormalized = $otpService->normalizeMobile($this->mobile);

        if (!$otpService->isValidMobile($mobileNormalized)) {
            $this->addError('mobile', 'Please enter a valid 10-digit mobile number.');
            return;
        }

        $existing = User::where('mobile', $mobileNormalized)->first();
        if ($existing && $existing->mobile_verified) {
            $this->addError('mobile', 'This mobile number is already registered.');
            return;
        }

        $hash = Hash::make($this->password);

        if ($existing) {
            $existing->update([
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'center_name' => $this->center_name,
                'center_address' => $this->center_address,
                'password' => $hash,
                'role' => 1, // center owner
                'status' => 'pending',
            ]);
        } else {
            User::create([
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'center_name' => $this->center_name,
                'center_address' => $this->center_address,
                'mobile' => $mobileNormalized,
                'password' => $hash,
                'role' => 1, // center owner
                'mobile_verified' => false,
                'status' => 'pending',
            ]);
        }

        try {
            $otp = $otpService->createOtp($mobileNormalized, 'register', request()->ip());
            
            $msg = 'OTP sent to your mobile number.';
            if (config('app.debug') || app()->environment('local')) {
                $msg = "OTP sent. Debug OTP: {$otp}";
            }
            
            session()->flash('success', $msg);
            $this->redirect(route('verify-otp', ['purpose' => 'register', 'mobile' => $mobileNormalized]), navigate: true);
        } catch (\Throwable $e) {
            $this->addError('mobile', $e->getMessage());
        }
    }
}; ?>
<div>

<style>
    :root {
        --puc-black: #0B1020;
        --puc-blue: #2457E6;
        --puc-blue-dark: #1E46C2;
        --puc-white: #FFFFFF;
        --puc-bg: #F7F9FD;
        --puc-text: #0B1020;
        --puc-muted: #5B6475;
        --puc-line: rgba(11, 16, 32, 0.10);
        --puc-shadow: 0 22px 55px rgba(11, 16, 32, 0.10);
        --puc-shadow-soft: 0 10px 28px rgba(11, 16, 32, 0.07);
    }

    .puc-register-page {
        position: relative;
        overflow: hidden;
        min-height: calc(100vh - 76px);
        padding: 56px 0 70px;
        background:
            radial-gradient(circle at 12% 0%, rgba(36, 87, 230, 0.08), transparent 32%),
            radial-gradient(circle at 88% 8%, rgba(36, 87, 230, 0.07), transparent 30%),
            linear-gradient(180deg, #FFFFFF 0%, #F7F9FD 100%);
    }

    .puc-register-page::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background-image:
            linear-gradient(rgba(11, 16, 32, 0.035) 1px, transparent 1px),
            linear-gradient(90deg, rgba(11, 16, 32, 0.035) 1px, transparent 1px);
        background-size: 42px 42px;
        mask-image: linear-gradient(180deg, rgba(0,0,0,0.45), transparent 65%);
    }

    .puc-register-page::after {
        content: "";
        position: absolute;
        width: 460px;
        height: 460px;
        right: -210px;
        top: 86px;
        border-radius: 999px;
        background: rgba(36, 87, 230, 0.08);
        pointer-events: none;
        filter: blur(2px);
    }

    .puc-register-shell {
        position: relative;
        z-index: 1;
        max-width: 900px;
        margin: 0 auto;
    }

    .puc-register-hero {
        text-align: center;
        max-width: 760px;
        margin: 0 auto 28px;
    }

    .puc-register-badge,
    .puc-register-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        padding: 9px 14px;
        border-radius: 999px;
        color: var(--puc-blue);
        background: #FFFFFF;
        border: 1px solid rgba(36, 87, 230, 0.16);
        font-size: 12.5px;
        font-weight: 700;
        box-shadow: var(--puc-shadow-soft);
    }

    .puc-register-badge::before,
    .puc-register-eyebrow::before {
        content: "";
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: var(--puc-blue);
        box-shadow: 0 0 0 5px rgba(36, 87, 230, 0.12);
        flex: 0 0 auto;
    }

    .puc-register-hero h1 {
        margin: 18px 0 12px;
        color: var(--puc-black) !important;
        font-size: clamp(36px, 5vw, 60px);
        line-height: 1.02;
        letter-spacing: -0.065em;
        font-weight: 700;
    }

    .puc-register-hero h1 span {
        color: var(--puc-blue);
        background: none;
        -webkit-background-clip: initial;
        background-clip: initial;
    }

    .puc-register-hero p {
        margin: 0 auto;
        max-width: 690px;
        color: var(--puc-muted) !important;
        font-size: 16px;
        line-height: 1.72;
        font-weight: 500;
    }

    .puc-register-card-wrap {
        position: relative;
        max-width: 820px;
        margin: 0 auto;
    }

    .puc-register-card-wrap::before {
        content: "";
        position: absolute;
        inset: 22px -16px -16px 18px;
        border-radius: 34px;
        background: rgba(36, 87, 230, 0.08);
        filter: blur(1px);
        pointer-events: none;
    }

    .puc-register-card {
        position: relative;
        overflow: hidden;
        border-radius: 28px;
        padding: 32px;
        background: var(--puc-white);
        border: 1px solid var(--puc-line);
        box-shadow: var(--puc-shadow);
    }

    .puc-register-card::before {
        content: "";
        position: absolute;
        left: 0;
        right: 0;
        top: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--puc-black), var(--puc-blue), var(--puc-black));
    }

    .puc-register-card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 24px;
        padding-bottom: 22px;
        border-bottom: 1px solid var(--puc-line);
    }

    .puc-register-title-area { min-width: 0; }

    .puc-register-card h2 {
        margin: 14px 0 7px;
        color: var(--puc-black) !important;
        font-size: clamp(28px, 3vw, 40px);
        line-height: 1.06;
        letter-spacing: -0.055em;
        font-weight: 700;
    }

    .puc-register-subtitle {
        margin: 0;
        color: var(--puc-muted);
        line-height: 1.65;
        font-size: 14.5px;
        font-weight: 500;
    }

    .puc-register-card-head .puc-register-mini-card {
        flex: 0 0 auto;
        min-width: 172px;
        padding: 16px;
        border-radius: 20px;
        background: #FFFFFF !important;
        color: var(--puc-black) !important;
        border: 1px solid rgba(36, 87, 230, 0.22) !important;
        box-shadow: 0 10px 26px rgba(11, 16, 32, 0.08) !important;
    }

    .puc-register-card-head .puc-register-mini-card strong {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 22px;
        line-height: 1;
        font-weight: 700;
        color: var(--puc-black) !important;
    }

    .puc-register-card-head .puc-register-mini-card strong::before {
        content: "";
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: var(--puc-blue);
        box-shadow: 0 0 0 5px rgba(36, 87, 230, 0.12);
        flex: 0 0 auto;
    }

    .puc-register-card-head .puc-register-mini-card span {
        display: block;
        margin-top: 8px;
        color: var(--puc-muted);
        font-size: 12px;
        line-height: 1.4;
        font-weight: 760;
    }

    .puc-register-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 15px;
    }

    .puc-register-form-group { margin-bottom: 15px; }

    .puc-register-form-group label {
        display: block;
        margin-bottom: 8px;
        color: var(--puc-black);
        font-size: 13px;
        font-weight: 700;
    }

    .puc-input-wrap { position: relative; }

    .puc-input-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        width: 18px;
        height: 18px;
        color: #7A8494;
        pointer-events: none;
        transition: 0.2s ease;
    }

    .puc-input-icon svg {
        width: 18px;
        height: 18px;
        display: block;
        stroke: currentColor;
        stroke-width: 2.1;
        fill: none;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .puc-register-form-group input {
        width: 100%;
        height: 52px;
        border-radius: 15px;
        border: 1px solid rgba(11, 16, 32, 0.12);
        background: #FFFFFF;
        color: var(--puc-black);
        padding: 0 15px 0 44px;
        outline: none;
        font-size: 14.5px;
        font-weight: 720;
        transition: 0.22s ease;
        box-shadow: 0 1px 0 rgba(11, 16, 32, 0.03);
    }

    .puc-register-form-group input::placeholder {
        color: #98A2B3;
        font-weight: 500;
    }

    .puc-register-form-group input:hover {
        border-color: rgba(36, 87, 230, 0.24);
        background: #FFFFFF;
    }

    .puc-register-form-group input:focus {
        border-color: rgba(36, 87, 230, 0.55);
        background: #ffffff;
        box-shadow:
            0 0 0 4px rgba(36, 87, 230, 0.10),
            0 12px 26px rgba(11, 16, 32, 0.06);
    }

    .puc-input-wrap:focus-within .puc-input-icon { color: var(--puc-blue); }
    .puc-password-input input { padding-right: 54px; }

    .puc-password-toggle {
        position: absolute;
        top: 50%;
        right: 8px;
        transform: translateY(-50%);
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 13px;
        color: var(--puc-blue);
        background: rgba(36, 87, 230, 0.08);
        cursor: pointer;
        transition: 0.22s ease;
    }

    .puc-password-toggle:hover {
        color: var(--puc-black);
        background: rgba(36, 87, 230, 0.13);
    }

    .puc-password-toggle svg {
        width: 19px;
        height: 19px;
        stroke: currentColor;
        stroke-width: 2.2;
        fill: none;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .puc-password-toggle .eye-off-icon { display: none !important; }
    .puc-password-toggle.is-visible .eye-icon { display: none !important; }
    .puc-password-toggle.is-visible .eye-off-icon { display: block !important; }

    .puc-register-submit {
        width: 100%;
        height: 56px;
        margin-top: 6px;
        border: 0;
        border-radius: 16px;
        color: #ffffff;
        background: var(--puc-blue);
        box-shadow: 0 16px 32px rgba(36, 87, 230, 0.23);
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: 0.22s ease;
    }

    .puc-register-submit:hover {
        transform: translateY(-1px);
        background: var(--puc-blue-dark);
        box-shadow: 0 20px 40px rgba(36, 87, 230, 0.30);
    }

    .puc-register-note {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-top: 16px;
        padding: 13px;
        border-radius: 16px;
        background: rgba(36, 87, 230, 0.06);
        border: 1px solid rgba(36, 87, 230, 0.13);
        color: var(--puc-muted);
        font-size: 13px;
        line-height: 1.5;
        font-weight: 700;
    }

    .puc-register-note svg {
        flex: 0 0 auto;
        width: 22px;
        height: 22px;
        color: var(--puc-blue);
        stroke: currentColor;
        stroke-width: 2.2;
        fill: none;
        stroke-linecap: round;
        stroke-linejoin: round;
        margin-top: 1px;
    }

    .puc-register-form-footer {
        margin: 18px 0 0;
        color: var(--puc-muted);
        text-align: center;
        font-size: 14px;
        line-height: 1.6;
        font-weight: 720;
    }

    .puc-register-form-footer a {
        color: var(--puc-blue);
        text-decoration: none;
        font-weight: 700;
    }

    .puc-register-form-footer a:hover {
        color: var(--puc-black);
        text-decoration: underline;
    }

    @media (max-width: 760px) {
        .puc-register-page { padding: 40px 0 56px; }
        .puc-register-card { padding: 24px; border-radius: 24px; }
        .puc-register-card-wrap::before { display: none; }
        .puc-register-card-head { display: block; }
        .puc-register-mini-card { margin-top: 16px; }
        .puc-register-form-grid { grid-template-columns: 1fr; gap: 0; }
        .puc-register-hero h1 { font-size: 38px; }
    }

    @media (max-width: 460px) {
        .puc-register-hero { text-align: left; }
        .puc-register-hero h1 { font-size: 33px; }
        .puc-register-card { padding: 21px; }
    }
</style>

<section class="puc-register-page" x-data="{ showPassword: false, showConfirmPassword: false }">
    <div class="container puc-register-shell">

        <div class="puc-register-hero">
            <span class="puc-register-badge">Secure Center Workspace</span>
            <h1>Create a secure <span>renewal workspace.</span></h1>
            <p>
                Register your center to manage records, track expiries, prepare professional reminders,
                and access a cleaner business workflow.
            </p>
        </div>

        <div class="puc-register-card-wrap">
            <form class="puc-register-card" wire:submit="register" autocomplete="off">
                <div class="puc-register-card-head">
                    <div class="puc-register-title-area">
                        <span class="puc-register-eyebrow">Create Account</span>
                        <h2>Register your center</h2>
                        <p class="puc-register-subtitle">
                            Enter your details to create a secure account and verify your mobile number with OTP.
                        </p>
                    </div>

                    <div class="puc-register-mini-card">
                        <strong>Secure</strong>
                        <span>Mobile verification before account activation</span>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger mb-4" style="background:#fef2f2;border:1px solid #fee2e2;color:#dc2626;padding:12px;border-radius:12px;font-size:13px;font-weight:600;">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="puc-register-form-grid">
                    <div class="puc-register-form-group">
                        <label for="first_name">First Name</label>
                        <div class="puc-input-wrap">
                            <span class="puc-input-icon">
                                <svg viewBox="0 0 24 24"><path d="M20 21a8 8 0 0 0-16 0"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            </span>
                            <input
                                wire:model="first_name"
                                id="first_name"
                                name="first_name"
                                required
                                placeholder="First name"
                            >
                        </div>
                    </div>

                    <div class="puc-register-form-group">
                        <label for="last_name">Last Name</label>
                        <div class="puc-input-wrap">
                            <span class="puc-input-icon">
                                <svg viewBox="0 0 24 24"><path d="M20 21a8 8 0 0 0-16 0"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            </span>
                            <input
                                wire:model="last_name"
                                id="last_name"
                                name="last_name"
                                required
                                placeholder="Last name"
                            >
                        </div>
                    </div>
                </div>

                <div class="puc-register-form-group">
                    <label for="center_name">Center Name</label>
                    <div class="puc-input-wrap">
                        <span class="puc-input-icon">
                            <svg viewBox="0 0 24 24"><path d="M3 21h18"></path><path d="M5 21V7l8-4v18"></path><path d="M19 21V11l-6-4"></path><path d="M9 9h1"></path><path d="M9 13h1"></path><path d="M9 17h1"></path></svg>
                        </span>
                        <input
                            wire:model="center_name"
                            id="center_name"
                            name="center_name"
                            required
                            placeholder="Example: Prime Renewal Center"
                        >
                    </div>
                </div>

                <div class="puc-register-form-group">
                    <label for="center_address">Center Address</label>
                    <div class="puc-input-wrap">
                        <span class="puc-input-icon" style="top: 24px;">
                            <svg viewBox="0 0 24 24" style="width:18px;height:18px;stroke:currentColor;stroke-width:2.1;fill:none;stroke-linecap:round;stroke-linejoin:round;"><path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"/><circle cx="12" cy="10" r="3"/></svg>
                        </span>
                        <textarea
                            wire:model="center_address"
                            id="center_address"
                            name="center_address"
                            required
                            placeholder="Center Address (will be included in WhatsApp reminders)"
                            style="width: 100%; min-height: 80px; border-radius: 15px; border: 1px solid rgba(11, 16, 32, 0.12); background: #FFFFFF; color: var(--puc-black); padding: 12px 15px 12px 44px; outline: none; font-size: 14.5px; font-weight: 720; transition: 0.22s ease; box-shadow: 0 1px 0 rgba(11, 16, 32, 0.03); resize: vertical;"
                        ></textarea>
                    </div>
                </div>

                <div class="puc-register-form-group">
                    <label for="mobile">Mobile Number</label>
                    <div class="puc-input-wrap">
                        <span class="puc-input-icon">
                            <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.8 19.8 0 0 1 3 5.18 2 2 0 0 1 5 3h3a2 2 0 0 1 2 1.72c.12.9.33 1.77.62 2.6a2 2 0 0 1-.45 2.11L8.91 10.7a16 16 0 0 0 4.39 4.39l1.27-1.26a2 2 0 0 1 2.11-.45c.83.29 1.7.5 2.6.62A2 2 0 0 1 22 16.92z"></path></svg>
                        </span>
                        <input
                            wire:model="mobile"
                            id="mobile"
                            name="mobile"
                            required
                            maxlength="10"
                            inputmode="numeric"
                            pattern="[0-9]{10}"
                            placeholder="10 digit mobile number"
                        >
                    </div>
                </div>

                <div class="puc-register-form-grid">
                    <div class="puc-register-form-group">
                        <label for="password">Password</label>
                        <div class="puc-input-wrap puc-password-input">
                            <span class="puc-input-icon">
                                <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            </span>
                            <input
                                wire:model="password"
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                name="password"
                                required
                                minlength="6"
                                placeholder="Create password"
                            >
                            <button class="puc-password-toggle" type="button" @click="showPassword = !showPassword" aria-label="Show password" :class="{ 'is-visible': showPassword }">
                                <svg class="eye-icon" viewBox="0 0 24 24" width="19" height="19" fill="none"
                                     stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"></path><circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg class="eye-off-icon" viewBox="0 0 24 24" width="19" height="19" fill="none"
                                     stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"></path>
                                    <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"></path>
                                    <path d="M10.73 10.73A3 3 0 0 0 12 15a3 3 0 0 0 2.09-.81"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="puc-register-form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <div class="puc-input-wrap puc-password-input">
                            <span class="puc-input-icon">
                                <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            </span>
                            <input
                                wire:model="password_confirmation"
                                id="confirm_password"
                                :type="showConfirmPassword ? 'text' : 'password'"
                                name="password_confirmation"
                                required
                                minlength="6"
                                placeholder="Confirm password"
                            >
                            <button class="puc-password-toggle" type="button" @click="showConfirmPassword = !showConfirmPassword" aria-label="Show confirm password" :class="{ 'is-visible': showConfirmPassword }">
                                <svg class="eye-icon" viewBox="0 0 24 24" width="19" height="19" fill="none"
                                     stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"></path><circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg class="eye-off-icon" viewBox="0 0 24 24" width="19" height="19" fill="none"
                                     stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"></path>
                                    <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"></path>
                                    <path d="M10.73 10.73A3 3 0 0 0 12 15a3 3 0 0 0 2.09-.81"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <button class="puc-register-submit" type="submit">Send OTP & Create Account</button>

                <div class="puc-register-note">
                    <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="M9 12l2 2 4-4"></path></svg>
                    <div>
                        Your mobile number will be verified before account activation. Use a valid number to receive the OTP.
                    </div>
                </div>

                <p class="puc-register-form-footer">
                    Already have an account?
                    <a href="{{ route('login') }}" wire:navigate>Login here</a>
                </p>
            </form>
        </div>

    </div>
</section>
</div>

