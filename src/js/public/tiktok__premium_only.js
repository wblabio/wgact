if (typeof varExists !== "function") {
    function varExists(varName) {
        return new Promise(function (resolve, reject) {
            (function waitForVar() {
                if (typeof window[varName] !== 'undefined') return resolve();
                setTimeout(waitForVar, 30);
            })();
        });
    }
}

varExists('jQuery').then(function () {

    jQuery(function () {

        if (wooptpm.objectExists(wooptpmDataLayer.pixels.tiktok)) {

            // AddToCart event
            jQuery(document).on('wooptpmAddToCart', function (event, product) {

                // console.log('firing TikTok ads AddToCart event');
                // console.log(product);

                ttq.track('AddToCart', {
                    content_id: product.dyn_r_ids[wooptpmDataLayer.pixels.tiktok.dynamic_remarketing.id_type],
                    content_type: 'product',
                    content_name: product.name,
                    quantity: product.quantity,
                    value: product.price,
                    currency: product.currency,
                });
            });

            // VIEW_CONTENT event
            jQuery(document).on('wooptpmViewItem', function (event, product) {

                // console.log('firing TikTok ads VIEW_CONTENT event');
                // console.log(product);

                ttq.track('VIEW_CONTENT', {
                    content_id: product.dyn_r_ids[wooptpmDataLayer.pixels.tiktok.dynamic_remarketing.id_type],
                    content_type: 'product',
                    content_name: product.name,
                    quantity: product.quantity,
                    value: product.price,
                    currency: product.currency,
                });
            });
        }
    })

    jQuery(window).on('load', function () {

        if (wooptpm.objectExists(wooptpmDataLayer.pixels.tiktok)) {

            wooptpmExists().then(function () {

                try {
                    if (wooptpmDataLayer.shop.page_type === 'product' && wooptpmDataLayer.shop.product_type !== 'variable' && wooptpm.getMainProductIdFromProductPage()) {

                        let product = wooptpm.getProductDataForViewItemEvent(wooptpm.getMainProductIdFromProductPage());

                        // console.log('ttq.track PageVisit');
                        // console.log(product);

                        ttq.track('VIEW_CONTENT', {
                            content_id: product.dyn_r_ids[wooptpmDataLayer.pixels.tiktok.dynamic_remarketing.id_type],
                            content_type: 'product',
                            content_name: product.name,
                            quantity: product.quantity,
                            value: product.price,
                            currency: product.currency,
                        });

                    }
                } catch (e) {
                    console.log(e);
                }
            })
        }
    });

}).catch(function () {
    console.log('object couldn\'t be loaded');
})