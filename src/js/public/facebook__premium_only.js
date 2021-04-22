jQuery(function () {

    if (wooptpm.objectExists(wooptpmDataLayer.pixels.facebook)) {

        // add_to_cart event
        jQuery(document).on('wooptpmAddToWishlist', function (event, product) {

            // console.log('firing facebook ads AddToWishlist event');
            // console.log(data);

            fbq("track", "AddToWishlist", {
                "content_name"    : product.name,
                "content_category": product.category,
                "content_ids"     : product.dyn_r_ids[wooptpmDataLayer.pixels.facebook.dynamic_remarketing.id_type],
                // "contents"          : "",
                "currency": product.currency,
                "value"   : product.price,
            });
        });

        jQuery(document).on('wooptpmBeginCheckout', function (event) {

            // console.log('firing facebook ads InitiateCheckout event');
            // console.log(data);

            fbq("track", "InitiateCheckout");
        });

    }
})