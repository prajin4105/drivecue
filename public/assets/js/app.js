/**
 * PUC Setu — Premium Frontend Interactions v3.0
 * Dependency-free. Hostinger shared hosting safe.
 * No business logic changes — UI enhancements only.
 */
document.addEventListener('DOMContentLoaded', function () {

    /* ── 1. Mobile Navigation Toggle ── */
    var navToggle = document.querySelector('[data-nav-toggle]');
    var mainNav   = document.querySelector('[data-main-nav]');
    if (navToggle && mainNav) {
        navToggle.addEventListener('click', function () {
            var isOpen = mainNav.classList.toggle('open');
            mainNav.classList.toggle('is-open', isOpen);
            navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            navToggle.textContent = isOpen ? '×' : '☰';
        });
        document.addEventListener('click', function (e) {
            if (!mainNav.classList.contains('open')) return;
            if (mainNav.contains(e.target) || navToggle.contains(e.target)) return;
            mainNav.classList.remove('open', 'is-open');
            navToggle.setAttribute('aria-expanded', 'false');
            navToggle.textContent = '☰';
        });
    }

    /* ── 2. Confirm Dialogs ── */
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!confirm(el.getAttribute('data-confirm') || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });

    /* ── 3. Vehicle Number Auto-Uppercase ── */
    document.querySelectorAll('[data-vehicle-input]').forEach(function (input) {
        input.addEventListener('input', function () {
            input.value = input.value.toUpperCase().replace(/[^A-Z0-9 ]/g, '');
        });
    });

    /* ── 4. Scroll Reveal Animation ── */
    var revealEls = [];
    document.querySelectorAll(
        '.card, .panel, .stat-card, .price-card, .hp-feature-card, ' +
        '.hp-step, .hp-proof-card, .premium-stat, .dashboard-panel, ' +
        '.report-stat, .logs-summary-card, .profile-form-card'
    ).forEach(function (el, i) {
        if (i > 30) return;
        el.classList.add('reveal');
        revealEls.push(el);
    });

    if ('IntersectionObserver' in window) {
        var revealObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var el = entry.target;
                    var delay = (parseInt(el.getAttribute('data-reveal-delay') || 0));
                    setTimeout(function () { el.classList.add('in-view'); }, delay);
                    revealObserver.unobserve(el);
                }
            });
        }, { threshold: 0.07, rootMargin: '0px 0px -40px 0px' });

        revealEls.forEach(function (el, i) {
            el.setAttribute('data-reveal-delay', Math.min(i % 4 * 80, 280));
            revealObserver.observe(el);
        });
    } else {
        revealEls.forEach(function (el) { el.classList.add('in-view'); });
    }

    /* ── 5. Animated Number Counters ── */
    function animateCount(el) {
        var raw = el.textContent.trim();
        var num = parseInt(raw.replace(/[^0-9]/g, ''), 10);
        if (isNaN(num) || num < 2) return;
        var suffix = raw.replace(/[0-9]/g, '');
        var duration = 900;
        var start = null;
        function step(ts) {
            if (!start) start = ts;
            var pct = Math.min((ts - start) / duration, 1);
            var ease = 1 - Math.pow(1 - pct, 3); // ease-out cubic
            el.textContent = Math.round(ease * num) + suffix;
            if (pct < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    if ('IntersectionObserver' in window) {
        var countObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.querySelectorAll(
                        '.premium-stat strong, .report-stat > strong, ' +
                        '.logs-summary-info strong, .stat-card strong'
                    ).forEach(animateCount);
                    countObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });

        document.querySelectorAll(
            '.premium-stat-grid, .report-stat-grid, .logs-summary-grid, .stats-grid'
        ).forEach(function (grid) { countObserver.observe(grid); });
    }

    /* ── 6. Bar Fill Animation ── */
    if ('IntersectionObserver' in window) {
        var barObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.querySelectorAll('.bar-fill, .usage-fill, .chart-modern-fill').forEach(function (bar) {
                        var target = bar.style.width || bar.getAttribute('data-width') || '0%';
                        bar.style.width = '0%';
                        setTimeout(function () { bar.style.width = target; }, 100);
                    });
                    barObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.2 });

        document.querySelectorAll('.chart-bars, .chart-modern, .usage-card').forEach(function (el) {
            el.querySelectorAll('.bar-fill, .usage-fill, .chart-modern-fill').forEach(function (bar) {
                bar.setAttribute('data-width', bar.style.width);
                bar.style.width = '0%';
            });
            barObserver.observe(el);
        });
    }

    /* ── 7. Password Toggle ── */
    document.querySelectorAll('[data-password-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id    = btn.getAttribute('data-password-toggle');
            var input = document.getElementById(id);
            if (!input) return;
            var hidden = input.type === 'password';
            input.type = hidden ? 'text' : 'password';
            btn.classList.toggle('is-visible', hidden);
            btn.setAttribute('aria-label', hidden ? 'Hide password' : 'Show password');
        });
    });

    /* ── 8. Table Row Click Ripple ── */
    document.querySelectorAll('tbody tr').forEach(function (row) {
        row.style.cursor = 'default';
    });

    /* ── 9. Flash Auto-Dismiss ── */
    document.querySelectorAll('.flash').forEach(function (flash) {
        setTimeout(function () {
            flash.style.transition = 'opacity 0.5s ease, transform 0.5s ease, max-height 0.5s ease';
            flash.style.opacity = '0';
            flash.style.transform = 'translateY(-6px)';
            setTimeout(function () { flash.remove(); }, 520);
        }, 5500);
    });

    /* ── 10. Active Nav Highlight ── */
    var current = window.location.pathname.split('/').pop() || 'index.php';
    document.querySelectorAll('.main-nav a, .sb-nav-item').forEach(function (link) {
        var href = (link.getAttribute('href') || '').split('?')[0].split('/').pop();
        if (href && href === current) link.classList.add('active');
    });

    /* ── 11. Smooth Button Press Feedback ── */
    document.querySelectorAll('.btn').forEach(function (btn) {
        btn.addEventListener('mousedown', function () {
            btn.style.transform = 'translateY(0) scale(0.98)';
        });
        btn.addEventListener('mouseup', function () {
            btn.style.transform = '';
        });
        btn.addEventListener('mouseleave', function () {
            btn.style.transform = '';
        });
    });

});
