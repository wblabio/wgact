(function (wooptpm, $, undefined) {

    wooptpm.getCartItemsGaUa = function () {

        let product = [];

        for (const [productId, product] of Object.entries(wooptpmDataLayer.cart)) {

            product.push({
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

        return product;
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
        //     // alert('test');
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
            // alert('firing google select_content event');
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
            // alert('firing google select_content event');
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
            // alert('firing google add_to_cart event');
            // console.log(product);

            gtag('event', 'add_to_cart', {
                "send_to": wooptpmDataLayer.pixels.google.analytics.universal.property_id,
                "items"  : [
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
            // alert('firing google view_item event');
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

        // remove_from_cart event
        jQuery(document).on('wooptpmRemoveFromCart', function (event, product) {

            // console.log('firing google remove_from_cart event');
            // alert('firing google remove_from_cart event');
            // console.log(product);

            gtag('event', 'remove_from_cart', {
                "send_to": wooptpmDataLayer.pixels.google.analytics.universal.property_id,
                "items"  : [
                    {
                        "id"  : product.dyn_r_ids[wooptpmDataLayer.pixels.google.analytics.id_type],
                        "name": product.name,
                        "list_name": wooptpmDataLayer.shop.list_name,
                        "brand"   : product.brand,
                        "category": product.category,
                        "variant" : product.variant,
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
                "send_to": wooptpmDataLayer.pixels.google.analytics.universal.property_id,
                "items"  : wooptpm.getCartItemsGaUa()
            });
        });

        // set_checkout_option event
        jQuery(document).on('wooptpmFireCheckoutOption', function (event, product) {

            // console.log('firing google set_checkout_option event');
            // console.log(product);

            gtag('event', 'set_checkout_option', {
                "send_to"        : wooptpmDataLayer.pixels.google.analytics.universal.property_id,
                "checkout_step"  : product.step,
                "checkout_option": product.checkout_option,
                "value"          : product.value
            });
        });
    }
});