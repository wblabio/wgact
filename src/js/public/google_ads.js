jQuery(function () {

    if (wooptpmDataLayer.pixels.google.ads.dynamic_remarketing.status) {

        // view_item_list event
        jQuery(document).on('wooptpmViewItemList', function (event, data) {

            // console.log('firing google select_content event');
            // alert('firing google select_content event');
            // console.log(data);

            gtag('event', 'view_item_list', {
                "send_to": wooptpmDataLayer.pixels.google.ads.conversionIds,
                "items"  : [{
                    "id"                      : data.dyn_r_ids[wooptpmDataLayer.pixels.google.ads.dynamic_remarketing.id_type],
                    "google_business_vertical": wooptpmDataLayer.pixels.google.ads.google_business_vertical,

                }]
            });
        });

        // add_to_cart event
        jQuery(document).on('wooptpmAddToCart', function (event, data) {

            // console.log('firing google ads add_to_cart event');
            // alert('firing google ads add_to_cart event');
            // console.log(data);
            // console.log(wooptpmDataLayer.pixels.google.ads.conversionIds);
            // console.log('dyn_r_id: ' + data.dyn_r_ids[wooptpmDataLayer.pixels.google.ads.dynamic_remarketing.id_type]);
            // console.log('dyn_r_id: ' + data.dyn_r_ids['gpf']);

            gtag("event", "add_to_cart", {
                "send_to": wooptpmDataLayer.pixels.google.ads.conversionIds,
                "value"  : data.quantity * data.price,
                "items"  : [{
                    "id"                      : data.dyn_r_ids[wooptpmDataLayer.pixels.google.ads.dynamic_remarketing.id_type],
                    "quantity"                : data.quantity,
                    "price"                   : data.price,
                    "google_business_vertical": wooptpmDataLayer.pixels.google.ads.google_business_vertical
                }]
            });
        });

        // select_content
        jQuery(document).on('wooptpmSelectContent', function (event, data) {

            // console.log('firing google ads select_content event');
            // alert('firing google ads select_content event');
            // console.log(data);

            gtag("event", "select_content", {
                "send_to"     : wooptpmDataLayer.pixels.google.ads.conversionIds,
                "content_type": "product",
                "items"       : [{
                        "id"                      : data.dyn_r_ids[wooptpmDataLayer.pixels.google.ads.dynamic_remarketing.id_type],
                        "quantity"                : data.quantity,
                        "name"                    : data.name,
                        "price"                   : data.price,
                        'google_business_vertical': wooptpmDataLayer.pixels.google.ads.google_business_vertical
                    }]
            });
        });
    }
})
