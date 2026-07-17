<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $user = auth()->user();
        if ($user) {
            $redirectUrl = $user->isSuperAdmin() ? url('admin') : route('dashboard', absolute: false);
            $this->redirectIntended(default: $redirectUrl, navigate: true);
        }
    }
}; ?>
<div>

<style>
    :root {
        --puc-white: #ffffff;
        --puc-black: #050816;
        --puc-blue: #2F5BFF;

        --puc-text: var(--puc-black);
        --puc-muted: rgba(5, 8, 22, 0.66);
        --puc-border: rgba(5, 8, 22, 0.10);
        --puc-blue-soft: rgba(47, 91, 255, 0.08);
        --puc-blue-border: rgba(47, 91, 255, 0.22);
        --puc-shadow: 0 26px 80px rgba(5, 8, 22, 0.12);
    }

    .puc-login-page {
        position: relative;
        min-height: calc(100vh - 76px);
        padding: 76px 0;
        overflow: hidden;
        background:
            radial-gradient(circle at 82% 18%, rgba(47, 91, 255, 0.18), transparent 34%),
            radial-gradient(circle at 12% 82%, rgba(47, 91, 255, 0.08), transparent 28%),
            var(--puc-white);
    }

    .puc-login-page::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(5, 8, 22, 0.035) 1px, transparent 1px),
            linear-gradient(90deg, rgba(5, 8, 22, 0.035) 1px, transparent 1px);
        background-size: 36px 36px;
        mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.42), transparent 72%);
        pointer-events: none;
    }

    .puc-login-page::after {
        content: "";
        position: absolute;
        width: 520px;
        height: 520px;
        right: -210px;
        top: 90px;
        border-radius: 999px;
        background: rgba(47, 91, 255, 0.10);
        filter: blur(4px);
        pointer-events: none;
    }

    .puc-login-layout {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: minmax(0, 1fr) 462px;
        gap: 54px;
        align-items: center;
    }

    .puc-login-info {
        max-width: 690px;
    }

    .puc-login-badge {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        padding: 10px 16px;
        border-radius: 999px;
        color: var(--puc-black);
        background: var(--puc-white);
        border: 1px solid var(--puc-blue-border);
        box-shadow: 0 12px 34px rgba(47, 91, 255, 0.10);
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }

    .puc-login-badge::before {
        content: "";
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: var(--puc-blue);
        box-shadow: 0 0 0 6px rgba(47, 91, 255, 0.14);
    }

    .puc-login-title {
        margin: 24px 0 18px;
        color: var(--puc-black) !important;
        font-size: clamp(42px, 5.4vw, 72px);
        line-height: 1.02;
        letter-spacing: -0.07em;
        font-weight: 700;
    }

    .puc-login-title span {
        color: var(--puc-blue);
    }

    .puc-login-desc {
        max-width: 560px;
        margin: 0;
        color: var(--puc-muted);
        font-size: 17px;
        line-height: 1.78;
        font-weight: 500;
    }

    .puc-login-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        margin-top: 30px;
    }

    .puc-login-action-primary,
    .puc-login-action-secondary {
        min-height: 54px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 0 24px;
        border-radius: 15px;
        text-decoration: none;
        font-size: 15px;
        font-weight: 700;
        transition: 0.22s ease;
    }

    .puc-login-action-primary {
        color: var(--puc-white);
        background: var(--puc-blue);
        border: 1px solid var(--puc-blue);
        box-shadow: 0 18px 34px rgba(47, 91, 255, 0.30);
    }

    .puc-login-action-primary:hover {
        color: var(--puc-white);
        transform: translateY(-2px);
        box-shadow: 0 24px 46px rgba(47, 91, 255, 0.38);
    }

    .puc-login-action-secondary {
        color: var(--puc-black);
        background: var(--puc-white);
        border: 1px solid var(--puc-border);
        box-shadow: 0 12px 30px rgba(5, 8, 22, 0.06);
    }

    .puc-login-action-secondary:hover {
        color: var(--puc-blue);
        border-color: var(--puc-blue-border);
        transform: translateY(-2px);
    }

    .puc-login-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 24px;
    }

    .puc-login-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 999px;
        color: var(--puc-black);
        background: var(--puc-white);
        border: 1px solid var(--puc-border);
        box-shadow: 0 10px 26px rgba(5, 8, 22, 0.06);
        font-size: 13px;
        font-weight: 600;
    }

    .puc-login-pill svg {
        width: 16px;
        height: 16px;
        color: var(--puc-blue);
        stroke: currentColor;
        stroke-width: 2.4;
        fill: none;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .puc-login-card {
        position: relative;
        overflow: hidden;
        padding: 34px;
        border-radius: 30px;
        background: var(--puc-white);
        border: 1px solid var(--puc-border);
        box-shadow: var(--puc-shadow);
    }

    .puc-login-card::before {
        content: "";
        position: absolute;
        inset: 0 0 auto 0;
        height: 5px;
        background: var(--puc-blue);
    }

    .puc-login-card::after {
        content: "";
        position: absolute;
        width: 220px;
        height: 220px;
        right: -90px;
        top: -90px;
        border-radius: 999px;
        background: rgba(47, 91, 255, 0.08);
        pointer-events: none;
    }

    .puc-login-card > * {
        position: relative;
        z-index: 1;
    }

    .puc-login-card-head {
        margin-bottom: 25px;
        padding-bottom: 22px;
        border-bottom: 1px solid var(--puc-border);
    }

    .puc-login-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        color: var(--puc-blue);
        background: var(--puc-blue-soft);
        border: 1px solid var(--puc-blue-border);
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .puc-login-eyebrow::before {
        content: "";
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: var(--puc-blue);
    }

    .puc-login-card h2 {
        margin: 15px 0 8px;
        color: var(--puc-black) !important;
        font-size: 34px;
        line-height: 1.08;
        letter-spacing: -0.045em;
        font-weight: 700;
    }

    .puc-login-subtitle {
        margin: 0;
        color: var(--puc-muted);
        font-size: 14.5px;
        line-height: 1.62;
        font-weight: 500;
    }

    .puc-login-form-group {
        margin-bottom: 17px;
    }

    .puc-login-form-group label {
        display: block;
        margin-bottom: 8px;
        color: var(--puc-black);
        font-size: 13px;
        font-weight: 700;
    }

    .puc-input-wrap {
        position: relative;
    }

    .puc-input-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        width: 19px;
        height: 19px;
        color: rgba(5, 8, 22, 0.48);
        pointer-events: none;
        transition: 0.22s ease;
    }

    .puc-input-icon svg {
        width: 19px;
        height: 19px;
        display: block;
        stroke: currentColor;
        stroke-width: 2.15;
        fill: none;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .puc-login-form-group input {
        width: 100%;
        height: 55px;
        padding: 0 15px 0 46px;
        border-radius: 16px;
        border: 1px solid var(--puc-border);
        background: var(--puc-white);
        color: var(--puc-black);
        outline: none;
        font-size: 14.5px;
        font-weight: 780;
        transition: 0.22s ease;
        box-shadow: inset 0 1px 0 rgba(5, 8, 22, 0.035);
    }

    .puc-login-form-group input::placeholder {
        color: rgba(5, 8, 22, 0.40);
        font-weight: 700;
    }

    .puc-login-form-group input:focus {
        border-color: rgba(47, 91, 255, 0.52);
        box-shadow: 0 0 0 4px rgba(47, 91, 255, 0.10);
    }

    .puc-input-wrap:focus-within .puc-input-icon {
        color: var(--puc-blue);
    }

    .puc-password-input input {
        padding-right: 55px;
    }

    .puc-password-toggle {
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
        color: var(--puc-blue);
        background: var(--puc-blue-soft);
        cursor: pointer;
        transition: 0.2s ease;
        z-index: 10;
    }

    .puc-password-toggle:hover {
        color: var(--puc-white);
        background: var(--puc-blue);
    }

    .puc-password-toggle svg {
        width: 19px;
        height: 19px;
        display: block;
        stroke: currentColor;
        stroke-width: 2.2;
        fill: none;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .puc-password-toggle .eye-off-icon {
        display: none !important;
    }

    .puc-password-toggle.is-visible .eye-icon {
        display: none !important;
    }

    .puc-password-toggle.is-visible .eye-off-icon {
        display: block !important;
    }

    .puc-login-submit {
        width: 100%;
        height: 57px;
        margin-top: 5px;
        border: 0;
        border-radius: 16px;
        color: var(--puc-white);
        background: var(--puc-blue);
        box-shadow: 0 18px 36px rgba(47, 91, 255, 0.32);
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: 0.22s ease;
    }

    .puc-login-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 24px 48px rgba(47, 91, 255, 0.40);
    }

    .puc-login-note {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        margin-top: 17px;
        padding: 14px;
        border-radius: 18px;
        background: var(--puc-blue-soft);
        border: 1px solid var(--puc-blue-border);
        color: rgba(5, 8, 22, 0.68);
        font-size: 13px;
        line-height: 1.5;
        font-weight: 720;
    }

    .puc-login-note svg {
        flex: 0 0 auto;
        width: 21px;
        height: 21px;
        color: var(--puc-blue);
        stroke: currentColor;
        stroke-width: 2.2;
        fill: none;
        stroke-linecap: round;
        stroke-linejoin: round;
        margin-top: 1px;
    }

    .puc-login-footer {
        margin: 18px 0 0;
        color: rgba(5, 8, 22, 0.65);
        text-align: center;
        font-size: 14px;
        line-height: 1.8;
        font-weight: 760;
    }

    .puc-login-footer a {
        color: var(--puc-blue);
        text-decoration: none;
        font-weight: 700;
    }

    .puc-login-footer a:hover {
        color: var(--puc-black);
        text-decoration: underline;
    }

    .puc-login-divider {
        display: inline-block;
        margin: 0 8px;
        color: rgba(5, 8, 22, 0.28);
    }

    @media (max-width: 991px) {
        .puc-login-page {
            padding: 54px 0 66px;
        }

        .puc-login-layout {
            grid-template-columns: 1fr;
            gap: 32px;
            max-width: 640px;
            margin: 0 auto;
        }

        .puc-login-info {
            text-align: center;
            margin: 0 auto;
        }

        .puc-login-desc {
            margin: 0 auto;
        }

        .puc-login-actions,
        .puc-login-pills {
            justify-content: center;
        }
    }

    @media (max-width: 640px) {
        .puc-login-page {
            padding: 38px 0 50px;
        }

        .puc-login-info {
            text-align: left;
        }

        .puc-login-title {
            margin-top: 19px;
            font-size: 39px;
            letter-spacing: -0.055em;
        }

        .puc-login-desc {
            font-size: 14.5px;
            line-height: 1.68;
        }

        .puc-login-actions,
        .puc-login-pills {
            justify-content: flex-start;
        }

        .puc-login-action-primary,
        .puc-login-action-secondary {
            width: 100%;
        }

        .puc-login-card {
            padding: 25px;
            border-radius: 25px;
        }

        .puc-login-card h2 {
            font-size: 29px;
        }

        .puc-login-form-group input {
            height: 53px;
            border-radius: 15px;
        }

        .puc-login-submit {
            height: 55px;
        }
    }
</style>

<section class="puc-login-page" x-data="{ showPassword: false }">
    <div class="container">
        <div class="puc-login-layout">

            <div class="puc-login-info">
                <span class="puc-login-badge">PUC Center Management Software</span>

                <h1 class="puc-login-title">
                    Manage PUC Records. <span>Track Expiry.</span>
                </h1>

                <p class="puc-login-desc">
                    Sign in to manage customer records, track certificate expiry,
                    prepare WhatsApp reminders, and continue your daily PUC center workflow.
                </p>

                <div class="puc-login-actions">
                    <a href="{{ route('register') }}" class="puc-login-action-primary" wire:navigate>
                        Start Free Trial
                    </a>
                    <a href="{{ url('/') }}#pricing" class="puc-login-action-secondary">
                        View Plans
                    </a>
                </div>

                <div class="puc-login-pills">
                    <span class="puc-login-pill">
                        <svg viewBox="0 0 24 24">
                            <path d="M20 6L9 17l-5-5"></path>
                        </svg>
                        No technical knowledge needed
                    </span>

                    <span class="puc-login-pill">
                        <svg viewBox="0 0 24 24">
                            <path d="M20 6L9 17l-5-5"></path>
                        </svg>
                        Works on mobile & desktop
                    </span>
                </div>
            </div>

            <form class="puc-login-card" wire:submit="login" autocomplete="off">
                <div class="puc-login-card-head">
                    <span class="puc-login-eyebrow">Login</span>
                    <h2>Access your account</h2>
                    <p class="puc-login-subtitle">
                        Enter your registered mobile number and password to continue.
                    </p>
                </div>

                @if (session('warning'))
                    <div class="alert alert-warning mb-4" style="background:#fffbeb;border:1px solid #fef3c7;color:#d97706;padding:12px;border-radius:12px;font-size:13px;font-weight:600;">
                        {{ session('warning') }}
                    </div>
                @endif

                <!-- Validation Alerts -->
                @if ($errors->has('form.mobile'))
                    <div class="alert alert-danger mb-4" style="background:#fef2f2;border:1px solid #fee2e2;color:#dc2626;padding:12px;border-radius:12px;font-size:13px;font-weight:600;">
                        {{ $errors->first('form.mobile') }}
                    </div>
                @endif

                @if ($errors->has('form.password'))
                    <div class="alert alert-danger mb-4" style="background:#fef2f2;border:1px solid #fee2e2;color:#dc2626;padding:12px;border-radius:12px;font-size:13px;font-weight:600;">
                        {{ $errors->first('form.password') }}
                    </div>
                @endif

                <div class="puc-login-form-group">
                    <label for="mobile">Mobile Number</label>
                    <div class="puc-input-wrap">
                        <span class="puc-input-icon">
                            <svg viewBox="0 0 24 24">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.8 19.8 0 0 1 3 5.18 2 2 0 0 1 5 3h3a2 2 0 0 1 2 1.72c.12.9.33 1.77.62 2.6a2 2 0 0 1-.45 2.11L8.91 10.7a16 16 0 0 0 4.39 4.39l1.27-1.26a2 2 0 0 1 2.11-.45c.83.29 1.7.5 2.6.62A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                        </span>
                        <input
                            wire:model="form.mobile"
                            id="mobile"
                            name="mobile"
                            required
                            maxlength="10"
                            inputmode="numeric"
                            pattern="[0-9]{10}"
                            placeholder="Registered mobile number"
                        >
                    </div>
                </div>

                <div class="puc-login-form-group">
                    <label for="password">Password</label>
                    <div class="puc-input-wrap puc-password-input">
                        <span class="puc-input-icon">
                            <svg viewBox="0 0 24 24">
                                <rect x="3" y="11" width="18" height="11" rx="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </span>
                        <input
                            wire:model="form.password"
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            name="password"
                            required
                            placeholder="Enter password"
                        >
                        <button class="puc-password-toggle" type="button" @click="showPassword = !showPassword" aria-label="Show password" :class="{ 'is-visible': showPassword }">
                            <svg class="eye-icon" viewBox="0 0 24 24" width="19" height="19" fill="none"
                                 stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/>
                                <circle cx="12" cy="12" r="3"/>
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

                <div class="mb-4 flex items-center justify-between" style="font-size: 13.5px; font-weight: 600; color: rgba(5,8,22,0.65);">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="form.remember" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        Remember me
                    </label>
                </div>

                <button class="puc-login-submit" type="submit">Login to Dashboard</button>

                <div class="puc-login-note">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        <path d="M9 12l2 2 4-4"></path>
                    </svg>
                    <div>
                        Use the same mobile number that was verified during account registration.
                    </div>
                </div>

                <p class="puc-login-footer">
                    <a href="{{ route('password.request') }}" wire:navigate>Forgot password?</a>
                    <span class="puc-login-divider">•</span>
                    <a href="{{ route('register') }}" wire:navigate>Create account</a>
                </p>
            </form>

        </div>
    </div>
</section>
</div>
