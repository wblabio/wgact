jQuery(function () {

    if(wooptpmDataLayer.pixels.facebook){

        // add_to_cart event
        jQuery(document).on('wooptpmAddToCart', function (event, data) {

            // console.log('firing facebook ads AddToCart event');
            // alert('firing facebook ads AddToCart event');
            // console.log(data);

            fbq('track', 'AddToCart', {
                'content_type': 'product',
                'content_name': data.name,
                'content_ids' : data.dyn_r_ids[wooptpmDataLayer.pixels.facebook.dynamic_remarketing.id_type],
                'value'       : data.quantity * data.price,
                'currency'    : data.currency,
            });
        });
    }
})