(function (wooptpm, $, undefined) {

    wooptpm.storeOrderIdOnServer = function (orderId) {

        try {
            // save the state in the database
            let data = {
                'action'  : 'wgact_purchase_pixels_fired',
                'order_id': orderId
            };

            jQuery.ajax(
                {
                    type    : "post",
                    dataType: "json",
                    url     : ajax_object.ajax_url,
                    data    : data,
                    success : function (msg) {
                        console.log(msg);
                    }
                });
        } catch (e) {
            console.log(e);
        }
    }

}(window.wooptpm = window.wooptpm || {}, jQuery));