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

        let fBUserData;

        wooptpm.getRandomEventId = function () {
            return (Math.random() + 1).toString(36).substring(2);
        }

        wooptpm.getFbUserData = function () {
            // We need the first one for InitiateCheckout
            // where getting the the user_data from the browser is too slow
            // using wooptpm.getCookie(), so we cache the user_data earlier.
            // And we need the second one because the ViewContent hit happens too fast
            // after adding a variation to the cart because the function to cache
            // the user_data is too slow. But we can get the user_data using wooptpm.getCookie()
            // because we don't move away from the page and can wait for the browser
            // to get it.
            if (fBUserData) {
                return fBUserData;
            } else {
                return wooptpm.getFbUserDataFromBrowser();
            }
        }

        wooptpm.setFbUserData = function () {
            fBUserData = wooptpm.getFbUserDataFromBrowser();
        }

        wooptpm.getFbUserDataFromBrowser = function () {
            return {
                "fbp"              : wooptpm.getCookie('_fbp'),
                "fbc"              : wooptpm.getCookie('_fbc'),
                "client_user_agent": navigator.userAgent
            }
        }

        wooptpm.fbViewContent = function (product) {
            let eventId = wooptpm.getRandomEventId();

            fbq("track", "ViewContent", {
                "content_type": "product",
                "content_name": product.name,
                // "content_category": product.category,
                "content_ids": product.dyn_r_ids[wooptpmDataLayer.pixels.facebook.dynamic_remarketing.id_type],
                "currency"   : wooptpmDataLayer.shop.currency,
                "value"      : product.price,
            }, {
                "eventID": eventId,
            });

            product['currency'] = wooptpmDataLayer.shop.currency;

            jQuery(document).trigger('wooptpmFbCapiEvent', {
                "event_name"      : "ViewContent",
                "event_id"        : eventId,
                "user_data"       : wooptpm.getFbUserData(),
                "product_data"    : product,
                "product_id"      : product.dyn_r_ids[wooptpmDataLayer.pixels.facebook.dynamic_remarketing.id_type],
                "event_source_url": window.location.href
            });
        }

    }(window.wooptpm = window.wooptpm || {}, jQuery));

    jQuery(function () {

        if (wooptpm.objectExists(wooptpmDataLayer.pixels.facebook)) {

            // AddToCart event
            jQuery(document).on('wooptpmAddToCart', function (event, product) {

                // console.log('firing facebook ads AddToCart event');
                // console.log(product);
                // console.log('value: ' + product.quantity * product.price);

                let eventId = wooptpm.getRandomEventId();

                // console.log('eventId: ' + eventId);

                fbq("track", "AddToCart", {
                    "content_type": "product",
                    "content_name": product.name,
                    "content_ids" : product.dyn_r_ids[wooptpmDataLayer.pixels.facebook.dynamic_remarketing.id_type],
                    "value"       : parseFloat(product.quantity * product.price),
                    "currency"    : product.currency,
                }, {
                    "eventID": eventId,
                });

                product['currency'] = wooptpmDataLayer.shop.currency;

                jQuery(document).trigger('wooptpmFbCapiEvent', {
                    "event_name"      : "AddToCart",
                    "event_id"        : eventId,
                    "user_data"       : wooptpm.getFbUserData(),
                    "product_data"    : product,
                    "product_id"      : product.dyn_r_ids[wooptpmDataLayer.pixels.facebook.dynamic_remarketing.id_type],
                    "event_source_url": window.location.href
                });
            });

            // InitiateCheckout event
            jQuery(document).on('wooptpmBeginCheckout', function (event) {

                // console.log('firing facebook ads InitiateCheckout event');

                let eventId = wooptpm.getRandomEventId();

                fbq("track", "InitiateCheckout", {}, {
                    "eventID": eventId,
                });


                jQuery(document).trigger('wooptpmFbCapiEvent', {
                    "event_name"      : "InitiateCheckout",
                    "event_id"        : eventId,
                    "user_data"       : wooptpm.getFbUserData(),
                    "event_source_url": window.location.href
                });
            });

            // AddToWishlist event
            jQuery(document).on('wooptpmAddToWishlist', function (event, product) {

                // console.log('firing facebook ads AddToWishlist event');
                // console.log(product);

                let eventId = wooptpm.getRandomEventId();

                fbq("track", "AddToWishlist", {
                    "content_type": "product",
                    "content_name": product.name,
                    "content_ids" : product.dyn_r_ids[wooptpmDataLayer.pixels.facebook.dynamic_remarketing.id_type],
                    "value"       : parseFloat(product.quantity * product.price),
                    "currency"    : product.currency,
                }, {
                    "eventID": eventId,
                });

                product['currency'] = wooptpmDataLayer.shop.currency;

                jQuery(document).trigger('wooptpmFbCapiEvent', {
                    "event_name"      : "AddToWishlist",
                    "event_id"        : eventId,
                    "user_data"       : wooptpm.getFbUserData(),
                    "product_data"    : product,
                    "product_id"      : product.dyn_r_ids[wooptpmDataLayer.pixels.facebook.dynamic_remarketing.id_type],
                    "event_source_url": window.location.href
                });
            });

            // ViewContent event
            jQuery(document).on('wooptpmViewItem', function (event, product) {

                // console.log('firing facebook ads ViewContent event');
                // console.log(product);

                wooptpm.fbViewContent(product);
            });
        }
    })

    jQuery(window).on('load', function () {

        if (wooptpm.objectExists(wooptpmDataLayer.pixels.facebook)) {

            wooptpm.setFbUserData();

            wooptpmExists().then(function () {

                try {
                    if (wooptpmDataLayer.shop.page_type === 'product' && wooptpmDataLayer.shop.product_type !== 'variable' && wooptpm.getMainProductIdFromProductPage()) {

                        let product = wooptpm.getProductDataForViewItemEvent(wooptpm.getMainProductIdFromProductPage());

                        // console.log('fbq ViewContent');
                        // console.log(product);

                        wooptpm.fbViewContent(product);

                    } else if (wooptpmDataLayer.shop.page_type === 'search') {

                        let eventId = wooptpm.getRandomEventId();

                        fbq("track", "Search", {}, {
                            "eventID": eventId,
                        });

                        jQuery(document).trigger('wooptpmFbCapiEvent', {
                            "event_name"      : "Search",
                            "event_id"        : eventId,
                            "user_data"       : wooptpm.getFbUserData(),
                            "event_source_url": window.location.href
                        });
                    }
                } catch (e) {
                    console.log(e);
                }
            })
        }
    });

}).catch(function () {
    console.log('object couldn\'t be loaded');
})