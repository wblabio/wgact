jQuery(function () {

    // fire view_item_list on product page to add related, upsell and cross-sell items to the remarketing list
    if (wooptpmDataLayer.pixels && wooptpmDataLayer.pixels.google.ads.dynamic_remarketing.status && wooptpmDataLayer.shop.page_type === 'product') {

        // reduce wooptpmDataLayer.visible_products to only the ones displayed on the front-end
        for (const [key, value] of Object.entries(wooptpmDataLayer.visible_products)) {

            if (!jQuery('.post-' + key)[0]) {
                delete wooptpmDataLayer.visible_products[key];
            }
        }

        // create gtag object with all wooptpmDataLayer.visible_products and fire
        // alert('test');
        // console.log(wooptpm.getViewItemProducts(wooptpmDataLayer.visible_products));
        gtag('event', 'view_item_list', {
            "send_to": wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id,
            "items": wooptpm.getViewItemProducts(wooptpmDataLayer.visible_products)
        });
    }

    // fire view_item_list on cart page to add related, upsell items to the remarketing list
    if (wooptpmDataLayer.pixels && wooptpmDataLayer.pixels.google.ads.dynamic_remarketing.status && wooptpmDataLayer.shop.page_type === 'cart') {

        // create gtag object with all wooptpmDataLayer.visible_products and fire
        gtag('event', 'view_item_list', {
            "send_to": wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id,
            "items": wooptpm.getViewItemProducts(wooptpmDataLayer.upsell_products)
        });
    }

    // select_content event
    jQuery(document).on('wooptpmSelectContent', function (event, data) {

        // console.log('firing google select_content event');
        // alert('firing google select_content event');
        // console.log(data);

        gtag('event', 'select_content', {
            "send_to": wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id,
            "content_type": "product",
            "items"       : [
                {
                    "id"       : data.id,
                    "name"     : data.name,
                    "list_name": data.list_name, // doesn't make sense on mini_cart
                    "brand"    : data.brand,
                    "category" : data.category,
                    // "variant": data.variant,
                    "list_position": data.list_position, // doesn't make sense on mini_cart
                    "quantity"     : data.quantity,
                    "price"        : data.price
                }
            ]
        });
    });

    // add_to_cart event
    jQuery(document).on('wooptpmAddToCart', function (event, data) {

        // console.log('firing google add_to_cart event');
        // alert('firing google add_to_cart event for: ' + wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id);
        //
        // console.log(data);

        gtag('event', 'add_to_cart', {
            "send_to": wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id,
            "items": [
                {
                    "id"       : data.id,
                    "name"     : data.name,
                    "list_name": data.list_name, // doesn't make sense on mini_cart
                    "brand"    : data.brand,
                    "category" : data.category,
                    // "variant": data.variant,
                    "list_position": data.list_position, // doesn't make sense on mini_cart
                    "quantity"     : data.quantity,
                    "price"        : data.price
                }
            ]
        });
    });

    // remove_from_cart event
    jQuery(document).on('wooptpmRemoveFromCart', function (event, data) {

        // console.log('firing google remove_from_cart event');
        // alert('firing google remove_from_cart event');
        // console.log(data);

        gtag('event', 'remove_from_cart', {
            "send_to": wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id,
            "items": [
                {
                    "id"  : data.id,
                    "name": data.name,
                    // "list_name": data.list_name, // doesn't make sense on mini_cart
                    "brand"   : data.brand,
                    "category": data.category,
                    // "variant": data.variant,
                    // "list_position": data.list_position, // doesn't make sense on mini_cart
                    "quantity": data.quantity,
                    "price"   : data.price
                }
            ]
        });
    });

    // begin_checkout event
    jQuery(document).on('wooptpmBeginCheckout', function (event, data) {

        // console.log('firing google begin_checkout event');
        // console.log(data);

        gtag('event', 'begin_checkout', {
            "send_to": wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id,
            "items": data
        });
    });

    // set_checkout_option event
    jQuery(document).on('wooptpmFireCheckoutOption', function (event, data) {

        // console.log('firing google set_checkout_option event');
        // console.log(data);

        gtag('event', 'set_checkout_option', {
            "send_to": wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id,
            "checkout_step"  : data.step,
            "checkout_option": data.checkout_option,
            "value"          : data.value
        });
    });
});