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

        wooptpm.getCartItemsGa4 = function () {

            let data = [];

            for (const [productId, product] of Object.entries(wooptpmDataLayer.cart)) {

                let productItems = {
                    "item_id"  : product.dyn_r_ids[wooptpmDataLayer.pixels.google.analytics.id_type],
                    "item_name": product.name,
                    "quantity" : product.quantity,
                    // "affiliation'  : '',
                    // "coupon"       : '',
                    // "discount"     : 0,
                    "item_brand"  : product.brand,
                    "item_variant": product.variant,
                    "price"       : product.price,
                    "currency"    : wooptpmDataLayer.shop.currency,
                }

                productItems = wooptpm.getFormattedGA4Categories(productItems, product.category);

                data.push(productItems);
            }

            return data;
        }

    }(window.wooptpm = window.wooptpm || {}, jQuery));


    jQuery(function () {

        if (wooptpmDataLayer.pixels.google.analytics.eec && wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id) {

            // view_item_list event
            jQuery(document).on('wooptpmViewItemList', function (event, product) {

                // console.log('firing google select_content event');
                // console.log('firing google select_content event');
                // console.log(product);

                let productItems = {
                    "item_id"  : product.dyn_r_ids[wooptpmDataLayer.pixels.google.analytics.id_type],
                    "item_name": product.name,
                    // "coupon"   : "",
                    // "discount": 0,
                    "index"         : product.list_position, // doesn't make sense on mini_cart
                    "item_list_name": wooptpmDataLayer.shop.list_name,
                    "item_list_id"  : wooptpmDataLayer.id,
                    // "affiliation": "",
                    "item_brand"  : product.brand,
                    "item_variant": product.variant,
                    "price"       : product.price,
                    "currency"    : wooptpmDataLayer.shop.currency,
                    "quantity"    : product.quantity,
                }

                productItems = wooptpm.getFormattedGA4Categories(productItems, product.category);

                gtag('event', 'view_item_list', {
                    "send_to"       : wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id,
                    "items"         : [productItems],
                    "item_list_name": product.list_name, // doesn't make sense on mini_cart
                    // "item_list_id": product.list_id, // doesn't make sense on mini_cart
                });
            });

            // select_item event
            jQuery(document).on('wooptpmSelectItem', function (event, product) {

                // console.log('firing google select_content event');
                // console.log('firing google select_content event');
                // console.log(product);

                let productItems = {
                    "item_id"  : product.dyn_r_ids[wooptpmDataLayer.pixels.google.analytics.id_type],
                    "item_name": product.name,
                    // "coupon"   : "",
                    // "discount": 0,
                    "index"         : product.list_position, // doesn't make sense on mini_cart
                    "item_list_name": wooptpmDataLayer.shop.list_name,
                    "item_list_id"  : wooptpmDataLayer.id,
                    // "affiliation": "",
                    "item_brand"  : product.brand,
                    "item_variant": product.variant,
                    "price"       : product.price,
                    "currency"    : wooptpmDataLayer.shop.currency,
                    "quantity"    : product.quantity,
                }

                productItems = wooptpm.getFormattedGA4Categories(productItems, product.category);

                gtag('event', 'select_item', {
                    "send_to": wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id,
                    "items"  : [productItems],
                });
            });

            // add_to_cart event
            jQuery(document).on('wooptpmAddToCart', function (event, product) {

                // console.log('firing google add_to_cart event');
                // console.log('firing google add_to_cart event for: ' + wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id);
                // console.log(product);

                let productItems = {
                    "item_name"     : product.name,
                    "item_id"       : product.dyn_r_ids[wooptpmDataLayer.pixels.google.analytics.id_type],
                    "item_list_name": wooptpmDataLayer.shop.list_name,
                    "item_list_id"  : wooptpmDataLayer.id,
                    // "coupon": "",
                    // "discount": 0,
                    // "affiliation": "",
                    "item_brand"  : product.brand,
                    "item_variant": product.variant,
                    "price"       : product.price,
                    "currency"    : wooptpmDataLayer.shop.currency,
                    "quantity"    : product.quantity,
                }

                productItems = wooptpm.getFormattedGA4Categories(productItems, product.category);

                gtag('event', 'add_to_cart', {
                    "send_to" : wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id,
                    "currency": wooptpmDataLayer.shop.currency,
                    // "value": 0,
                    "items": [productItems]
                });
            });

            // view_item event
            jQuery(document).on('wooptpmViewItem', function (event, product) {

                // console.log('firing google view_item event');
                // console.log('firing google view_item event for: ' + wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id);
                // console.log(product);

                let productItems = {
                    "item_id"  : product.dyn_r_ids[wooptpmDataLayer.pixels.google.analytics.id_type],
                    "item_name": product.name,
                    // "coupon": "",
                    // "discount": 0,
                    // "affiliation": "",
                    "item_brand"  : product.brand,
                    "item_variant": product.variant,
                    "price"       : product.price,
                    "currency"    : wooptpmDataLayer.shop.currency,
                    "quantity"    : 1,
                }

                productItems = wooptpm.getFormattedGA4Categories(productItems, product.category);

                gtag('event', 'view_item', {
                    "send_to" : wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id,
                    "currency": wooptpmDataLayer.shop.currency,
                    // "value": 0,
                    "items": [productItems]
                });
            });

            // add_to_wishlist event
            jQuery(document).on('wooptpmAddToWishlist', function (event, product) {

                // console.log('firing google add_to_wishlist event');
                // console.log('firing google add_to_wishlist event for: ' + wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id);
                // console.log(product);

                let productItems = {
                    "item_id"  : product.dyn_r_ids[wooptpmDataLayer.pixels.google.analytics.id_type],
                    "item_name": product.name,
                    "quantity" : 1,
                    // "affiliation": "",
                    // "coupon": "",
                    // "discount": 0,
                    "item_brand"  : product.brand,
                    "item_variant": product.variant,
                    "price"       : product.price,
                    "currency"    : wooptpmDataLayer.shop.currency,
                }

                productItems = wooptpm.getFormattedGA4Categories(productItems, product.category);

                gtag('event', 'add_to_wishlist', {
                    "send_to" : wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id,
                    "currency": wooptpmDataLayer.shop.currency,
                    // "value": 0,
                    "items": [productItems]
                });
            });

            // remove_from_cart event
            jQuery(document).on('wooptpmRemoveFromCart', function (event, product) {

                // console.log('firing google remove_from_cart event');
                // console.log('firing google remove_from_cart event');
                // console.log(product);

                let productItems = {
                    "item_id"  : product.dyn_r_ids[wooptpmDataLayer.pixels.google.analytics.id_type],
                    "item_name": product.name,
                    // "coupon": "",
                    // "discount": 0,
                    // "affiliation": "",
                    "item_brand"  : product.brand,
                    "item_variant": product.variant,
                    "price"       : product.price,
                    "currency"    : wooptpmDataLayer.shop.currency,
                    "quantity"    : product.quantity,
                }

                productItems = wooptpm.getFormattedGA4Categories(productItems, product.category);

                gtag('event', 'remove_from_cart', {
                    "send_to" : wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id,
                    "currency": wooptpmDataLayer.shop.currency,
                    // "value": 0,
                    "items": [productItems]
                });
            });

            // begin_checkout event
            jQuery(document).on('wooptpmBeginCheckout', function (event) {

                // console.log('firing google begin_checkout event');
                // console.log(product);

                gtag('event', 'begin_checkout', {
                    "send_to": wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id,
                    // "coupon": "",
                    "currency": wooptpmDataLayer.shop.currency,
                    // "value": 0,
                    "items": wooptpm.getCartItemsGa4()
                });
            });
        }
    });

    jQuery(window).on('load', function () {

        wooptpmExists().then(function () {

            try {
                if (wooptpmDataLayer.shop.page_type === 'product' && wooptpmDataLayer.shop.product_type !== 'variable' && wooptpm.getMainProductIdFromProductPage()) {

                    let product = wooptpm.getProductDataForViewItemEvent(wooptpm.getMainProductIdFromProductPage());

                    let productItems = {
                        "item_id"  : product.dyn_r_ids[wooptpmDataLayer.pixels.google.analytics.id_type],
                        "item_name": product.name,
                        // "coupon": "",
                        // "discount": 0,
                        // "affiliation": "",
                        "item_brand"  : product.brand,
                        "item_variant": product.variant,
                        "price"       : product.price,
                        "currency"    : wooptpmDataLayer.shop.currency,
                        "quantity"    : 1,
                    }

                    productItems = wooptpm.getFormattedGA4Categories(productItems, product.category);

                    gtag("event", "view_item", {
                        "send_to" : wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id,
                        "currency": wooptpmDataLayer.shop.currency,
                        "value"   : 1 * product.price,
                        "items"   : [productItems]
                    });
                } else if (wooptpmDataLayer.shop.page_type === 'search') {

                    let products = [];

                    for (const [key, product] of Object.entries(wooptpmDataLayer.products)) {
                        // console.log(`${key}: ${value}`);

                        let productItems = {
                            "item_id"  : product.dyn_r_ids[wooptpmDataLayer.pixels.google.analytics.id_type],
                            "item_name": product.name,
                            "quantity" : 1,
                            // "affiliation": "",
                            // "coupon": "",
                            // "discount": 0,
                            "index"         : product.position,
                            "item_brand"    : product.brand,
                            "item_list_name": wooptpmDataLayer.shop.list_name,
                            "item_list_id"  : wooptpmDataLayer.shop.list_id,
                            "item_variant"  : product.variant,
                            "price"         : product.price,
                            "currency"      : wooptpmDataLayer.shop.currency,
                        }

                        productItems = wooptpm.getFormattedGA4Categories(productItems, product.category);

                        products.push(productItems);
                    }

                    gtag("event", "view_search_results", {
                        "send_to"    : wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id,
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
                if (wooptpmDataLayer.pixels.google.analytics.eec && wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id) {

                    let targetID = wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id;

                    // console.log('setting cid on server: ' + targetID);

                    // We need to be sure that we capture the cid early enough, because the
                    // shop might be using a one click checkout button as early as on the product page.
                    if (['product', 'cart', 'checkout'].indexOf(wooptpmDataLayer.shop.page_type) >= 0) {
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