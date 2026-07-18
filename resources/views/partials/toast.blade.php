@if(session('success') || session('error') || session('warning') || (isset($errors) && $errors->any()))
    <div id="global-toast-container" style="position: fixed; bottom: 24px; right: 24px; z-index: 99999; display: flex; flex-direction: column; gap: 12px; pointer-events: none;">
        @if(session('success'))
            <div class="global-toast success">
                <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="global-toast error">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if(session('warning'))
            <div class="global-toast warning">
                <svg viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                <span>{{ session('warning') }}</span>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            @foreach($errors->all() as $error)
                <div class="global-toast error">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                    <span>{{ $error }}</span>
                </div>
            @endforeach
        @endif
    </div>

    <style>
        .global-toast {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 18px;
            border-radius: 14px;
            font-size: .88rem;
            font-weight: 760;
            box-shadow: 0 12px 36px rgba(11,16,32,.12);
            max-width: 380px;
            opacity: 0;
            transform: translateY(16px);
            animation: slideInToast 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            pointer-events: auto;
        }
        .global-toast.success { background: #F0FDF4; border: 1.5px solid #BBF7D0; color: #166534; }
        .global-toast.error   { background: #FEF2F2; border: 1.5px solid #FECACA; color: #B91C1C; }
        .global-toast.warning { background: #FFFBEB; border: 1.5px solid #FDE68A; color: #92400E; }
        .global-toast svg { width: 20px; height: 20px; flex: 0 0 auto; stroke: currentColor; stroke-width: 2.2; fill: none; stroke-linecap: round; stroke-linejoin: round; }

        @keyframes slideInToast {
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeOutToast {
            to { opacity: 0; transform: translateY(8px); }
        }
    </style>

    <script>
        function initGlobalToasts() {
            var toasts = document.querySelectorAll('.global-toast');
            toasts.forEach(function(toast) {
                if (toast.dataset.initialized) return;
                toast.dataset.initialized = 'true';
                
                // Auto dismiss after 5 seconds
                setTimeout(function() {
                    toast.style.animation = 'fadeOutToast 0.3s ease forwards';
                    setTimeout(function() { toast.remove(); }, 300);
                }, 5000);
            });
        }
        
        document.addEventListener('DOMContentLoaded', initGlobalToasts);
        document.addEventListener('livewire:navigated', initGlobalToasts);
    </script>
@endif
