@php
    $waService = app(\App\Services\WhatsAppService::class);
    $quota = $waService->checkQuota(auth()->id());
    $whatsappUsed = $waService->countWhatsappUsed(auth()->id(), $quota['plan_name'] === 'Free Trial');
    $whatsappRemaining = $waService->getRemainingQuota(auth()->id());
    $whatsappTotalLimit = $quota['limit'];
    
    // Notifications count
    $expiredCount = \App\Models\VehicleRecord::where('user_id', auth()->id())->where('expiry_date', '<', date('Y-m-d'))->count();
    $expiringCount = \App\Models\VehicleRecord::where('user_id', auth()->id())->whereBetween('expiry_date', [date('Y-m-d'), date('Y-m-d', strtotime('+7 days'))])->count();
    $failedReminderCount = \App\Models\ReminderLog::where('user_id', auth()->id())->where('status', 'failed')->count();
    $notificationCount = $expiredCount + $expiringCount + $failedReminderCount;
    
    // Owner name and avatar
    $user = auth()->user();
    $ownerName = trim($user->first_name . ' ' . $user->last_name);
    if (empty($ownerName)) $ownerName = trim($user->center_name ?? 'Center Owner');
    $centerName = $user->center_name ?? 'Drive Cue';
    $avatarLetter = strtoupper(substr($ownerName ?: 'P', 0, 1));
    
    // Plan End Date text
    $sub = $waService->getActiveSubscription(auth()->id());
    $planName = $quota['plan_name'] ?: 'No Active Plan';
    $planDaysText = 'Expired';
    if ($sub) {
        if ($sub->payment_status === 'trial' || $sub->billing_cycle === 'lifetime') {
            $planDaysText = 'Trial · Lifetime';
        } else {
            $daysLeft = (int) \Carbon\Carbon::today()->diffInDays($sub->end_date, false);
            $planDaysText = $daysLeft >= 0 ? $daysLeft . ' days left' : 'Expired';
        }
    }
    
    if ($whatsappTotalLimit > 0) {
        $messageLimitText = number_format($whatsappRemaining) . ' left / ' . number_format($whatsappTotalLimit);
        $messageLimitTitle = 'WhatsApp: ' . number_format($whatsappUsed) . ' used of ' . number_format($whatsappTotalLimit);
    } else {
        $messageLimitText = 'No limit set';
        $messageLimitTitle = 'WhatsApp message limit not configured';
    }
@endphp

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Drive Cue — Renewal Management Platform')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard-shell.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    @stack('styles')
</head>
<body>
    @include('partials.loader')
    <script>
        (function() {
            var loader = document.getElementById('global-page-loader');
            var loadStart = Date.now();

            function showLoader() {
                if (loader) {
                    loader.style.opacity = '1';
                    loader.style.visibility = 'visible';
                }
            }

            function hideLoader() {
                if (loader) {
                    loader.style.opacity = '0';
                    loader.style.visibility = 'hidden';
                }
            }

            function hideLoaderWithDelay(startTime, minDelay) {
                var elapsed = Date.now() - startTime;
                var delay = Math.max(0, minDelay - elapsed);
                setTimeout(hideLoader, delay);
            }

            // On initial load, keep the loader visible for 2 seconds, then fade out smoothly
            hideLoaderWithDelay(loadStart, 2000);

            // Handle back-forward cache (bfcache) restoration
            window.addEventListener('pageshow', function (event) {
                if (event.persisted) {
                    hideLoader();
                }
            });

            // Show loader when unloading the page (e.g. clicking links or submitting forms)
            window.addEventListener('beforeunload', function () {
                showLoader();
            });

            // Intercept form submissions
            document.addEventListener('submit', function (e) {
                var form = e.target;
                if (form && form.tagName === 'FORM') {
                    form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<span style="display:inline-flex; align-items:center; gap:6px;">⏳ Processing...</span>';
                    });
                    showLoader();
                }
            });

            // Intercept general button clicks
            document.addEventListener('click', function (e) {
                var btn = e.target.closest('button:not([type="button"]), .ds-wa-btn, .btn-send');
                if (!btn) return;
                if (btn.disabled) return;
                if (btn.type === 'submit' || btn.closest('form')) return;
                if (['dsOpenModal', 'dsCloseModal'].includes(btn.id) || btn.classList.contains('modal-tab') || btn.classList.contains('filter-btn') || btn.classList.contains('modal-close') || btn.id === 'dsCancelBtn') {
                    return;
                }
                btn.disabled = true;
                showLoader();
            });

            // Intercept link clicks in the sidebar and other navigation areas
            document.addEventListener('click', function (e) {
                var anchor = e.target.closest('a');
                if (!anchor) return;

                if (anchor.href && 
                    anchor.href.indexOf(window.location.origin) === 0 && 
                    !anchor.hash && 
                    anchor.target !== '_blank' && 
                    !e.defaultPrevented &&
                    !anchor.closest('.modal-footer') &&
                    !anchor.classList.contains('modal-close') &&
                    !anchor.classList.contains('filter-btn')
                ) {
                    if (anchor.href.indexOf('javascript:') === -1) {
                        showLoader();
                    }
                }
            });

            // Livewire request hooks
            document.addEventListener('livewire:init', function () {
                Livewire.hook('request', function ({ fail, respond, succeed }) {
                    var requestStart = Date.now();
                    showLoader();

                    succeed(function () {
                        hideLoaderWithDelay(requestStart, 2000);
                    });

                    fail(function () {
                        hideLoaderWithDelay(requestStart, 2000);
                    });
                });
            });
        })();
    </script>


<aside class="psh-sidebar" id="pucSidebar" aria-label="Dashboard navigation">
    <div class="psh-sb-head">
        <div class="psh-sb-head-text">
            <span class="psh-sb-label">{{ $ownerName ?: 'Owner' }}</span>
            <strong style="font-size: 1.15rem; font-weight: 900; letter-spacing: -0.02em; color: var(--puc-blue);">{{ $centerName }}</strong>
        </div>
        <button class="psh-sb-close" id="sidebarCloseBtn" type="button" aria-label="Close sidebar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="M6 6l12 12"/></svg>
        </button>
    </div>

    <label class="psh-sb-search" aria-label="Search menu">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="search" id="pucSidebarSearch" placeholder="Search menu…" autocomplete="off">
    </label>

    <nav class="psh-sb-nav" role="navigation" aria-label="Dashboard navigation">
        <span class="psh-sb-section">Workspace</span>

        <a href="{{ route('dashboard') }}" class="psh-sb-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span class="psh-sb-icon">
                <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
            </span>
            <span>Dashboard</span>
        </a>

        <a href="{{ url('/customers') }}" class="psh-sb-item {{ request()->is('customers*') || request()->is('customer/*') ? 'active' : '' }}">
            <span class="psh-sb-icon">
                <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </span>
            <span>Vehicle Records</span>
        </a>

        <a href="{{ url('/reports') }}" class="psh-sb-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <span class="psh-sb-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
            </span>
            <span>Reports</span>
        </a>

        <a href="{{ route('whatsapp-logs.index') }}" class="psh-sb-item {{ request()->routeIs('whatsapp-logs.*') ? 'active' : '' }}">
            <span class="psh-sb-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </span>
            <span>WhatsApp Logs</span>
        </a>

        <span class="psh-sb-section psh-sb-section-gap">Account</span>

        <a href="{{ url('/profile') }}" class="psh-sb-item {{ request()->is('profile*') ? 'active' : '' }}">
            <span class="psh-sb-icon">
                <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </span>
            <span>Profile</span>
        </a>

        <a href="{{ route('billing.index') }}" class="psh-sb-item {{ request()->routeIs('billing.*') ? 'active' : '' }}">
            <span class="psh-sb-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
            </span>
            <span>Billing & Plans</span>
        </a>
    </nav>

    <div class="psh-sb-footer">
        <div class="psh-plan-chip">
            <span class="psh-plan-icon">
                <svg viewBox="0 0 24 24"><path d="m12 2 3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            </span>
            <span class="psh-plan-text">
                <b>{{ $planName }}</b>
                <small>{{ $planDaysText }}</small>
            </span>
            <a href="{{ route('billing.index') }}" class="psh-plan-manage">Manage</a>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="psh-logout-form">
            @csrf
            <button type="submit" class="psh-logout border-0 bg-transparent text-left w-full cursor-pointer flex items-center gap-2">
                <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                <span>Logout</span>
            </button>
        </form>
    </div>
</aside>

<div class="psh-overlay" id="sidebarOverlay"></div>

<main class="dashboard-layout">
    <div class="content">
        @yield('content')
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Menu toggle for mobile
    var openBtn = document.querySelector('[data-sidebar-open]');
    var closeBtn = document.getElementById('sidebarCloseBtn');
    var overlay = document.getElementById('sidebarOverlay');
    var sidebar = document.getElementById('pucSidebar');

    if (openBtn && sidebar) {
        openBtn.addEventListener('click', function () {
            sidebar.classList.add('is-open');
            if (overlay) overlay.classList.add('is-visible');
        });
    }

    if (closeBtn && sidebar) {
        closeBtn.addEventListener('click', function () {
            sidebar.classList.remove('is-open');
            if (overlay) overlay.classList.remove('is-visible');
        });
    }

    if (overlay && sidebar) {
        overlay.addEventListener('click', function () {
            sidebar.classList.remove('is-open');
            overlay.classList.remove('is-visible');
        });
    }

    // Notification dropdown toggle
    var notifyBtn = document.getElementById('pshNotifyBtn');
    var notifyWrap = document.getElementById('pshNotifyWrap');

    if (notifyBtn && notifyWrap) {
        notifyBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            var isOpen = notifyWrap.classList.toggle('is-open');
            notifyBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        document.addEventListener('click', function (e) {
            if (!notifyWrap.contains(e.target)) {
                notifyWrap.classList.remove('is-open');
                notifyBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // Sidebar search filtering
    var searchInp = document.getElementById('pucSidebarSearch');
    if (searchInp) {
        searchInp.addEventListener('input', function () {
            var q = this.value.toLowerCase().trim();
            document.querySelectorAll('.psh-sb-nav .psh-sb-item').forEach(function (item) {
                var txt = item.querySelector('span').textContent.toLowerCase();
                if (txt.indexOf(q) > -1) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
});
</script>

@stack('scripts')
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
@stack('footer-scripts')
@include('partials.toast')
</body>
</html>
