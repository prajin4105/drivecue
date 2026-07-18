<?php

use App\Models\User;
use App\Plan;
use App\Models\Subscription;
use App\Services\OtpService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Carbon\Carbon;

new #[Layout('layouts.app')] class extends Component
{
    #[Url]
    public string $mobile = '';

    #[Url]
    public string $purpose = 'register';

    public string $otp = '';

    public function mount(): void
    {
        if (empty($this->mobile) || !in_array($this->purpose, ['register', 'forgot_password'])) {
            session()->flash('error', 'Invalid OTP request. Please start again.');
            $this->redirect(route('register'), navigate: true);
        }
    }

    /**
     * Handle the OTP verification form submission.
     */
    public function verify(): void
    {
        $otpService = app(OtpService::class);
        $mobileNormalized = $otpService->normalizeMobile($this->mobile);

        if (strlen($this->otp) !== 6) {
            $this->addError('otp', 'Please enter the complete 6-digit OTP.');
            return;
        }

        if (!$otpService->verifyOtp($mobileNormalized, $this->purpose, $this->otp)) {
            $this->addError('otp', 'Invalid or expired OTP. Please try again or request a new one.');
            return;
        }

        if ($this->purpose === 'register') {
            $user = User::where('mobile', $mobileNormalized)->first();

            if (!$user) {
                $this->addError('otp', 'Account not found. Please contact support.');
                return;
            }

            // Mark user verified and active
            $user->update([
                'mobile_verified' => true,
                'status' => 'active',
            ]);

            // Assign free trial subscription if it doesn't exist
            $plan = App\Models\Plan::where('status', 'active')->where('is_trial', true)->first()
                ?? App\Models\Plan::where('status', 'active')->orderBy('monthly_price', 'asc')->first();

            if ($plan) {
                $exists = Subscription::where('user_id', $user->id)->exists();
                if (!$exists) {
                    Subscription::create([
                        'user_id' => $user->id,
                        'plan_id' => $plan->id,
                        'start_date' => Carbon::today(),
                        'end_date' => Carbon::today()->addMonth()->subDay(),
                        'status' => 'active',
                        'payment_status' => 'trial',
                        'billing_cycle' => 'monthly',
                        'notes' => 'One-month Free Plan with 100 WhatsApp messages, assigned automatically on registration',
                    ]);
                }
            }

            // Log user in
            Auth::login($user);

            session()->flash('success', '🎉 Welcome! Your Free Plan is active for one month and includes 100 WhatsApp messages.');
            $this->redirect(route('dashboard', absolute: false), navigate: true);

        } elseif ($this->purpose === 'forgot_password') {
            session([
                'password_reset_mobile' => $mobileNormalized,
                'password_reset_verified' => true
            ]);

            session()->flash('success', 'OTP verified! Now set your new password.');
            $this->redirect(route('password.reset', ['mobile' => $mobileNormalized]), navigate: true);
        }
    }

    /**
     * Resend the OTP.
     */
    public function resend(): void
    {
        $otpService = app(OtpService::class);
        $mobileNormalized = $otpService->normalizeMobile($this->mobile);

        try {
            $otp = $otpService->createOtp($mobileNormalized, $this->purpose, request()->ip());
            
            $msg = 'New OTP sent! Check your SMS inbox.';
            if (config('app.debug') || app()->environment('local')) {
                $msg = "New OTP sent. Debug OTP: {$otp}";
            }
            
            session()->flash('success', $msg);
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }
    }
}; ?>
<div>

<style>
/* ── Shared layout ─────────────────────────────────────────────────── */
.auth-page {
    min-height: calc(100vh - 74px);
    display: grid;
    grid-template-columns: 1fr 480px;
    align-items: stretch;
    background: #ffffff; /* prevent body gray leaking */
}

.auth-left {
    background: linear-gradient(145deg, #0B1020 0%, #1a2448 60%, #0f1d3d 100%);
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 60px 64px;
    position: relative;
    overflow: hidden;
}

.auth-left::before {
    content: "";
    position: absolute;
    width: 500px; height: 500px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(36,87,230,0.35) 0%, transparent 70%);
    top: -180px; right: -180px;
}

.auth-left::after {
    content: "";
    position: absolute;
    width: 320px; height: 320px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(36,87,230,0.18) 0%, transparent 70%);
    bottom: -120px; left: -100px;
}

.auth-left-inner { position: relative; z-index: 1; }

.auth-left-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    border-radius: 999px;
    background: rgba(36,87,230,0.25);
    border: 1px solid rgba(36,87,230,0.45);
    color: #93B4FF;
    font-size: 12px; font-weight: 700;
    letter-spacing: 0.06em; text-transform: uppercase;
    margin-bottom: 28px;
}

.auth-left-badge::before {
    content: "";
    width: 7px; height: 7px;
    border-radius: 50%;
    background: #4F83FF;
    box-shadow: 0 0 0 5px rgba(79,131,255,0.25);
}

.auth-left h1 {
    margin: 0 0 16px;
    color: #fff;
    font-size: clamp(30px, 3.2vw, 46px);
    font-weight: 700;
    line-height: 1.06;
    letter-spacing: -0.055em;
}

.auth-left h1 span { color: #4F83FF; }

.auth-left p {
    color: rgba(255,255,255,0.60);
    font-size: 15px; line-height: 1.72; font-weight: 500;
    max-width: 400px; margin: 0 0 34px;
}

.auth-left-steps { display: flex; flex-direction: column; gap: 14px; }

.auth-step { display: flex; align-items: center; gap: 14px; }

.auth-step-num {
    width: 34px; height: 34px;
    border-radius: 10px;
    background: rgba(36,87,230,0.30);
    border: 1px solid rgba(79,131,255,0.40);
    color: #93B4FF;
    font-size: 13px; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

.auth-step-num.done {
    background: rgba(5,150,105,0.25);
    border-color: rgba(5,150,105,0.45);
    color: #34D399;
}

.auth-step-num.active {
    background: rgba(36,87,230,0.50);
    border-color: rgba(79,131,255,0.70);
    color: #fff;
    box-shadow: 0 0 0 4px rgba(79,131,255,0.20);
}

.auth-step-text {
    color: rgba(255,255,255,0.70);
    font-size: 14px; font-weight: 600; line-height: 1.4;
}

/* RIGHT panel */
.auth-right {
    background: #fff;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 52px 48px;
    border-left: 1px solid rgba(11,16,32,0.08);
}

/* Eyebrow */
.auth-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 6px 12px;
    border-radius: 999px;
    background: rgba(36,87,230,0.07);
    border: 1px solid rgba(36,87,230,0.18);
    color: #2457E6;
    font-size: 11.5px; font-weight: 700;
    letter-spacing: 0.06em; text-transform: uppercase;
    margin-bottom: 14px;
}

.auth-eyebrow::before {
    content: "";
    width: 6px; height: 6px;
    border-radius: 50%;
    background: currentColor;
}

.auth-right h2 {
    margin: 0 0 8px;
    color: #0B1020;
    font-size: 26px; font-weight: 700;
    letter-spacing: -0.045em; line-height: 1.1;
}

.auth-desc {
    color: rgba(11,16,32,0.58);
    font-size: 14px; line-height: 1.62; font-weight: 600;
    margin: 0 0 22px;
}

.auth-mobile-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: 999px;
    background: rgba(36,87,230,0.08);
    border: 1px solid rgba(36,87,230,0.20);
    color: #2457E6;
    font-size: 13px; font-weight: 700;
}

.auth-divider {
    height: 1px;
    background: rgba(11,16,32,0.08);
    margin: 0 0 20px;
}

/* Countdown */
.auth-countdown {
    display: flex; align-items: center;
    justify-content: space-between; gap: 10px;
    padding: 11px 13px; border-radius: 11px;
    background: rgba(245,158,11,0.07);
    border: 1px solid rgba(245,158,11,0.28);
    color: #92400E;
    font-size: 13px; font-weight: 700;
    margin-bottom: 20px;
    transition: background 0.4s, border-color 0.4s, color 0.4s;
}

.auth-countdown-left { display: flex; align-items: center; gap: 7px; }

.auth-countdown svg {
    width: 15px; height: 15px;
    stroke: currentColor; stroke-width: 2.2;
    fill: none; stroke-linecap: round; stroke-linejoin: round;
    flex-shrink: 0;
}

#voTimerVal { font-weight: 700; }

/* OTP Boxes */
.auth-otp-row {
    display: flex; gap: 10px;
    justify-content: space-between;
    margin-bottom: 20px;
}

.auth-otp-box {
    flex: 1 !important;
    height: 58px !important;
    border-radius: 12px !important;
    border: 1.5px solid rgba(11,16,32,0.12) !important;
    background: #F8FAFF !important;
    color: #0B1020 !important;
    font-size: 22px !important;
    font-weight: 700 !important;
    text-align: center !important;
    outline: none !important;
    box-shadow: none !important;
    transition: border-color 0.18s, box-shadow 0.18s !important;
    -moz-appearance: textfield !important;
    padding: 0 !important;
    font-family: inherit !important;
    min-height: unset !important; /* override global 42px min-height */
}

.auth-otp-box::-webkit-inner-spin-button,
.auth-otp-box::-webkit-outer-spin-button { -webkit-appearance: none !important; margin: 0; }

.auth-otp-box:focus {
    border-color: rgba(36,87,230,0.60) !important;
    box-shadow: 0 0 0 4px rgba(36,87,230,0.10) !important;
    background: #fff !important;
    outline: none !important;
}

.auth-otp-box.filled {
    border-color: rgba(36,87,230,0.45) !important;
    background: rgba(36,87,230,0.06) !important;
    color: #2457E6 !important;
}


/* Alert */
.auth-alert {
    display: flex; align-items: flex-start; gap: 10px;
    padding: 12px 13px; border-radius: 11px;
    font-size: 13px; font-weight: 700; line-height: 1.5;
    margin-bottom: 16px;
}

.auth-alert.danger  { background: rgba(220,38,38,0.06); border: 1px solid rgba(220,38,38,0.22); color: #991B1B; }
.auth-alert.success { background: rgba(5,150,105,0.07); border: 1px solid rgba(5,150,105,0.25); color: #065F46; }
.auth-alert.info    { background: rgba(36,87,230,0.07); border: 1px solid rgba(36,87,230,0.20); color: #1E3A8A; }

.auth-alert svg {
    flex-shrink: 0; width: 16px; height: 16px;
    stroke: currentColor; stroke-width: 2.2;
    fill: none; stroke-linecap: round; stroke-linejoin: round;
    margin-top: 1px;
}

/* Submit button */
.auth-btn {
    width: 100%; height: 50px;
    border: none; border-radius: 12px;
    background: #2457E6; color: #fff;
    font-size: 14.5px; font-weight: 700;
    cursor: pointer;
    transition: background 0.18s, transform 0.18s, box-shadow 0.18s;
    box-shadow: 0 10px 28px rgba(36,87,230,0.26);
    display: flex; align-items: center; justify-content: center;
    gap: 8px;
}

.auth-btn svg {
    width: 16px; height: 16px;
    stroke: currentColor; stroke-width: 2.4;
    fill: none; stroke-linecap: round; stroke-linejoin: round;
}

.auth-btn:hover:not(:disabled) {
    background: #1D4ED8;
    transform: translateY(-1px);
    box-shadow: 0 14px 34px rgba(36,87,230,0.34);
}

.auth-btn:disabled { opacity: 0.55; cursor: not-allowed; }

/* Loading state */
.auth-spin {
    display: none; width: 16px; height: 16px;
    border: 2.5px solid rgba(255,255,255,0.35);
    border-top-color: #fff; border-radius: 50%;
    animation: authSpin 0.65s linear infinite;
}

@keyframes authSpin { to { transform: rotate(360deg); } }

.auth-btn.loading .auth-btn-label { display: none; }
.auth-btn.loading .auth-spin      { display: block; }

/* Note */
.auth-note {
    display: flex; align-items: flex-start; gap: 9px;
    margin-top: 14px; padding: 11px 13px;
    border-radius: 11px;
    background: rgba(36,87,230,0.06);
    border: 1px solid rgba(36,87,230,0.15);
    color: rgba(11,16,32,0.55);
    font-size: 12.5px; line-height: 1.5; font-weight: 500;
}

.auth-note svg {
    flex-shrink: 0; width: 14px; height: 14px;
    stroke: #2457E6; stroke-width: 2.2;
    fill: none; stroke-linecap: round; stroke-linejoin: round;
    margin-top: 1px;
}

/* Resend */
.auth-resend {
    margin-top: 14px; text-align: center;
    font-size: 13px; font-weight: 600;
    color: rgba(11,16,32,0.50);
}

.auth-resend-btn {
    background: none; border: none; padding: 0;
    color: #2457E6; font-size: 13px; font-weight: 700;
    cursor: pointer; text-decoration: underline;
    text-underline-offset: 2px; transition: color 0.15s;
}

.auth-resend-btn:hover:not(:disabled) { color: #0B1020; }
.auth-resend-btn:disabled { color: rgba(11,16,32,0.38); cursor: not-allowed; text-decoration: none; }

.auth-footer-link {
    margin-top: 16px; text-align: center;
    font-size: 13px; font-weight: 600;
    color: rgba(11,16,32,0.50);
}

.auth-footer-link a { color: #2457E6; text-decoration: none; font-weight: 700; }
.auth-footer-link a:hover { text-decoration: underline; }

/* Responsive */
@media (max-width: 900px) {
    .auth-page { grid-template-columns: 1fr; }
    .auth-left { display: none; }
    .auth-right {
        padding: 44px 32px;
        min-height: calc(100vh - 74px);
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .auth-right { padding: 32px 18px; }
    .auth-right h2 { font-size: 22px; }
    .auth-otp-box { height: 50px; font-size: 20px; border-radius: 10px; }
}
</style>

<div class="auth-page" x-data="{ timer: 300, formattedTimer: '05:00', expired: false }" x-init="
    setInterval(() => {
        if (timer > 0) {
            timer--;
            let mins = Math.floor(timer / 60);
            let secs = timer % 60;
            formattedTimer = (mins < 10 ? '0' : '') + mins + ':' + (secs < 10 ? '0' : '') + secs;
        } else {
            expired = true;
            formattedTimer = 'Expired';
        }
    }, 1000);
">
    <!-- LEFT PANEL -->
    <div class="auth-left">
        <div class="auth-left-inner">
            <span class="auth-left-badge">OTP Verification</span>
            <h1>Verify your <span>mobile</span></h1>
            <p>Enter the 6-digit code we sent to your registered mobile number to continue.</p>

            <div class="auth-left-steps">
                @if ($purpose === 'forgot_password')
                    <div class="auth-step">
                        <span class="auth-step-num done">✓</span>
                        <span class="auth-step-text">Mobile number entered</span>
                    </div>
                    <div class="auth-step">
                        <span class="auth-step-num active">2</span>
                        <span class="auth-step-text">Enter OTP sent to your phone</span>
                    </div>
                    <div class="auth-step">
                        <span class="auth-step-num">3</span>
                        <span class="auth-step-text">Set your new password</span>
                    </div>
                @else
                    <div class="auth-step">
                        <span class="auth-step-num done">✓</span>
                        <span class="auth-step-text">Account details submitted</span>
                    </div>
                    <div class="auth-step">
                        <span class="auth-step-num active">2</span>
                        <span class="auth-step-text">Verify mobile OTP</span>
                    </div>
                    <div class="auth-step">
                        <span class="auth-step-num">3</span>
                        <span class="auth-step-text">Access your dashboard</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="auth-right">
        <span class="auth-eyebrow">OTP Verification</span>
        <h2>Verify your mobile</h2>
        <p class="auth-desc">
            Enter the 6-digit OTP sent to
            <span class="auth-mobile-chip">{{ $mobile }}</span>
        </p>

        <div class="auth-divider"></div>

        <!-- Success/Error Alerts -->
        @if (session('success'))
            <div class="auth-alert success" role="alert">
                <svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="auth-alert danger" role="alert">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->has('otp'))
            <div class="auth-alert danger" role="alert">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                <span>{{ $errors->first('otp') }}</span>
            </div>
        @endif

        <!-- Debug OTP (from session if stored in OtpService) -->
        @if ((config('app.debug') || app()->environment('local')) && session('last_debug_otp'))
            <div class="auth-alert info" role="alert">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                <span>Debug OTP: <strong>{{ session('last_debug_otp') }}</strong></span>
            </div>
        @endif

        <!-- Countdown -->
        <div class="auth-countdown" :style="expired ? 'background:rgba(220,38,38,0.07);border-color:rgba(220,38,38,0.25);color:#991B1B;' : ''">
            <span class="auth-countdown-left">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                OTP expires in <span x-text="formattedTimer">05:00</span>
            </span>
            <span style="font-size:11.5px;opacity:0.78;font-weight: 600;">Request new if expired</span>
        </div>

        <!-- OTP Form -->
        <form wire:submit="verify" id="voForm" novalidate autocomplete="off">
            <input type="hidden" wire:model="otp" id="voOtpVal">

            <!-- 6 individual OTP boxes -->
            <div class="auth-otp-row" id="voOtpRow">
                @for ($i = 0; $i < 6; $i++)
                    <input
                        type="tel"
                        class="auth-otp-box"
                        maxlength="1"
                        inputmode="numeric"
                        pattern="[0-9]"
                        data-idx="{{ $i }}"
                        aria-label="OTP digit {{ $i + 1 }}"
                        autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                    >
                @endfor
            </div>

            <button class="auth-btn" type="submit" id="voSubmit" :disabled="expired" wire:loading.attr="disabled">
                <span class="auth-btn-label" style="display:inline-flex;align-items:center;gap:8px;" wire:loading.remove wire:target="verify">
                    <svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
                    Verify OTP
                </span>
                <span class="auth-spin" wire:loading wire:target="verify" style="display:block;"></span>
            </button>
        </form>

        <!-- Resend -->
        <form wire:submit.prevent="resend" id="voResendForm" class="mt-4">
            <p class="auth-resend">
                Didn't receive it?
                <button type="submit" class="auth-resend-btn" id="voResendBtn" wire:loading.attr="disabled">Resend OTP</button>
            </p>
        </form>

        <div class="auth-note">
            <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            OTP is valid for 5 minutes only. Never share it with anyone.
        </div>

        <p class="auth-footer-link">
            @if ($purpose === 'forgot_password')
                <a href="{{ route('password.request') }}" wire:navigate>← Start over</a>
            @else
                <a href="{{ route('register') }}" wire:navigate>← Change mobile</a>
            @endif
        </p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var otpLen  = 6;
    var boxes   = Array.from(document.querySelectorAll('.auth-otp-row .auth-otp-box'));
    var hidden  = document.getElementById('voOtpVal');
    var submit  = document.getElementById('voSubmit');
    var rsBtn   = document.getElementById('voResendBtn');
    var rsForm  = document.getElementById('voResendForm');

    if (!boxes.length || !hidden) return;

    function sync() {
        var val = boxes.map(function(b){ return b.value; }).join('');
        hidden.value = val;
        // Dispatch 'input' event so Livewire binds it to the $otp property
        hidden.dispatchEvent(new Event('input'));
        
        if (submit) {
            submit.disabled = (val.length !== otpLen);
        }
        
        boxes.forEach(function(b){
            b.classList.toggle('filled', b.value.length === 1);
        });
    }

    boxes.forEach(function(box, idx) {
        box.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !box.value && idx > 0) {
                boxes[idx-1].value = '';
                boxes[idx-1].focus();
                sync();
            }
            if (e.key === 'ArrowLeft'  && idx > 0)          boxes[idx-1].focus();
            if (e.key === 'ArrowRight' && idx < otpLen-1)   boxes[idx+1].focus();
        });

        box.addEventListener('input', function() {
            var d = box.value.replace(/\D/g,'').slice(-1);
            box.value = d;
            if (d && idx < otpLen-1) boxes[idx+1].focus();
            sync();
        });

        box.addEventListener('paste', function(e) {
            e.preventDefault();
            var p = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g,'');
            p.split('').forEach(function(c, i){
                if (i < otpLen && boxes[i]) boxes[i].value = c;
            });
            sync();
            var focusIdx = Math.min(p.length, otpLen-1);
            if (boxes[focusIdx]) boxes[focusIdx].focus();
        });
    });

    if (boxes[0]) boxes[0].focus();

    // Resend button cooldown
    if (rsBtn && rsForm) {
        rsForm.addEventListener('submit', function () {
            var orig = rsBtn.textContent;
            rsBtn.disabled = true;
            var cd = 60;
            var t2 = setInterval(function () {
                cd--;
                rsBtn.textContent = 'Resend in ' + cd + 's';
                if (cd <= 0) {
                    clearInterval(t2);
                    rsBtn.disabled = false;
                    rsBtn.textContent = orig;
                }
            }, 1000);
        });
    }
});
</script>
</div>
