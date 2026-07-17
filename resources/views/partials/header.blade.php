@php
    $user = auth()->user();
    $logoUrl = asset('logo/logo1.png');
    $hasLogo = file_exists(public_path('logo/logo1.png')) || file_exists(public_path('logo/logo.png'));
    if (!file_exists(public_path('logo/logo1.png')) && file_exists(public_path('logo/logo.png'))) {
        $logoUrl = asset('logo/logo.png');
    }
@endphp

<style>
    :root {
        --puc-header-black: #0B1020;
        --puc-header-blue: #2457E6;
        --puc-header-blue-dark: #1E49C9;
        --puc-header-white: #FFFFFF;
        --puc-header-muted: #526078;
        --puc-header-border: #E6EBF3;
        --puc-header-soft: #F7FAFF;
        --puc-header-shadow: 0 10px 28px rgba(11, 16, 32, 0.08);
    }

    .site-header {
        position: sticky;
        top: 0;
        z-index: 9999;
        width: 100%;
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
        border-bottom: 1px solid var(--puc-header-border);
        box-shadow: 0 6px 20px rgba(11, 16, 32, 0.045);
    }

    .site-header .container {
        max-width: 1240px;
    }

    .site-header .nav-wrap {
        min-height: 74px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 28px;
        position: relative;
    }

    .brand,
    .brand-with-logo {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        text-decoration: none !important;
        color: var(--puc-header-black);
        flex: 0 0 auto;
        min-width: 245px;
    }

    .brand-logo-shell {
        width: 46px;
        height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: var(--puc-header-white);
        border: 1px solid var(--puc-header-border);
        box-shadow: 0 8px 22px rgba(11, 16, 32, 0.07);
        overflow: hidden;
        flex-shrink: 0;
    }

    .brand-logo {
        display: block;
        width: auto;
        height: 34px;
        max-width: 36px;
        object-fit: contain;
        object-position: center;
    }

    .brand-fallback-mark {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--puc-header-blue);
        font-size: 18px;
        font-weight: 700;
        background: var(--puc-header-white);
        border: 1px solid var(--puc-header-border);
        box-shadow: 0 8px 22px rgba(11, 16, 32, 0.07);
        flex-shrink: 0;
    }

    .brand-text {
        display: inline-flex;
        flex-direction: column;
        gap: 3px;
        min-width: 0;
    }

    .brand-name {
        color: var(--puc-header-black);
        font-size: 15px;
        line-height: 1;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .brand-subtitle {
        color: var(--puc-header-muted);
        font-size: 13px;
        line-height: 1.1;
        font-weight: 500;
        white-space: nowrap;
    }

    .main-nav {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 22px;
        margin-left: auto;
    }

    .main-nav a {
        height: 42px;
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 2px;
        border-radius: 10px;
        color: var(--puc-header-muted);
        text-decoration: none !important;
        font-weight: 600;
        font-size: 15px;
        line-height: 1;
        letter-spacing: -0.01em;
        transition: 0.18s ease;
        white-space: nowrap;
        border: 1px solid transparent;
        background: transparent;
    }

    .main-nav a:hover,
    .main-nav a.active {
        color: var(--puc-header-black);
    }

    .main-nav a.active {
        font-weight: 700;
    }

    .main-nav .nav-login {
        height: 42px;
        min-height: 42px;
        padding: 0 18px;
        color: var(--puc-header-black);
        background: var(--puc-header-white);
        border: 1px solid var(--puc-header-border);
        box-shadow: 0 8px 20px rgba(11, 16, 32, 0.055);
        border-radius: 12px;
        font-weight: 600;
    }

    .main-nav .nav-login:hover,
    .main-nav .nav-login.active {
        color: var(--puc-header-black);
        background: var(--puc-header-soft);
        border-color: rgba(36, 87, 230, 0.20);
        box-shadow: 0 10px 24px rgba(11, 16, 32, 0.075);
    }

    .main-nav .btn.btn-primary,
    .main-nav a.btn.btn-primary {
        height: 42px;
        min-height: 42px;
        padding: 0 20px;
        color: #FFFFFF !important;
        background: var(--puc-header-blue) !important;
        border: 1px solid var(--puc-header-blue) !important;
        border-radius: 12px;
        box-shadow: 0 10px 24px rgba(36, 87, 230, 0.22);
        font-size: 15px;
        font-weight: 700;
    }

    .main-nav .btn.btn-primary:hover,
    .main-nav a.btn.btn-primary:hover {
        color: #FFFFFF !important;
        background: var(--puc-header-blue-dark) !important;
        border-color: var(--puc-header-blue-dark) !important;
        box-shadow: 0 12px 28px rgba(36, 87, 230, 0.28);
        transform: translateY(-1px);
    }

    .nav-toggle {
        display: none;
        width: 44px;
        height: 44px;
        border: 1px solid var(--puc-header-border);
        border-radius: 12px;
        background: var(--puc-header-white);
        color: var(--puc-header-blue);
        font-size: 22px;
        font-weight: 700;
        line-height: 1;
        cursor: pointer;
        box-shadow: 0 8px 20px rgba(11, 16, 32, 0.06);
        transition: 0.18s ease;
    }

    .nav-toggle:hover {
        background: var(--puc-header-soft);
        border-color: rgba(36, 87, 230, 0.20);
    }

    @media (max-width: 1100px) {
        .site-header .nav-wrap {
            gap: 18px;
        }

        .brand,
        .brand-with-logo {
            min-width: 210px;
        }

        .main-nav {
            gap: 14px;
        }

        .main-nav a {
            font-size: 14px;
        }

        .main-nav .nav-login,
        .main-nav .btn.btn-primary,
        .main-nav a.btn.btn-primary {
            padding-left: 15px;
            padding-right: 15px;
        }
    }

    @media (max-width: 820px) {
        .site-header .nav-wrap {
            min-height: 68px;
        }

        .brand,
        .brand-with-logo {
            min-width: 0;
        }

        .brand-logo-shell,
        .brand-fallback-mark {
            width: 42px;
            height: 42px;
            border-radius: 12px;
        }

        .brand-logo {
            height: 31px;
            max-width: 33px;
        }

        .brand-name {
            font-size: 13px;
        }

        .brand-subtitle {
            font-size: 12px;
        }

        .nav-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
        }

        .main-nav {
            position: absolute;
            top: calc(100% + 10px);
            left: 0;
            right: 0;
            height: auto;
            display: none;
            flex-direction: column;
            align-items: stretch;
            gap: 8px;
            padding: 12px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.98);
            border: 1px solid var(--puc-header-border);
            box-shadow: var(--puc-header-shadow);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .main-nav.is-open {
            display: flex;
            animation: pucNavDrop 0.20s ease both;
        }

        @keyframes pucNavDrop {
            from {
                opacity: 0;
                transform: translateY(-6px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .main-nav a,
        .main-nav .nav-login,
        .main-nav .btn.btn-primary,
        .main-nav a.btn.btn-primary {
            width: 100%;
            height: 46px;
            min-height: 46px;
            justify-content: flex-start;
            padding: 0 14px;
            border-radius: 12px;
        }

        .main-nav a {
            border: 1px solid var(--puc-header-border);
            background: var(--puc-header-white);
        }

        .main-nav .btn.btn-primary,
        .main-nav a.btn.btn-primary {
            justify-content: center;
        }
    }

    @media (max-width: 460px) {
        .site-header .nav-wrap {
            min-height: 64px;
        }

        .brand-logo-shell,
        .brand-fallback-mark {
            width: 40px;
            height: 40px;
            border-radius: 12px;
        }

        .brand-logo {
            height: 29px;
            max-width: 31px;
        }

        .brand-name {
            font-size: 12px;
            letter-spacing: 0.06em;
        }

        .brand-subtitle {
            display: none;
        }

        .nav-toggle {
            width: 40px;
            height: 40px;
            border-radius: 12px;
        }
    }
</style>

<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand brand-with-logo" href="{{ url('/') }}" aria-label="Drive Cue Home">
            @if ($hasLogo)
                <span class="brand-logo-shell">
                    <img
                        src="{{ $logoUrl }}"
                        alt="Drive Cue"
                        class="brand-logo"
                        loading="eager"
                        decoding="async"
                    >
                </span>
            @else
                <span class="brand-fallback-mark">P</span>
            @endif

            <span class="brand-text">
                <span class="brand-name">Drive Cue</span>
                <span class="brand-subtitle">Renewal Platform</span>
            </span>
        </a>

        <button class="nav-toggle" data-nav-toggle aria-label="Open Menu" aria-expanded="false">☰</button>

        <nav class="main-nav" data-main-nav>
            @if (!$user)
                <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">Home</a>
                <a href="{{ url('/pricing') }}" class="{{ request()->is('pricing') ? 'active' : '' }}">Pricing</a>
                <a href="{{ url('/contact') }}" class="{{ request()->is('contact') ? 'active' : '' }}">Contact</a>
                <a href="{{ route('login') }}" class="nav-login {{ request()->routeIs('login') ? 'active' : '' }}">Login</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Start Free</a>
            @elseif ($user->isSuperAdmin())
                <a href="{{ url('/admin') }}" class="active">Admin Panel</a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="nav-login border-0 bg-transparent cursor-pointer">Logout</button>
                </form>
            @else
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
                <a href="{{ url('/customers') }}" class="{{ request()->is('customers*') ? 'active' : '' }}">Customers</a>
                <a href="{{ url('/message-logs') }}" class="{{ request()->is('message-logs*') ? 'active' : '' }}">Message Logs</a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="nav-login border-0 bg-transparent cursor-pointer">Logout</button>
                </form>
            @endif
        </nav>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var toggle = document.querySelector('[data-nav-toggle]');
        var nav = document.querySelector('[data-main-nav]');

        if (!toggle || !nav) return;

        toggle.addEventListener('click', function () {
            var isOpen = nav.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            toggle.textContent = isOpen ? '×' : '☰';
        });

        document.addEventListener('click', function (event) {
            if (!nav.classList.contains('is-open')) return;

            var clickedInsideNav = nav.contains(event.target);
            var clickedToggle = toggle.contains(event.target);

            if (!clickedInsideNav && !clickedToggle) {
                nav.classList.remove('is-open');
                toggle.setAttribute('aria-expanded', 'false');
                toggle.textContent = '☰';
            }
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth > 820 && nav.classList.contains('is-open')) {
                nav.classList.remove('is-open');
                toggle.setAttribute('aria-expanded', 'false');
                toggle.textContent = '☰';
            }
        });
    });
</script>
