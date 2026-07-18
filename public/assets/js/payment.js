/* Secure Razorpay Standard Checkout: orders and signature verification happen on the server. */
(function () {
    var config = window.driveCueCheckout;
    if (!config) return;

    function request(url, body) {
        return fetch(url, {method: 'POST', credentials: 'same-origin', headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': config.csrf}, body: JSON.stringify(body)})
            .then(function (response) { return response.json().catch(function () { return {}; }).then(function (data) { if (!response.ok) throw new Error(data.message || 'Payment request failed.'); return data; }); });
    }

    document.querySelectorAll('.js-buy').forEach(function (button) {
        button.addEventListener('click', function () {
            if (typeof window.Razorpay === 'undefined') { alert('Razorpay checkout could not be loaded. Please disable any ad blocker and try again.'); return; }
            var original = button.textContent;
            button.disabled = true; button.textContent = 'Preparing checkout…';
            request(config.createOrder, {plan_id: Number(button.dataset.plan), billing_cycle: button.dataset.cycle || 'monthly'}).then(function (order) {
                var checkout = new Razorpay({key: order.key, amount: order.amount, currency: order.currency, name: order.name, description: order.description, order_id: order.order_id, prefill: order.prefill, theme: {color: '#2563eb'}, handler: function (response) {
                    button.textContent = 'Verifying payment…';
                    request(config.verify, response).then(function (result) { alert(result.message || 'Payment verified.'); window.location.href = '/dashboard'; }).catch(function (error) { alert(error.message); button.disabled = false; button.textContent = original; });
                }, modal: {ondismiss: function () { button.disabled = false; button.textContent = original; }}});
                checkout.on('payment.failed', function (response) { alert(response.error.description || 'Payment failed.'); button.disabled = false; button.textContent = original; });
                checkout.open();
            }).catch(function (error) { alert(error.message); button.disabled = false; button.textContent = original; });
        });
    });
})();
