/**
 * PUC Setu — Razorpay checkout (dashboard + pricing)
 * Handles plan subscriptions and top-up purchases.
 */
(function (window) {
    'use strict';

    /* ── Helpers ──────────────────────────────────────────────── */

    function apiUrl(path) {
        var base = window.PUC_BASE || '';
        if (base && base.charAt(base.length - 1) !== '/') {
            base += '/';
        }
        return base + String(path || '').replace(/^\//, '');
    }

    function log() {
        var args = Array.prototype.slice.call(arguments);
        args.unshift('[PucPayment]');
        console.log.apply(console, args);
    }

    function warn() {
        var args = Array.prototype.slice.call(arguments);
        args.unshift('[PucPayment]');
        console.warn.apply(console, args);
    }

    function setButtonState(btn, text, disabled) {
        if (!btn) return;
        btn.innerText = text;
        btn.disabled = !!disabled;
    }

    /* ── Razorpay SDK Loader ─────────────────────────────────── */

    var sdkLoadPromise = null;

    function ensureRazorpayLoaded() {
        // If already loaded, resolve immediately
        if (typeof window.Razorpay !== 'undefined') {
            log('Razorpay SDK already available on window');
            return Promise.resolve();
        }

        // Reuse existing load attempt if one is in progress
        if (sdkLoadPromise) {
            log('Reusing existing SDK load attempt');
            return sdkLoadPromise;
        }

        sdkLoadPromise = new Promise(function (resolve, reject) {
            log('Starting Razorpay SDK load...');

            // Remove any existing broken script tags
            var existing = document.querySelectorAll('script[src*="checkout.razorpay.com"]');
            existing.forEach(function (el) {
                log('Removing existing Razorpay script tag:', el.src);
                el.parentNode.removeChild(el);
            });

            // Create a fresh script tag
            var script = document.createElement('script');
            script.src = 'https://checkout.razorpay.com/v1/checkout.js';
            script.async = true;

            var timeoutId = setTimeout(function () {
                warn('Razorpay SDK load timed out after 15 seconds');
                sdkLoadPromise = null; // Allow retry
                reject(new Error('Razorpay SDK load timed out. Check your internet connection or ad-blocker.'));
            }, 15000);

            script.onload = function () {
                clearTimeout(timeoutId);
                if (typeof window.Razorpay !== 'undefined') {
                    log('Razorpay SDK loaded successfully. Version:', window.Razorpay.version || 'unknown');
                    resolve();
                } else {
                    warn('Razorpay script loaded but window.Razorpay is undefined');
                    sdkLoadPromise = null;
                    reject(new Error('Razorpay script loaded but checkout object is unavailable.'));
                }
            };

            script.onerror = function (e) {
                clearTimeout(timeoutId);
                warn('Razorpay script onerror fired. Likely blocked by ad-blocker or network issue.', e);
                sdkLoadPromise = null;
                reject(new Error('Failed to load Razorpay checkout script. Please disable ad-blocker for this site or check your internet.'));
            };

            document.head.appendChild(script);
            log('Razorpay script tag injected into <head>');
        });

        return sdkLoadPromise;
    }

    /* ── Payment Flow ────────────────────────────────────────── */

    function paySubscription(planId, btn) {
        var btnYr = document.getElementById('btnYearly');
        var isYearly = btnYr && btnYr.classList.contains('active');
        var cycle = isYearly ? 'yearly' : 'monthly';
        log('paySubscription called — planId:', planId, 'cycle:', cycle);
        payNow('subscription', planId, cycle, btn);
    }

    function payNow(type, itemId, cycle, btn) {
        var targetBtn = btn || null;
        var originalText = targetBtn ? targetBtn.innerText : '';
        setButtonState(targetBtn, 'Processing...', true);

        log('payNow called — type:', type, 'itemId:', itemId, 'cycle:', cycle);
        log('Razorpay SDK available at start:', typeof window.Razorpay !== 'undefined');
        log('PUC_BASE:', window.PUC_BASE);
        log('API URL:', apiUrl('create-order.php'));

        var formData = new URLSearchParams();
        formData.append('type', type);
        formData.append('item_id', String(itemId));
        formData.append('cycle', cycle);

        fetch(apiUrl('create-order.php'), {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        })
            .then(function (res) {
                log('create-order response status:', res.status);
                return res.text().then(function (text) {
                    log('create-order raw response:', text.substring(0, 500));
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        warn('create-order returned non-JSON:', text);
                        return { error: 'Server returned invalid JSON. Please refresh and try again.' };
                    }
                });
            })
            .then(function (data) {
                if (data.error) {
                    warn('create-order error:', data.error);
                    alert(data.error);
                    setButtonState(targetBtn, originalText, false);
                    return;
                }

                log('Order created successfully:', {
                    order_id: data.order_id,
                    amount: data.amount,
                    key: data.key,
                    simulated: data.simulated,
                    item_name: data.item_name
                });

                function resetButton() {
                    setButtonState(targetBtn, originalText, false);
                }

                var options = {
                    key: data.key,
                    amount: data.amount,
                    currency: data.currency,
                    name: 'PUC Setu',
                    description: data.item_name,
                    order_id: data.order_id,
                    handler: function (response) {
                        log('Razorpay handler called with:', response);
                        setButtonState(targetBtn, 'Verifying...', true);

                        var verifyData = new URLSearchParams();
                        verifyData.append('razorpay_payment_id', response.razorpay_payment_id || '');
                        verifyData.append('razorpay_order_id', response.razorpay_order_id || '');
                        verifyData.append('razorpay_signature', response.razorpay_signature || '');
                        verifyData.append('simulated', data.simulated ? 'true' : 'false');
                        verifyData.append('type', type);
                        verifyData.append('item_id', String(itemId));
                        verifyData.append('cycle', cycle);

                        fetch(apiUrl('verify-payment.php'), {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: verifyData.toString()
                        })
                            .then(function (res) {
                                log('verify-payment status:', res.status);
                                return res.text().then(function (text) {
                                    log('verify-payment raw response:', text.substring(0, 500));
                                    try {
                                        return JSON.parse(text);
                                    } catch (e) {
                                        warn('verify-payment returned non-JSON:', text);
                                        return { error: 'Verification server returned invalid JSON.' };
                                    }
                                });
                            })
                            .then(function (resData) {
                                if (resData.success) {
                                    log('Payment verified successfully!');
                                    alert('Payment successfully verified! Your subscription/top-up is now active.');
                                    window.location.reload();
                                } else {
                                    warn('Verification failed:', resData.error);
                                    alert('Verification failed: ' + (resData.error || 'Unknown error'));
                                    resetButton();
                                }
                            })
                            .catch(function (err) {
                                warn('Verification fetch error:', err);
                                alert('Verification error. Please try again.');
                                resetButton();
                            });
                    },
                    prefill: {
                        name: data.user_name || '',
                        email: data.user_email || '',
                        contact: data.user_mobile || ''
                    },
                    theme: { color: '#2563EB' },
                    modal: {
                        ondismiss: function () {
                            log('Razorpay modal dismissed by user');
                            resetButton();
                        }
                    }
                };

                // If the backend flagged this as simulated (keys not set or API call failed),
                // skip Razorpay SDK entirely and use the confirm-dialog simulation
                if (data.simulated) {
                    log('Running in SIMULATED mode (Razorpay API unreachable or keys not configured)');
                    if (confirm('Test Mode: Simulate successful checkout for ' + data.item_name + ' of amount INR ' + (data.amount / 100) + '?')) {
                        options.handler({
                            razorpay_payment_id: 'pay_sim_' + Math.random().toString(36).substring(2, 12),
                            razorpay_order_id: data.order_id,
                            razorpay_signature: 'sig_simulated'
                        });
                    } else {
                        resetButton();
                    }
                    return;
                }

                // Real payment — load SDK and open checkout
                log('Loading Razorpay SDK for real checkout...');
                ensureRazorpayLoaded()
                    .then(function () {
                        log('Opening Razorpay checkout overlay');
                        try {
                            var rzp = new window.Razorpay(options);
                            rzp.on('payment.failed', function (resp) {
                                warn('Payment failed:', resp.error);
                                alert('Payment failed: ' + ((resp.error && resp.error.description) || 'Please try again.'));
                                resetButton();
                            });
                            rzp.open();
                        } catch (err) {
                            warn('Razorpay constructor/open error:', err);
                            alert('Could not open Razorpay checkout: ' + err.message);
                            resetButton();
                        }
                    })
                    .catch(function (err) {
                        warn('SDK load failed:', err.message);
                        warn('Falling back to simulated checkout because SDK could not load');
                        // Fallback: offer simulated payment when SDK can't load
                        if (confirm(
                            'Razorpay checkout could not load (' + err.message + ').\n\n' +
                            'Would you like to simulate a test payment for ' + data.item_name +
                            ' (INR ' + (data.amount / 100) + ') instead?'
                        )) {
                            options.handler({
                                razorpay_payment_id: 'pay_sim_' + Math.random().toString(36).substring(2, 12),
                                razorpay_order_id: data.order_id,
                                razorpay_signature: 'sig_simulated'
                            });
                        } else {
                            resetButton();
                        }
                    });
            })
            .catch(function (err) {
                warn('Fetch error for create-order:', err);
                alert('Error creating payment order: ' + (err.message || 'Network error'));
                setButtonState(targetBtn, originalText, false);
            });
    }

    /* ── Public API ───────────────────────────────────────────── */

    window.PucPayment = {
        payNow: payNow,
        paySubscription: paySubscription
    };

    log('PucPayment initialized. Razorpay SDK available:', typeof window.Razorpay !== 'undefined');

})(window);
