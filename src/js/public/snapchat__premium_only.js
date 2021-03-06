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

        if (wooptpm.objectExists(wooptpmDataLayer.pixels.snapchat)) {

            // AddToCart event
            jQuery(document).on('wooptpmAddToCart', function (event, product) {

                // console.log('firing Snapchat ads ADD_CART event');
                // console.log(product);

                snaptr('track', 'ADD_CART', {
                    'item_ids': [product.dyn_r_ids[wooptpmDataLayer.pixels.snapchat.dynamic_remarketing.id_type]],
                });
            });

            // VIEW_CONTENT event
            jQuery(document).on('wooptpmViewItem', function (event, product) {

                // console.log('firing Snapchat ads VIEW_CONTENT event');
                // console.log(product);

                snaptr('track', 'VIEW_CONTENT', {
                    'item_ids': [product.dyn_r_ids[wooptpmDataLayer.pixels.snapchat.dynamic_remarketing.id_type]],
                });
            });
        }
    })

    jQuery(window).on('load', function () {

        if (wooptpm.objectExists(wooptpmDataLayer.pixels.snapchat)) {

            wooptpmExists().then(function () {

                try {
                    if (wooptpmDataLayer.shop.page_type === 'product' && wooptpmDataLayer.shop.product_type !== 'variable' && wooptpm.getMainProductIdFromProductPage()) {

                        let product = wooptpm.getProductDataForViewItemEvent(wooptpm.getMainProductIdFromProductPage());

                        // console.log('snaptr VIEW_CONTENT');
                        // console.log(product);

                        snaptr('track', 'VIEW_CONTENT', {
                            'item_ids': [product.dyn_r_ids[wooptpmDataLayer.pixels.snapchat.dynamic_remarketing.id_type]],
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