var alkimAmazonPay = {
    payButtonCount:0,
    initCheckout: function(){
        console.log('would start checkout');
    },
    ajaxPost: function (form, callback) {
        var url = form.action,
            xhr = new XMLHttpRequest();

        var params = [];

        var fields = form.querySelectorAll('input, select, textarea');
        for(var i = 0; i < fields.length; i++){
            var field = fields[i];
            if(field.name && field.value) {
                params.push(encodeURIComponent(field.name) + '=' + encodeURIComponent(field.value));
            }
        }
        params = params.join('&');
        xhr.open("POST", url);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onload = callback.bind(xhr);
        console.log(url, params);
        xhr.send(params);
    }
}




var commentsInput = document.getElementById('checkout-confirmation-comments-input');
if(commentsInput){
    commentsInput.addEventListener('keyup', function(){
        document.getElementById('checkout-confirmation-comments').value = commentsInput.value;
    });
}