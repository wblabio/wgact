(function (wooptpm, $, undefined) {

    wooptpm.getViewItemProductsGa4 = function (productList) {

        let data = [];

        for (const [key, value] of Object.entries(productList)) {

            data.push({
                'item_id'  : value.dyn_r_ids[wooptpmDataLayer.pixels.google.analytics.id_type],
                'item_name': value['name'],
                'quantity' : 1,
                // 'affiliation': '',
                // 'coupon': '',
                // 'discount': 0,
                // 'index': 0, // probably doesn't make much sense on the product page
                'item_brand'   : value['brand'],
                'item_category': value['category'],
                // 'item_list_name': '', // probably doesn't make much sense on the product page
                // 'item_list_id': '', // probably doesn't make much sense on the product page
                // 'item_variant': '',
                'price': value['price'],
                // 'currency': '',
            });

        }

        return data;
    }

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
                // 'item_variant'      : product.variant,
                'price'   : product.price,
                'currency': '',
            });
        }

        return data;
    }

}(window.wooptpm = window.wooptpm || {}, jQuery));


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
            "items"  : wooptpm.getViewItemProductsGa4(wooptpmDataLayer.visible_products)
        });
    }

    // fire view_item_list on cart page to add related, upsell items to the remarketing list
    if (wooptpmDataLayer.pixels && wooptpmDataLayer.pixels.google.ads.dynamic_remarketing.status && wooptpmDataLayer.shop.page_type === 'cart') {

        // create gtag object with all wooptpmDataLayer.visible_products and fire
        gtag('event', 'view_item_list', {
            "send_to": wooptpmDataLayer.pixels.google.analytics.ga4.measurement_id,
            "items"  : wooptpm.getViewItemProductsGa4(wooptpmDataLayer.upsell_products)
        });
    }

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
                    // "item_variant": data.variant,
                    "price"   : data.price,
                    "currency": "",
                    "quantity": data.quantity,
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
                    // "item_variant": data.variant,
                    "price": data.price,
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


});