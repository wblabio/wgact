// add_to_cart event
jQuery(document).on('wooptpmAddToCart', function (event, data) {

    // console.log('firing facebook ads AddToCart event');
    // alert('firing facebook ads AddToCart event');
    // console.log(data);

    fbq('track', 'AddToCart', {
        'content_type': 'product',
        'content_name': data.name,
        'content_ids' : data.id,
        'value'       : data.quantity * data.price,
        'currency'    : data.currency,
    });
});