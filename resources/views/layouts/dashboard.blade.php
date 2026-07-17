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
    @stack('styles')
</head>
<body>
    <!-- Global Page Loader Overlay -->
    <div id="global-page-loader" style="position: fixed; inset: 0; background: #ffffff; display: flex; align-items: center; justify-content: center; z-index: 99999; opacity: 1; visibility: visible; transition: opacity 0.3s ease, visibility 0.3s ease;">
        <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
            <div class="loader">
              <div class="truckWrapper">
                <div class="truckBody">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 198 93"
                    class="trucksvg"
                  >
                    <path
                      stroke-width="3"
                      stroke="#282828"
                      fill="#F83D3D"
                      d="M135 22.5H177.264C178.295 22.5 179.22 23.133 179.594 24.0939L192.33 56.8443C192.442 57.1332 192.5 57.4404 192.5 57.7504V89C192.5 90.3807 191.381 91.5 190 91.5H135C133.619 91.5 132.5 90.3807 132.5 89V25C132.5 23.6193 133.619 22.5 135 22.5Z"
                    ></path>
                    <path
                      stroke-width="3"
                      stroke="#282828"
                      fill="#7D7C7C"
                      d="M146 33.5H181.741C182.779 33.5 183.709 34.1415 184.078 35.112L190.538 52.112C191.16 53.748 189.951 55.5 188.201 55.5H146C144.619 55.5 143.5 54.3807 143.5 53V36C143.5 34.6193 144.619 33.5 146 33.5Z"
                    ></path>
                    <path
                      stroke-width="2"
                      stroke="#282828"
                      fill="#282828"
                      d="M150 65C150 65.39 149.763 65.8656 149.127 66.2893C148.499 66.7083 147.573 67 146.5 67C145.427 67 144.501 66.7083 143.873 66.2893C143.237 65.8656 143 65.39 143 65C143 64.61 143.237 64.1344 143.873 63.7107C144.501 63.2917 145.427 63 146.5 63C147.573 63 148.499 63.2917 149.127 63.7107C149.763 64.1344 150 64.61 150 65Z"
                    ></path>
                    <rect
                      stroke-width="2"
                      stroke="#282828"
                      fill="#FFFCAB"
                      rx="1"
                      height="7"
                      width="5"
                      y="63"
                      x="187"
                    ></rect>
                    <rect
                      stroke-width="2"
                      stroke="#282828"
                      fill="#282828"
                      rx="1"
                      height="11"
                      width="4"
                      y="81"
                      x="193"
                    ></rect>
                    <rect
                      stroke-width="3"
                      stroke="#282828"
                      fill="#DFDFDF"
                      rx="2.5"
                      height="90"
                      width="121"
                      y="1.5"
                      x="6.5"
                    ></rect>
                    <rect
                      stroke-width="2"
                      stroke="#282828"
                      fill="#DFDFDF"
                      rx="2"
                      height="4"
                      width="6"
                      y="84"
                      x="1"
                    ></rect>
                  </svg>
                </div>
                <div class="truckTires">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 30 30"
                    class="tiresvg"
                  >
                    <circle
                      stroke-width="3"
                      stroke="#282828"
                      fill="#282828"
                      r="13.5"
                      cy="15"
                      cx="15"
                    ></circle>
                    <circle fill="#DFDFDF" r="7" cy="15" cx="15"></circle>
                  </svg>
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 30 30"
                    class="tiresvg"
                  >
                    <circle
                      stroke-width="3"
                      stroke="#282828"
                      fill="#282828"
                      r="13.5"
                      cy="15"
                      cx="15"
                    ></circle>
                    <circle fill="#DFDFDF" r="7" cy="15" cx="15"></circle>
                  </svg>
                </div>
                <div class="road"></div>
                <svg
                  xml:space="preserve"
                  viewBox="0 0 453.459 453.459"
                  xmlns:xlink="http://www.w3.org/1999/xlink"
                  xmlns="http://www.w3.org/2000/svg"
                  id="Capa_1"
                  version="1.1"
                  fill="#000000"
                  class="lampPost"
                >
                  <path
                    d="M252.882,0c-37.781,0-68.686,29.953-70.245,67.358h-6.917v8.954c-26.109,2.163-45.463,10.011-45.463,19.366h9.993
            c-1.65,5.146-2.507,10.54-2.507,16.017c0,28.956,23.558,52.514,52.514,52.514c28.956,0,52.514-23.558,52.514-52.514
            c0-5.478-0.856-10.872-2.506-16.017h9.992c0-9.354-19.352-17.204-45.463-19.366v-8.954h-6.149C200.189,38.779,223.924,16,252.882,16
            c29.952,0,54.32,24.368,54.32,54.32c0,28.774-11.078,37.009-25.105,47.437c-17.444,12.968-37.216,27.667-37.216,78.884v113.914
            h-0.797c-5.068,0-9.174,4.108-9.174,9.177c0,2.844,1.293,5.383,3.321,7.066c-3.432,27.933-26.851,95.744-8.226,115.459v11.202h45.75
            v-11.202c18.625-19.715-4.794-87.527-8.227-115.459c2.029-1.683,3.322-4.223,3.322-7.066c0-5.068-4.107-9.177-9.176-9.177h-0.795
            V196.641c0-43.174,14.942-54.283,30.762-66.043c14.793-10.997,31.559-23.461,31.559-60.277C323.202,31.545,291.656,0,252.882,0z
            M232.77,111.694c0,23.442-19.071,42.514-42.514,42.514c-23.442,0-42.514-19.072-42.514-42.514c0-5.531,1.078-10.957,3.141-16.017
            h78.747C231.693,100.736,232.77,106.162,232.77,111.694z"
                  ></path>
                </svg>
              </div>
            </div>
            <span style="font-size: 13.5px; font-weight: 800; color: #1E293B; font-family: inherit; margin-top: -6px; letter-spacing: -0.01em;">Processing...</span>
        </div>
    </div>
    <style>
        .loader {
          width: fit-content;
          height: fit-content;
          display: flex;
          align-items: center;
          justify-content: center;
        }
        .truckWrapper {
          width: 200px;
          height: 100px;
          display: flex;
          flex-direction: column;
          position: relative;
          align-items: center;
          justify-content: flex-end;
          overflow-x: hidden;
        }
        .truckBody {
          width: 130px;
          height: fit-content;
          margin-bottom: 6px;
          animation: motion 1s linear infinite;
        }
        @keyframes motion {
          0% { transform: translateY(0px); }
          50% { transform: translateY(3px); }
          100% { transform: translateY(0px); }
        }
        .truckTires {
          width: 130px;
          height: fit-content;
          display: flex;
          align-items: center;
          justify-content: space-between;
          padding: 0px 10px 0px 15px;
          position: absolute;
          bottom: 0;
        }
        .truckTires svg {
          width: 24px;
        }
        .road {
          width: 100%;
          height: 1.5px;
          background-color: #282828;
          position: relative;
          bottom: 0;
          align-self: flex-end;
          border-radius: 3px;
        }
        .road::before {
          content: "";
          position: absolute;
          width: 20px;
          height: 100%;
          background-color: #282828;
          right: -50%;
          border-radius: 3px;
          animation: roadAnimation 1.4s linear infinite;
          border-left: 10px solid white;
        }
        .road::after {
          content: "";
          position: absolute;
          width: 10px;
          height: 100%;
          background-color: #282828;
          right: -65%;
          border-radius: 3px;
          animation: roadAnimation 1.4s linear infinite;
          border-left: 4px solid white;
        }
        .lampPost {
          position: absolute;
          bottom: 0;
          right: -90%;
          height: 90px;
          animation: roadAnimation 1.4s linear infinite;
        }
        @keyframes roadAnimation {
          0% { transform: translateX(0px); }
          100% { transform: translateX(-350px); }
        }
    </style>
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

<header class="psh-header" id="pucDashboardHeader">
    <div class="psh-brand-col">
        <button class="psh-menu-btn" type="button" data-sidebar-open aria-label="Open sidebar">
            <svg viewBox="0 0 24 24"><path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h16"/></svg>
        </button>

        @php
            $profileImage = $user->profile_image ? asset($user->profile_image) : null;
            $centerName = $user->center_name ?: 'Drive Cue';
            $centerInitial = strtoupper(substr($centerName ?: 'C', 0, 1));
        @endphp
        <a class="psh-brand" href="{{ route('dashboard') }}" aria-label="{{ $centerName }}">
            <span class="psh-logo-box" style="width: 40px; height: 40px; border-radius: 12px; background: var(--blue-lt); border: 1px solid var(--blue-bd); display: flex; align-items: center; justify-content: center; overflow: hidden; font-weight: 700; font-size: 18px; color: var(--blue);">
                <img id="pshBrandLogoImg" src="{{ $profileImage }}" alt="Logo" onerror="this.style.display='none'; document.getElementById('pshBrandInitial').style.display='flex';" style="{{ $profileImage ? 'width: 100%; height: 100%; object-fit: cover;' : 'display:none;' }}">
                <span id="pshBrandInitial" style="{{ $profileImage ? 'display:none;' : 'display:flex; width: 100%; height: 100%; align-items: center; justify-content: center;' }}">{{ $centerInitial }}</span>
            </span>
            <span class="psh-brand-text" style="display: flex; flex-direction: column; gap: 2px; min-width: 0;">
                <span class="psh-brand-name" style="font-size: 14px; font-weight: 700; color: var(--black); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $centerName }}</span>
                <span class="psh-brand-sub" style="font-size: 11px; font-weight: 500; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Renewal Center</span>
            </span>
        </a>
    </div>

    <div class="psh-page-col">
        <div class="psh-title-row">
            <span class="psh-dot" aria-hidden="true"></span>
            <h1 id="pshPageTitle">@yield('page-title', 'Dashboard')</h1>
        </div>
        <p id="pshPageSub">@yield('page-subtitle', 'Daily PUC renewal overview, reminders and reports.')</p>
    </div>

    <div class="psh-actions-col">
        <div class="psh-msg-pill" title="{{ $messageLimitTitle }}">
            <span class="psh-msg-icon">
                <svg viewBox="0 0 24 24"><path d="M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/><path d="M8 9h8"/><path d="M8 13h5"/></svg>
            </span>
            <span class="psh-msg-text">
                <b>Messages</b>
                <small>{{ $messageLimitText }}</small>
            </span>
        </div>

        <!-- Notification Bell -->
        <div class="psh-notify-wrap" id="pshNotifyWrap">
            <button class="psh-icon-btn" type="button" id="pshNotifyBtn" aria-label="Notifications" aria-expanded="false">
                <svg viewBox="0 0 24 24"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                @if ($notificationCount > 0)
                    <span class="psh-badge">{{ min($notificationCount, 99) }}</span>
                @endif
            </button>

            <div class="psh-notify-panel" role="dialog" aria-label="Notifications">
                <div class="psh-notify-head">
                    <strong>Notifications</strong>
                    <span class="psh-notify-count">{{ $notificationCount }} active</span>
                </div>
                <div class="psh-notify-body">
                    <a href="{{ url('/customers?filter=expired') }}" class="psh-notify-item{{ $expiredCount > 0 ? ' is-urgent' : '' }}">
                        <span class="psh-notify-icon">
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                        </span>
                        <span>
                            <b>{{ $expiredCount }} expired records</b>
                            <small>Need immediate follow-up.</small>
                        </span>
                    </a>
                    <a href="{{ url('/customers?filter=expiring') }}" class="psh-notify-item">
                        <span class="psh-notify-icon">
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </span>
                        <span>
                            <b>{{ $expiringCount }} expiring soon</b>
                            <small>Certificates expiring within 7 days.</small>
                        </span>
                    </a>
                    <a href="{{ url('/message-logs') }}" class="psh-notify-item">
                        <span class="psh-notify-icon">
                            <svg viewBox="0 0 24 24"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                        </span>
                        <span>
                            <b>{{ $failedReminderCount }} failed reminders</b>
                            <small>Review WhatsApp / SMS logs.</small>
                        </span>
                    </a>
                </div>
            </div>
        </div>

        <a href="{{ url('/profile') }}" class="psh-user-pill" title="{{ $centerName }}">
            <span class="psh-avatar">{{ $avatarLetter }}</span>
            <span class="psh-user-text">
                <b>{{ $ownerName ?: 'Owner' }}</b>
                <small>{{ $planName }} · {{ $planDaysText }}</small>
            </span>
        </a>
    </div>
</header>

<aside class="psh-sidebar" id="pucSidebar" aria-label="Dashboard navigation">
    <div class="psh-sb-head">
        <div class="psh-sb-head-text">
            <span class="psh-sb-label">Navigation</span>
            <strong>Dashboard</strong>
        </div>
        <button class="psh-sb-close" id="sidebarCloseBtn" type="button" aria-label="Close sidebar">
            <svg viewBox="0 0 24 24"><path d="M18 6 6 18"/><path d="M6 6l12 12"/></svg>
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

        <a href="{{ url('/reports') }}" class="psh-sb-item {{ request()->is('reports*') ? 'active' : '' }}">
            <span class="psh-sb-icon">
                <svg viewBox="0 0 24 24"><path d="M18 20V10"/><path d="M12 20V4"/><path d="M6 20v-6"/></svg>
            </span>
            <span>Reports</span>
        </a>

        <a href="{{ url('/message-logs') }}" class="psh-sb-item {{ request()->is('message-logs*') ? 'active' : '' }}">
            <span class="psh-sb-icon">
                <svg viewBox="0 0 24 24"><path d="M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/></svg>
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

        <a href="{{ url('/pricing') }}" class="psh-sb-item {{ request()->is('pricing*') ? 'active' : '' }}">
            <span class="psh-sb-icon">
                <svg viewBox="0 0 24 24"><path d="m12 2 3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            </span>
            <span>Plan Details</span>
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
            <a href="{{ url('/pricing') }}" class="psh-plan-manage">Manage</a>
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
</body>
</html>
