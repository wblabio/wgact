jQuery(function () {

    if (wooptpmDataLayer.pixels.google.ads.dynamic_remarketing.status) {

        // view_item_list event
        jQuery(document).on('wooptpmViewItemList', function (event, product) {

            // console.log('firing google select_content event');
            // alert('firing google select_content event');
            // console.log(product);

            gtag('event', 'view_item_list', {
                "send_to": wooptpmDataLayer.pixels.google.ads.conversionIds,
                "items"  : [{
                    "id"                      : product.dyn_r_ids[wooptpmDataLayer.pixels.google.ads.dynamic_remarketing.id_type],
                    "google_business_vertical": wooptpmDataLayer.pixels.google.ads.google_business_vertical,

                }]
            });
        });

        // add_to_cart event
        jQuery(document).on('wooptpmAddToCart', function (event, product) {

            // console.log('firing google ads add_to_cart event');
            // alert('firing google ads add_to_cart event');
            // console.log(product);
            // console.log(wooptpmDataLayer.pixels.google.ads.conversionIds);
            // console.log('dyn_r_id: ' + product.dyn_r_ids[wooptpmDataLayer.pixels.google.ads.dynamic_remarketing.id_type]);
            // console.log('dyn_r_id: ' + product.dyn_r_ids['gpf']);

            gtag("event", "add_to_cart", {
                "send_to": wooptpmDataLayer.pixels.google.ads.conversionIds,
                "value"  : product.quantity * product.price,
                "items"  : [{
                    "id"                      : product.dyn_r_ids[wooptpmDataLayer.pixels.google.ads.dynamic_remarketing.id_type],
                    "quantity"                : product.quantity,
                    "price"                   : product.price,
                    "google_business_vertical": wooptpmDataLayer.pixels.google.ads.google_business_vertical
                }]
            });
        });

        // select_content
        // there is no select_content event that is processed by Google Ads: https://support.google.com/google-ads/answer/7305793?hl=en
        // jQuery(document).on('wooptpmSelectContentGaUa', function (event, product) {
        //
        //     // console.log('firing google ads select_content event');
        //     // alert('firing google ads select_content event');
        //     // console.log(product);
        //
        //     gtag("event", "select_content", {
        //         "send_to"     : wooptpmDataLayer.pixels.google.ads.conversionIds,
        //         "content_type": "product",
        //         "items"       : [{
        //                 "id"                      : product.dyn_r_ids[wooptpmDataLayer.pixels.google.ads.dynamic_remarketing.id_type],
        //                 "quantity"                : product.quantity,
        //                 "name"                    : product.name,
        //                 "price"                   : product.price,
        //                 'google_business_vertical': wooptpmDataLayer.pixels.google.ads.google_business_vertical
        //             }]
        //     });
        // });

        // view_item event
        jQuery(document).on('wooptpmViewItem', function (event, product) {

            // console.log('firing google ads view_item event');
            // alert('firing google ads view_item event');
            // console.log(product);
            // console.log(wooptpmDataLayer.pixels.google.ads.conversionIds);
            // console.log('dyn_r_id: ' + product.dyn_r_ids[wooptpmDataLayer.pixels.google.ads.dynamic_remarketing.id_type]);
            // console.log('dyn_r_id: ' + product.dyn_r_ids['gpf']);

            gtag("event", "view_item", {
                "send_to": wooptpmDataLayer.pixels.google.ads.conversionIds,
                "value"  : product.quantity * product.price,
                "items"  : [{
                    "id"                      : product.dyn_r_ids[wooptpmDataLayer.pixels.google.ads.dynamic_remarketing.id_type],
                    "quantity"                : 1,
                    "price"                   : product.price,
                    "google_business_vertical": wooptpmDataLayer.pixels.google.ads.google_business_vertical
                }]
            });
        });
    }
})

jQuery(window).on('load', function () {

    wooptpmExists().then(function () {

        if (wooptpmDataLayer.shop.page_type === 'product' && wooptpm.getMainProductIdFromProductPage()) {

            // console.log('productId: ' + wooptpm.getMainProductIdFromProductPage());

            let product = wooptpm.getProductDataForViewItemEvent(wooptpm.getMainProductIdFromProductPage());

            gtag("event", "view_item", {
                "send_to": wooptpmDataLayer.pixels.google.ads.conversionIds,
                "value"  : 1 * product.price,
                "items"  : [{
                    "id"                      : product.dyn_r_ids[wooptpmDataLayer.pixels.google.ads.dynamic_remarketing.id_type],
                    "quantity"                : 1,
                    "price"                   : product.price,
                    "google_business_vertical": wooptpmDataLayer.pixels.google.ads.google_business_vertical
                }]
            });
        }
    })
});


