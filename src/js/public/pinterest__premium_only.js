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

    (function (wooptpm, $, undefined) {

        wooptpm.getPinterestProductData = function (product) {

            if (product.isVariation) {
                return {
                    product_name      : product.name,
                    product_variant_id: product.dyn_r_ids[wooptpmDataLayer.pixels.pinterest.dynamic_remarketing.id_type],
                    product_id        : wooptpmDataLayer.products[product.parentId].dyn_r_ids[wooptpmDataLayer.pixels.pinterest.dynamic_remarketing.id_type],
                    product_category  : product.category,
                    product_variant   : product.variant,
                    product_price     : product.price,
                    product_quantity  : product.quantity,
                    product_brand     : product.brand,
                }
            } else {
                return {
                    product_name    : product.name,
                    product_id      : product.dyn_r_ids[wooptpmDataLayer.pixels.pinterest.dynamic_remarketing.id_type],
                    product_category: product.category,
                    product_price   : product.price,
                    product_quantity: product.quantity,
                    product_brand   : product.brand,
                }
            }
        }

    }(window.wooptpm = window.wooptpm || {}, jQuery));

    jQuery(function () {

        if (wooptpm.objectExists(wooptpmDataLayer.pixels.pinterest)) {

            // AddToCart event
            jQuery(document).on('wooptpmAddToCart', function (event, product) {

                // console.log('firing Pinterest ads AddToCart event');
                // console.log(product);

                pintrk("track", "addtocart", {
                    "value"     : parseFloat(product.quantity * product.price),
                    "currency"  : product.currency,
                    "line_items": [wooptpm.getPinterestProductData(product)],
                });
            });

            // pageview event
            jQuery(document).on('wooptpmViewItem', function (event, product) {

                // console.log('firing Pinterest pageview event');
                // console.log(product);

                pintrk("track", "pagevisit", {
                    "currency"  : product.currency,
                    "line_items": [wooptpm.getPinterestProductData(product)],
                });
            });
        }
    })

    jQuery(window).on('load', function () {

        if (wooptpm.objectExists(wooptpmDataLayer.pixels.pinterest)) {

            wooptpmExists().then(function () {

                try {
                    if (wooptpmDataLayer.shop.page_type === 'product' && wooptpmDataLayer.shop.product_type !== 'variable' && wooptpm.getMainProductIdFromProductPage()) {

                        let product = wooptpm.getProductDataForViewItemEvent(wooptpm.getMainProductIdFromProductPage());

                        // console.log('pintrk PageVisit');
                        // console.log(product);

                        let productData = wooptpm.getPinterestProductData(product);

                        // console.log(productData);

                        pintrk("track", "pagevisit", {
                            "currency"  : product.currency,
                            "line_items": [productData],
                        });

                        // pintrk("track", "pagevisit");

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