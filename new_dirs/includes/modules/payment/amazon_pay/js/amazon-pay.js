const alkimAmazonPay = {
    payButtonCount: 0,
    initCheckout: function () {
        console.log('would start checkout');
    },
    ajaxPost: function (form, callback) {
        const url = form.action, xhr = new XMLHttpRequest();
        const params = [];
        const fields = form.querySelectorAll('input, select, textarea');
        for (let i = 0; i < fields.length; i++) {
            const field = fields[i];
            if (field.name && field.value) {
                params.push(encodeURIComponent(field.name) + '=' + encodeURIComponent(field.value));
            }
        }
        xhr.open("POST", url);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onload = callback.bind(xhr);
        xhr.send(params.join('&'));
    }
};


const commentsInput = document.getElementById('checkout-confirmation-comments-input');
if (commentsInput) {
    commentsInput.addEventListener('keyup', function () {
        document.getElementById('checkout-confirmation-comments').value = commentsInput.value;
    });
}

const amazonPayUseCreditCheckbox = document.querySelector('[name="amazon_pay_use_credit"]');
if (amazonPayUseCreditCheckbox) {
    amazonPayUseCreditCheckbox.addEventListener('change', function () {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", useCreditUrl);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.send('use_credit=' + (amazonPayUseCreditCheckbox.checked ? 1 : 0));
        xhr.onload = function () {
            window.location.reload();
        }
    });
}
