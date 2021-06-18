if (typeof varExists !== "function") {
    function varExists(varName) {
        return new Promise(function (resolve, reject) {
            (function waitForVar() {
                if (typeof window[varName] !== 'undefined') return resolve();
                setTimeout(waitForVar, 30);
            })();
        });
    }
}

varExists('jQuery').then(function () {


    (function (wooptpm, $, undefined) {


        wooptpm.setFacebookIdentifiersOnServer = function () {

            try {

                // get first party cookies fbp and fbc

                // send the cookies to the server in order to save them on the session

                let data = {
                    'action': 'wooptpm_facebook_set_session_identifiers',
                    'nonce' : wooptpm_facebook_premium_only_ajax_object.nonce,
                    'fbp'   : wooptpm.getCookie("_fbp"),
                    'fbc'   : wooptpm.getCookie("_fbc"),
                };

                if (data.fbp && window.sessionStorage && window.sessionStorage.getItem('wooptpm_fb_session_id_' + data.fbp + '_set')) {
                    return;
                }

                jQuery.ajax(
                    {
                        type    : "post",
                        dataType: "json",
                        url     : wooptpm_facebook_premium_only_ajax_object.ajax_url,
                        data    : data,
                        success : function (response) {
                            // console.log(response);

                            if(window.sessionStorage && response['success'] === true ){
                                // console.log('setting session storage');
                                window.sessionStorage.setItem('wooptpm_fb_session_id_' + data.fbp + '_set', JSON.stringify(true));
                            }
                        },
                        error   : function (response) {
                            // console.log(response);
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

            jQuery(document).on('wooptpmFbCapiEvent', function (event, eventData) {

                try {
                    // save the state in the database
                    let data = {
                        'action': 'wooptpm_facebook_capi_event',
                        'data'  : eventData,
                        'nonce' : wooptpm_facebook_premium_only_ajax_object.nonce,
                    };

                    jQuery.ajax(
                        {
                            type    : "post",
                            dataType: "json",
                            url     : wooptpm_facebook_premium_only_ajax_object.ajax_url,
                            data    : data,
                            success : function (msg) {
                                // console.log(msg);
                            },
                            error   : function (msg) {
                                // console.log(msg);
                            }
                        });
                } catch (e) {
                    console.log(e);
                }
            });

        }
    })

    jQuery(window).on('load', function () {

        wooptpmExists().then(function () {

            try {
                if (wooptpmDataLayer.pixels.facebook.pixel_id && wooptpmDataLayer.pixels.facebook.capi) {

                    // We need to be sure that we capture the cid early enough, because the
                    // shop might be using a one click checkout button as early as on the product page.
                    if (['cart', 'checkout'].indexOf(wooptpmDataLayer.shop.page_type) >= 0) {

                        wooptpm.setFacebookIdentifiersOnServer();
                    }
                }
            } catch (e) {
                console.log(e);
            }
        })
    });

}).catch(function () {
    console.log('object couldn\'t be loaded');
})