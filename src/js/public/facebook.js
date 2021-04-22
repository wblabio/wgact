jQuery(function () {

        if (wooptpm.objectExists(wooptpmDataLayer.pixels.facebook)) {

            // add_to_cart event
            jQuery(document).on('wooptpmAddToCart', function (event, product) {

                // console.log('firing facebook ads AddToCart event');
                // console.log(data);

                fbq('track', 'AddToCart', {
                    'content_type': 'product',
                    'content_name': product.name,
                    'content_ids' : product.dyn_r_ids[wooptpmDataLayer.pixels.facebook.dynamic_remarketing.id_type],
                    'value'       : product.quantity * product.price,
                    'currency'    : product.currency,
                });
            });


        }
})

jQuery(window).on('load', function () {

    if (wooptpm.objectExists(wooptpmDataLayer.pixels.facebook)) {
        wooptpmExists().then(function () {

            try {
                if (wooptpmDataLayer.shop.page_type === 'product' && wooptpm.getMainProductIdFromProductPage()) {

                    let product = wooptpm.getProductDataForViewItemEvent(wooptpm.getMainProductIdFromProductPage());

                    // console.log('fbq ViewContent');
                    // console.log(product);

                    fbq("track", "ViewContent", {
                        "content_type"    : "product",
                        "content_name"    : product.name,
                        "content_category": product.category,
                        "content_ids"     : product.dyn_r_ids[wooptpmDataLayer.pixels.google.ads.dynamic_remarketing.id_type],
                        "currency"        : wooptpmDataLayer.shop.currency,
                        "value"           : product.price,
                    });

                } else if (wooptpmDataLayer.shop.page_type === 'search') {

                    fbq("track", "Search");
                }
            } catch (e) {
                console.log(e);
            }
        })
    }
});