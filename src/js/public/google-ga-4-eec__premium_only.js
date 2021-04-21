(function (wooptpm, $, undefined) {

    wooptpm.getCartItemsGa4 = function () {

        let data = [];

        for (const [productId, product] of Object.entries(wooptpmDataLayer.cart)) {

            data.push({
                "item_id"  : product.dyn_r_ids[wooptpmDataLayer.pixels.google.analytics.id_type],
                "item_name": product.name,
                "quantity" : product.quantity,
                // "affiliation'  : '',
                // "coupon"       : '',
                // "discount"     : 0,
                "item_brand"   : product.brand,
                "item_category": product.category,
                "item_variant" : product.variant,
                "price"        : product.price,
                "currency"     : wooptpmDataLayer.shop.currency,
            });
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

            gtag('event', 'view_item_list', {
                "send_to"       : wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id,
                "items"         : [{
                    "item_id"  : product.dyn_r_ids[wooptpmDataLayer.pixels.google.analytics.id_type],
                    "item_name": product.name,
                    // "coupon"   : "",
                    // "discount": 0,
                    "index"         : product.list_position, // doesn't make sense on mini_cart
                    "item_list_name": wooptpmDataLayer.shop.list_name,
                    "item_list_id"  : wooptpmDataLayer.id,
                    // "affiliation": "",
                    "item_brand"   : product.brand,
                    "item_category": product.category,
                    "item_variant" : product.variant,
                    "price"        : product.price,
                    "currency"     : wooptpmDataLayer.shop.currency,
                    "quantity"     : product.quantity,
                }],
                "item_list_name": product.list_name, // doesn't make sense on mini_cart
                // "item_list_id": product.list_id, // doesn't make sense on mini_cart
            });
        });

        // select_item event
        jQuery(document).on('wooptpmSelectItem', function (event, product) {

            // console.log('firing google select_content event');
            // console.log('firing google select_content event');
            // console.log(product);

            gtag('event', 'select_item', {
                "send_to": wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id,
                "items"  : [{
                    "item_id"  : product.dyn_r_ids[wooptpmDataLayer.pixels.google.analytics.id_type],
                    "item_name": product.name,
                    // "coupon"   : "",
                    // "discount": 0,
                    "index"         : product.list_position, // doesn't make sense on mini_cart
                    "item_list_name": wooptpmDataLayer.shop.list_name,
                    "item_list_id"  : wooptpmDataLayer.id,
                    // "affiliation": "",
                    "item_brand"   : product.brand,
                    "item_category": product.category,
                    "item_variant" : product.variant,
                    "price"        : product.price,
                    "currency"     : wooptpmDataLayer.shop.currency,
                    "quantity"     : product.quantity,
                }],
            });
        });

        // add_to_cart event
        jQuery(document).on('wooptpmAddToCart', function (event, product) {

            // console.log('firing google add_to_cart event');
            // console.log('firing google add_to_cart event for: ' + wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id);
            // console.log(product);

            gtag('event', 'add_to_cart', {
                "send_to" : wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id,
                "currency": wooptpmDataLayer.shop.currency,
                // "value": 0,
                "items": [
                    {
                        "item_id"       : product.dyn_r_ids[wooptpmDataLayer.pixels.google.analytics.id_type],
                        "item_list_name": wooptpmDataLayer.shop.list_name,
                        "item_list_id"  : wooptpmDataLayer.id,
                        // "coupon": "",
                        // "discount": 0,
                        // "affiliation": "",
                        "item_brand"   : product.brand,
                        "item_category": product.category,
                        "item_variant" : product.variant,
                        "price"        : product.price,
                        "currency"     : wooptpmDataLayer.shop.currency,
                        "quantity"     : product.quantity,
                    }
                ]
            });
        });

        // view_item event
        jQuery(document).on('wooptpmViewItem', function (event, product) {

            // console.log('firing google view_item event');
            // console.log('firing google view_item event for: ' + wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id);
            // console.log(product);

            gtag('event', 'view_item', {
                "send_to" : wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id,
                "currency": wooptpmDataLayer.shop.currency,
                // "value": 0,
                "items": [
                    {
                        "item_id"  : product.dyn_r_ids[wooptpmDataLayer.pixels.google.analytics.id_type],
                        "item_name": product.name,
                        // "coupon": "",
                        // "discount": 0,
                        // "affiliation": "",
                        "item_brand"   : product.brand,
                        "item_category": product.category,
                        "item_variant" : product.variant,
                        "price"        : product.price,
                        "currency"     : wooptpmDataLayer.shop.currency,
                        "quantity"     : 1,
                    }
                ]
            });
        });

        // add_to_wishlist event
        jQuery(document).on('wooptpmAddToWishlist', function (event, product) {

            // console.log('firing google add_to_wishlist event');
            // console.log('firing google add_to_wishlist event for: ' + wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id);
            // console.log(product);

            gtag('event', 'add_to_wishlist', {
                "send_to" : wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id,
                "currency": wooptpmDataLayer.shop.currency,
                // "value": 0,
                "items": [
                    {
                        "item_id"  : product.dyn_r_ids[wooptpmDataLayer.pixels.google.analytics.id_type],
                        "item_name": product.name,
                        "quantity"     : 1,
                        // "affiliation": "",
                        // "coupon": "",
                        // "discount": 0,
                        "item_brand"   : product.brand,
                        "item_category": product.category,
                        "item_variant" : product.variant,
                        "price"        : product.price,
                        "currency"     : wooptpmDataLayer.shop.currency,
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
                "send_to" : wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id,
                "currency": wooptpmDataLayer.shop.currency,
                // "value": 0,
                "items": [
                    {
                        "item_id"  : product.dyn_r_ids[wooptpmDataLayer.pixels.google.analytics.id_type],
                        "item_name": product.name,
                        // "coupon": "",
                        // "discount": 0,
                        // "affiliation": "",
                        "item_brand"   : product.brand,
                        "item_category": product.category,
                        "item_variant" : product.variant,
                        "price"        : product.price,
                        "currency"     : wooptpmDataLayer.shop.currency,
                        "quantity"     : product.quantity,
                    }
                ]
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
            if (wooptpmDataLayer.shop.page_type === 'product' && wooptpm.getMainProductIdFromProductPage()) {

                let product = wooptpm.getProductDataForViewItemEvent(wooptpm.getMainProductIdFromProductPage());

                gtag("event", "view_item", {
                    "send_to" : wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id,
                    "currency": wooptpmDataLayer.shop.currency,
                    "value"   : 1 * product.price,
                    "items"   : [{
                        "item_id"  : product.dyn_r_ids[wooptpmDataLayer.pixels.google.ads.dynamic_remarketing.id_type],
                        "item_name": product.name,
                        // "coupon": "",
                        // "discount": 0,
                        // "affiliation": "",
                        "item_brand"   : product.brand,
                        "item_category": product.category,
                        "item_variant" : product.variant,
                        "price"        : product.price,
                        "currency"     : wooptpmDataLayer.shop.currency,
                        "quantity"     : 1,
                    }]
                });
            } else if (wooptpmDataLayer.shop.page_type === 'search') {

                let products = [];

                for (const [key, product] of Object.entries(wooptpmDataLayer.products)) {
                    // console.log(`${key}: ${value}`);

                    products.push({
                        "item_id"  : product.dyn_r_ids[wooptpmDataLayer.pixels.google.ads.dynamic_remarketing.id_type],
                        "item_name": product.name,
                        "quantity" : 1,
                        // "affiliation": "",
                        // "coupon": "",
                        // "discount": 0,
                        "index"         : product.position,
                        "item_brand"    : product.brand,
                        "item_category" : product.category,
                        "item_list_name": wooptpmDataLayer.shop.list_name,
                        "item_list_id"  : wooptpmDataLayer.shop.list_id,
                        "item_variant"  : product.variant,
                        "price"         : product.price,
                        "currency"      : wooptpmDataLayer.shop.currency,
                    });
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