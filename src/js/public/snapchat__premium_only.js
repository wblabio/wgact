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

                snaptr('track','ADD_CART')
            });

            // VIEW_CONTENT event
            jQuery(document).on('wooptpmViewItem', function (event, product) {

                // console.log('firing Snapchat ads VIEW_CONTENT event');
                // console.log(product);

                snaptr('track','VIEW_CONTENT')
            });
        }
    })

    jQuery(window).on('load', function () {

        if (wooptpm.objectExists(wooptpmDataLayer.pixels.snapchat)) {

            wooptpmExists().then(function () {

                try {
                    if (wooptpmDataLayer.shop.page_type === 'product' && wooptpmDataLayer.shop.product_type !== 'variable' && wooptpm.getMainProductIdFromProductPage()) {

                        let product = wooptpm.getProductDataForViewItemEvent(wooptpm.getMainProductIdFromProductPage());

                        // console.log('pintrk PageVisit');
                        // console.log(product);

                        snaptr('track','VIEW_CONTENT')

                    } else if (wooptpmDataLayer.shop.page_type === 'search') {

                        let urlParams = new URLSearchParams(window.location.search);

                        pintrk("track", "search", {
                            "search_query": urlParams.get('s'),
                        });

                    } else if (wooptpmDataLayer.shop.page_type === 'product_category') {

                        pintrk("track", "viewcategory");
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