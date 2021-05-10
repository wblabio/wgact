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

        wooptpm.getCartItemsGaUa = function () {

            let data = [];

            for (const [productId, product] of Object.entries(wooptpmDataLayer.cart)) {

                data.push({
                    'id'      : product.dyn_r_ids[wooptpmDataLayer.pixels.google.analytics.id_type],
                    'name'    : product.name,
                    'brand'   : product.brand,
                    'category': product.category,
                    // 'coupon': '',
                    // 'list_name': '',
                    // 'list_position': 1,
                    'price'   : product.price,
                    'quantity': product.quantity,
                    'variant' : product.variant,
                });
            }

            return data;
        }

    }(window.wooptpm = window.wooptpm || {}, jQuery));


    jQuery(function () {

        if (wooptpmDataLayer.pixels.google.analytics.eec && wooptpmDataLayer.pixels.google.analytics.universal.property_id) {

            // fire view_item_list on product page to add related, upsell and cross-sell items to the remarketing list
            // if (wooptpmDataLayer.pixels && wooptpmDataLayer.pixels.google.ads.dynamic_remarketing.status && wooptpmDataLayer.shop.page_type === 'product') {
            //
            //     // reduce wooptpmDataLayer.products to only the ones displayed on the front-end
            //     for (const [key, value] of Object.entries(wooptpmDataLayer.products)) {
            //
            //         if (!jQuery('.post-' + key)[0]) {
            //             delete wooptpmDataLayer.products[key];
            //         }
            //     }
            //
            //     // create gtag object with all wooptpmDataLayer.products and fire
            //     // console.log('test');
            //     // console.log(wooptpm.getViewItemProducts(wooptpmDataLayer.products));
            //     gtag('event', 'view_item_list', {
            //         "send_to": wooptpmDataLayer.pixels.google.analytics.universal.property_id,
            //         "items"  : wooptpm.getViewItemProductsGaUa(wooptpmDataLayer.products)
            //     });
            // }

            // fire view_item_list on cart page to add related, upsell items to the remarketing list
            // if (wooptpmDataLayer.pixels && wooptpmDataLayer.pixels.google.ads.dynamic_remarketing.status && wooptpmDataLayer.shop.page_type === 'cart') {
            //
            //     // create gtag object with all wooptpmDataLayer.products and fire
            //     gtag('event', 'view_item_list', {
            //         "send_to": wooptpmDataLayer.pixels.google.analytics.universal.property_id,
            //         "items"  : wooptpm.getViewItemProductsGaUa(wooptpmDataLayer.upsell_products)
            //     });
            // }

            // view_item_list event
            jQuery(document).on('wooptpmViewItemList', function (event, product) {

                // console.log('firing google select_content event');
                // console.log('firing google select_content event');
                // console.log(product);

                gtag('event', 'view_item_list', {
                    "send_to": wooptpmDataLayer.pixels.google.analytics.universal.property_id,
                    "items"  : [{
                        "id"      : product.dyn_r_ids[wooptpmDataLayer.pixels.google.analytics.id_type],
                        "name"    : product.name,
                        "brand"   : product.brand,
                        "category": product.category,
                        // "coupon"   : "",
                        "list_name"    : wooptpmDataLayer.shop.list_name,
                        "list_position": product.list_position, // doesn't make sense on mini_cart
                        "price"        : product.price,
                        "quantity"     : product.quantity,
                        "variant"      : product.variant,
                    }]
                });
            });

            // select_content event
            jQuery(document).on('wooptpmSelectContentGaUa', function (event, product) {

                // console.log('firing google select_content event');
                // console.log('firing google select_content event');
                // console.log(product);

                gtag('event', 'select_content', {
                    "send_to"     : wooptpmDataLayer.pixels.google.analytics.universal.property_id,
                    "content_type": "product",
                    "items"       : [
                        {
                            "id"           : product.dyn_r_ids[wooptpmDataLayer.pixels.google.analytics.id_type],
                            "name"         : product.name,
                            "list_name"    : wooptpmDataLayer.shop.list_name,
                            "brand"        : product.brand,
                            "category"     : product.category,
                            "variant"      : product.variant,
                            "list_position": product.list_position, // doesn't make sense on mini_cart
                            "quantity"     : product.quantity,
                            "price"        : product.price
                        }
                    ]
                });
            });

            // add_to_cart event
            jQuery(document).on('wooptpmAddToCart', function (event, product) {

                // console.log('firing google add_to_cart event');
                // console.log('firing google add_to_cart event');
                // console.log(product);

                gtag('event', 'add_to_cart', {
                    "send_to" : wooptpmDataLayer.pixels.google.analytics.universal.property_id,
                    "currency": wooptpmDataLayer.shop.currency,
                    "items"   : [
                        {
                            "id"           : product.dyn_r_ids[wooptpmDataLayer.pixels.google.analytics.id_type],
                            "name"         : product.name,
                            "list_name"    : wooptpmDataLayer.shop.list_name,
                            "brand"        : product.brand,
                            "category"     : product.category,
                            "variant"      : product.variant,
                            "list_position": product.list_position, // doesn't make sense on mini_cart
                            "quantity"     : product.quantity,
                            "price"        : product.price
                        }
                    ]
                });
            });

            // view_item event
            jQuery(document).on('wooptpmViewItem', function (event, product) {

                // console.log('firing google view_item event');
                // console.log(product);

                gtag('event', 'view_item', {
                    "send_to": wooptpmDataLayer.pixels.google.analytics.universal.property_id,
                    "items"  : [
                        {
                            "id"           : product.dyn_r_ids[wooptpmDataLayer.pixels.google.analytics.id_type],
                            "name"         : product.name,
                            "list_name"    : wooptpmDataLayer.shop.list_name, // doesn't make sense on mini_cart
                            "brand"        : product.brand,
                            "category"     : product.category,
                            "variant"      : product.variant,
                            "list_position": 1,
                            "quantity"     : 1,
                            "price"        : product.price
                        }
                    ]
                });
            });

            // add_to_wishlist event
            jQuery(document).on('wooptpmAddToWishlist', function (event, product) {

                // console.log('firing google add_to_wishlist event');
                // console.log(product);

                gtag('event', 'add_to_wishlist', {
                    "send_to": wooptpmDataLayer.pixels.google.analytics.universal.property_id,
                    "items"  : [
                        {
                            "id"      : product.dyn_r_ids[wooptpmDataLayer.pixels.google.analytics.id_type],
                            "name"    : product.name,
                            "brand"   : product.brand,
                            "category": product.category,
                            // "coupon"       : "",
                            "list_name"    : wooptpmDataLayer.shop.list_name, // doesn't make sense on mini_cart
                            "list_position": 1,
                            "price"        : product.price,
                            "quantity"     : 1,
                            "variant"      : product.variant,
                        }
                    ]
                });
            });

            // remove_from_cart event
            jQuery(document).on('wooptpmRemoveFromCart', function (event, product) {

                // console.log('firing google remove_from_cart event');
                // console.log('firing google remove_from_cart event');
                // console.log(product);

                gtag('event', 'remove_from_cart', {
                    "send_to" : wooptpmDataLayer.pixels.google.analytics.universal.property_id,
                    "currency": wooptpmDataLayer.shop.currency,
                    "items"   : [
                        {
                            "id"       : product.dyn_r_ids[wooptpmDataLayer.pixels.google.analytics.id_type],
                            "name"     : product.name,
                            "list_name": wooptpmDataLayer.shop.list_name,
                            "brand"    : product.brand,
                            "category" : product.category,
                            "variant"  : product.variant,
                            // "list_position": product.list_position, // doesn't make sense on mini_cart
                            "quantity": product.quantity,
                            "price"   : product.price
                        }
                    ]
                });
            });

            // begin_checkout event
            jQuery(document).on('wooptpmBeginCheckout', function (event) {

                // console.log('firing google begin_checkout event');
                // console.log(product);

                gtag('event', 'begin_checkout', {
                    "send_to" : wooptpmDataLayer.pixels.google.analytics.universal.property_id,
                    "currency": wooptpmDataLayer.shop.currency,
                    "items"   : wooptpm.getCartItemsGaUa()
                });
            });

            // set_checkout_option event
            jQuery(document).on('wooptpmFireCheckoutOption', function (event, data) {

                // console.log('firing google set_checkout_option event');
                // console.log(data);

                gtag('event', 'set_checkout_option', {
                    "send_to"        : wooptpmDataLayer.pixels.google.analytics.universal.property_id,
                    "checkout_step"  : data.step,
                    "checkout_option": data.checkout_option,
                    "value"          : data.value
                });
            });

            // checkout_progress event
            jQuery(document).on('wooptpmFireCheckoutProgress', function (event, data) {

                // console.log('firing google checkout_progress event');
                // console.log(data);

                gtag('event', 'checkout_progress', {
                    "send_to"      : wooptpmDataLayer.pixels.google.analytics.universal.property_id,
                    "checkout_step": data.step,
                });
            });
        }
    });

    jQuery(window).on('load', function () {

        wooptpmExists().then(function () {

            try {
                if (wooptpmDataLayer.shop.page_type === 'product' && wooptpm.getMainProductIdFromProductPage()) {

                    let product = wooptpm.getProductDataForViewItemEvent(wooptpm.getMainProductIdFromProductPage());

                    gtag("event", "view_item", {
                        "send_to": wooptpmDataLayer.pixels.google.analytics.universal.property_id,
                        "items"  : [{
                            "id"      : product.dyn_r_ids[wooptpmDataLayer.pixels.google.ads.dynamic_remarketing.id_type],
                            "name"    : product.name,
                            "brand"   : product.brand,
                            "category": product.category,
                            // "coupon": "",
                            // "list_name": "",
                            // "list_position": 1,
                            "price"   : product.price,
                            "quantity": 1,
                            "variant" : product.variant,
                        }]
                    });
                } else if (wooptpmDataLayer.shop.page_type === 'search') {

                    let products = [];

                    for (const [key, product] of Object.entries(wooptpmDataLayer.products)) {
                        // console.log(`${key}: ${value}`);

                        products.push({
                            "id"      : product.dyn_r_ids[wooptpmDataLayer.pixels.google.ads.dynamic_remarketing.id_type],
                            "name"    : product.name,
                            "quantity": 1,
                            // "affiliation": "",
                            // "coupon": "",
                            // "discount": 0,
                            "list_position": product.position,
                            "brand"        : product.brand,
                            "category"     : product.category,
                            "list_name"    : wooptpmDataLayer.shop.list_name,
                            "variant"      : product.variant,
                            "price"        : product.price,
                        });
                    }

                    gtag("event", "view_search_results", {
                        "send_to"    : wooptpmDataLayer.pixels.google.analytics.universal.property_id,
                        "search_term": wooptpm.getSearchTermFromUrl(),
                        "items"      : products
                    });
                }
            } catch (e) {
                console.log(e);
            }
        })
    });

    jQuery(window).on('load', function () {

        wooptpmExists().then(function () {

            try {
                if (wooptpmDataLayer.pixels.google.analytics.eec && wooptpmDataLayer.pixels.google.analytics.universal.property_id) {

                    // We need to be sure that we capture the cid early enough, because the
                    // shop might be using a one click checkout button as early as on the product page.
                    if (['cart', 'checkout', 'product'].indexOf(wooptpmDataLayer.shop.page_type) >= 0) {

                        let targetID = wooptpmDataLayer.pixels.google.analytics.universal.property_id;

                        wooptpm.setGoogleCidOnServer(targetID);
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