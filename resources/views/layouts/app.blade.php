<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'PUC Setu — Smart PUC Renewal Management Software')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
    <!-- Global Page Loader Overlay -->
    <div id="global-page-loader" style="position: fixed; inset: 0; background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(4px); display: none; align-items: center; justify-content: center; z-index: 99999; transition: opacity 0.22s ease;">
        <div style="display: flex; flex-direction: column; align-items: center; gap: 14px;">
            <div style="width: 48px; height: 48px; border: 4px solid #E2E8F0; border-top-color: #2563EB; border-radius: 50%; animation: spin-loader 0.8s linear infinite;"></div>
            <span style="font-size: 14px; font-weight: 700; color: #0F172A; font-family: inherit;">Loading...</span>
        </div>
    </div>
    <style>
        @keyframes spin-loader {
            to { transform: rotate(360deg); }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var loader = document.getElementById('global-page-loader');

            // Show loader on page unload / navigation
            window.addEventListener('beforeunload', function () {
                if (loader) loader.style.display = 'flex';
            });

            // Intercept form submissions
            document.querySelectorAll('form').forEach(function (form) {
                form.addEventListener('submit', function () {
                    form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<span style="display:inline-flex; align-items:center; gap:6px;">⏳ Processing...</span>';
                    });
                    if (loader) loader.style.display = 'flex';
                });
            });

            // Intercept general button clicks
            document.querySelectorAll('button:not([type="button"]), .ds-wa-btn, .btn-send').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (btn.disabled) return;
                    if (btn.type === 'submit' || btn.closest('form')) return;
                    if (btn.id === 'dsOpenModal' || btn.id === 'dsCloseModal' || btn.classList.contains('modal-tab') || btn.classList.contains('filter-btn') || btn.classList.contains('modal-close') || btn.id === 'dsCancelBtn') {
                        return;
                    }
                    btn.disabled = true;
                    if (loader) loader.style.display = 'flex';
                });
            });
        });

        // Livewire hook
        document.addEventListener('livewire:init', function () {
            Livewire.hook('request', function ({ fail, respond, succeed }) {
                var loader = document.getElementById('global-page-loader');
                if (loader) loader.style.display = 'flex';

                succeed(function () {
                    if (loader) loader.style.display = 'none';
                });

                fail(function () {
                    if (loader) loader.style.display = 'none';
                });
            });
        });
    </script>

    @include('partials.header')

    @if (isset($slot))
        {{ $slot }}
    @else
        @yield('content')
    @endif

    @include('partials.footer')
    @stack('scripts')
</body>
</html>
