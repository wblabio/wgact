(function (wooptpm, $, undefined) {


    wooptpm.setFacebookIdentifiersOnServer = function () {

        try {

            // get first party cookies fbp and fbc

            // send the cookies to the server in order to save them on the session


            let data = {
                'action': 'wooptpm_facebook_set_session_identifiers',
                'nonce' : wooptpm_facebook_premium_only_ajax_object.nonce,
                'fbp'   : Cookies.get("_fbp"),
                'fbc'   : Cookies.get("_fbc"),
            };

            jQuery.ajax(
                {
                    type    : "post",
                    dataType: "json",
                    url     : wooptpm_facebook_premium_only_ajax_object.ajax_url,
                    data    : data,
                    success : function (response) {
                        // console.log(response);
                    },
                    error   : function (msg) {
                        // console.log(msg);
                    },
                });

        } catch (e) {
            console.log(e);
        }
    }

}(window.wooptpm = window.wooptpm || {}, jQuery));

jQuery(function () {

    if (wooptpm.objectExists(wooptpmDataLayer.pixels.facebook)) {

        // add_to_cart event
        jQuery(document).on('wooptpmAddToWishlist', function (event, product) {

            // console.log('firing facebook ads AddToWishlist event');
            // console.log(data);

            fbq("track", "AddToWishlist", {
                "content_name"    : product.name,
                "content_category": product.category,
                "content_ids"     : product.dyn_r_ids[wooptpmDataLayer.pixels.facebook.dynamic_remarketing.id_type],
                // "contents"          : "",
                "currency": product.currency,
                "value"   : product.price,
            });
        });

        jQuery(document).on('wooptpmBeginCheckout', function (event) {

            // console.log('firing facebook ads InitiateCheckout event');
            // console.log(data);

            fbq("track", "InitiateCheckout");
        });

    }
})

jQuery(window).on('load', function () {

    wooptpmExists().then(function () {

        try {
            if (wooptpmDataLayer.pixels.facebook.pixel_id) {

                // We need to be sure that we capture the cid early enough, because the
                // shop might be using a one click checkout button as early as on the product page.
                if (['cart', 'checkout', 'product'].indexOf(wooptpmDataLayer.shop.page_type) >= 0) {


                    wooptpm.setFacebookIdentifiersOnServer();
                }
            }
        } catch (e) {
            console.log(e);
        }
    })
});