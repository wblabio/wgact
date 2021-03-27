// add_to_cart event
jQuery(document).on('wooptpmAddToCart', function (event, data) {

    if (wooptpmDataLayer.pixels.google.ads.dynamic_remarketing) {

        // console.log('firing google ads add_to_cart event');
        // alert('firing google ads add_to_cart event');
        // console.log(data);
        // console.log(wooptpmDataLayer.pixels.google.ads.conversionIds);

        gtag('event', 'add_to_cart', {
            'send_to': wooptpmDataLayer.pixels.google.ads.conversionIds,
            'value'  : data.quantity * data.price,
            'items'  : {
                'id'                      : data.id,
                'quantity'                : data.quantity,
                'price'                   : data.price,
                'google_business_vertical': wooptpmDataLayer.pixels.google.ads.google_business_vertical
            }
        });
    }
});

jQuery(document).on('wooptpmSelectContent', function (event, data) {

    if (wooptpmDataLayer.pixels.google.ads.dynamic_remarketing) {

        // console.log('firing google ads select_content event');
        // alert('firing google ads select_content event');
        // console.log(data);

        gtag('event', 'select_content', {
            "send_to"     : wooptpmDataLayer.pixels.google.ads.conversionIds,
            "content_type": "product",
            "items"       : [
                {
                    "id"                      : data.id,
                    "quantity"                : data.quantity,
                    "name"                    : data.name,
                    "price"                   : data.price,
                    'google_business_vertical': wooptpmDataLayer.pixels.google.ads.google_business_vertical
                }
            ]
        });
    }
});