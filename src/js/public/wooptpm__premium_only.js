if (typeof varExists !== "function") {
    function varExists(varName) {
        return new Promise(function (resolve, reject) {
            (function waitForJQuery() {
                if (typeof window[varName] !== 'undefined') return resolve();
                setTimeout(waitForJQuery, 30);
            })();
        });
    }
}

varExists('jQuery').then(function () {

    (function (wooptpm, $, undefined) {

        wooptpm.storeOrderIdOnServer = function (orderId) {

            // console.log('saving wooptpm_purchase_pixels_fired');

            // console.log('url: ' + wooptpm_premium_only_ajax_object.ajax_url);
            // console.log('nonce: ' + wooptpm_premium_only_ajax_object.nonce);


            try {
                // save the state in the database
                let data = {
                    'action'  : 'wooptpm_purchase_pixels_fired',
                    'order_id': orderId,
                    'nonce'   : wooptpm_premium_only_ajax_object.nonce,
                };

                jQuery.ajax(
                    {
                        type    : "post",
                        dataType: "json",
                        url     : wooptpm_premium_only_ajax_object.ajax_url,
                        data    : data,
                        success : function (response) {
                            if (response.success === false) {
                                console.log(response);
                            }
                        },
                        error   : function (response) {
                            console.log(response);
                        }
                    });
            } catch (e) {
                console.log(e);
            }
        }

    }(window.wooptpm = window.wooptpm || {}, jQuery));

}).catch(function () {
    console.log('object couldn\'t be loaded');
})