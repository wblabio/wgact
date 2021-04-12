(function (wooptpm, $, undefined) {

    wooptpm.getCartItemsGa4 = function () {

        let data = [];

        for (const [productId, product] of Object.entries(wooptpmDataLayer.cart)) {

            data.push({
                'item_id'  : product.dyn_r_ids[wooptpmDataLayer.pixels.google.analytics.id_type],
                'item_name': product.name,
                'quantity' : product.quantity,
                // 'affiliation'  : '',
                // 'coupon'       : '',
                // 'discount'     : 0,
                'item_brand'   : product.brand,
                'item_category': product.category,
                'item_variant' : product.variant,
                'price'        : product.price,
                'currency'     : '',
            });
        }

        return data;
    }

}(window.wooptpm = window.wooptpm || {}, jQuery));


jQuery(function () {

    if (wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id) {

        // view_item_list event
        jQuery(document).on('wooptpmViewItemList', function (event, data) {

            // console.log('firing google select_content event');
            // alert('firing google select_content event');
            // console.log(data);

            gtag('event', 'view_item_list', {
                "send_to"       : wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id,
                "items"         : [{
                    "item_id"  : data.dyn_r_ids[wooptpmDataLayer.pixels.google.analytics.id_type],
                    "item_name": data.name,
                    // "coupon"   : "",
                    // "discount": 0,
                    "index"         : data.list_position, // doesn't make sense on mini_cart
                    "item_list_name": data.list_name, // doesn't make sense on mini_cart
                    // "item_list_id": data.list_id, // doesn't make sense on mini_cart
                    // "affiliation": "",
                    "item_brand"   : data.brand,
                    "item_category": data.category,
                    "item_variant" : data.variant,
                    "price"        : data.price,
                    // "currency": "",
                    "quantity": data.quantity,
                }],
                "item_list_name": data.list_name, // doesn't make sense on mini_cart
                // "item_list_id": data.list_id, // doesn't make sense on mini_cart
            });
        });

        // select_content event
        jQuery(document).on('wooptpmSelectContent', function (event, data) {

            // console.log('firing google select_content event');
            // alert('firing google select_content event');
            // console.log(data);

            gtag('event', 'select_content', {
                "send_to"     : wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id,
                "content_type": "product",
                "item_id"     : data.dyn_r_ids[wooptpmDataLayer.pixels.google.analytics.id_type]
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
                // "currency": "",
                // "value": 0,
                "items": [
                    {
                        "item_id"  : data.dyn_r_ids[wooptpmDataLayer.pixels.google.analytics.id_type],
                        "item_name": data.name,
                        // "coupon": "",
                        // "discount": 0,
                        // "affiliation": "",
                        "item_brand"   : data.brand,
                        "item_category": data.category,
                        "item_variant" : data.variant,
                        "price"        : data.price,
                        "currency"     : "",
                        "quantity"     : data.quantity,
                    }
                ]
            });
        });

        // view_item event
        jQuery(document).on('wooptpmViewItem', function (event, data) {

            // console.log('firing google view_item event');
            // alert('firing google view_item event for: ' + wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id);
            // console.log(data);

            gtag('event', 'view_item', {
                "send_to": wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id,
                // "currency": "",
                // "value": 0,
                "items": [
                    {
                        "item_id"  : data.dyn_r_ids[wooptpmDataLayer.pixels.google.analytics.id_type],
                        "item_name": data.name,
                        // "coupon": "",
                        // "discount": 0,
                        // "affiliation": "",
                        "item_brand"   : data.brand,
                        "item_category": data.category,
                        "item_variant" : data.variant,
                        "price"        : data.price,
                        // "currency"     : "",
                        "quantity"     : 1,
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
                // "currency": "",
                // "value": 0,
                "items": [
                    {
                        "item_id"  : data.dyn_r_ids[wooptpmDataLayer.pixels.google.analytics.id_type],
                        "item_name": data.name,
                        // "coupon": "",
                        // "discount": 0,
                        // "affiliation": "",
                        "item_brand"   : data.brand,
                        "item_category": data.category,
                        "item_variant" : data.variant,
                        "price"        : data.price,
                        // "currency": "",
                        "quantity": data.quantity,
                    }
                ]
            });
        });

        // begin_checkout event
        jQuery(document).on('wooptpmBeginCheckout', function (event) {

            // console.log('firing google begin_checkout event');
            // console.log(data);

            gtag('event', 'begin_checkout', {
                "send_to": wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id,
                // "coupon": "",
                // "currency": "",
                // "value": 0,
                "items": wooptpm.getCartItemsGa4()
            });
        });
    }
});