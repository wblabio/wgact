jQuery(function () {

    // select_content event
    jQuery(document).on('wooptpmSelectContent', function (event, data) {

        // console.log('firing google select_content event');
        // console.log(data);

        gtag('event', 'select_content', {
            "content_type": "product",
            "items"       : [
                {
                    "id"       : data.id,
                    "name"     : data.name,
                    "list_name": data.list_name, // doesn't make sense on mini_cart
                    "brand"    : data.brand,
                    "category" : data.category,
                    // "variant": data.variant,
                    "list_position": data.list_position, // doesn't make sense on mini_cart
                    "quantity"     : data.quantity,
                    "price"        : data.price
                }
            ]
        });
    });

    // remove_from_cart event
    jQuery(document).on('wooptpmRemoveFromCart', function (event, data) {

        // console.log('firing google remove_from_cart event');
        // console.log(data);

        gtag('event', 'remove_from_cart', {
            "items": [
                {
                    "id"  : data.id,
                    "name": data.name,
                    // "list_name": data.list_name, // doesn't make sense on mini_cart
                    "brand"   : data.brand,
                    "category": data.category,
                    // "variant": data.variant,
                    // "list_position": data.list_position, // doesn't make sense on mini_cart
                    "quantity": data.quantity,
                    "price"   : data.price
                }
            ]
        });
    });

    // add_to_cart event
    jQuery(document).on('wooptpmAddToCart', function (event, data) {

        // console.log('firing google add_to_cart event');
        // console.log(data);

        gtag('event', 'add_to_cart', {
            "items": [
                {
                    "id"       : data.id,
                    "name"     : data.name,
                    "list_name": data.list_name, // doesn't make sense on mini_cart
                    "brand"    : data.brand,
                    "category" : data.category,
                    // "variant": data.variant,
                    "list_position": data.list_position, // doesn't make sense on mini_cart
                    "quantity"     : data.quantity,
                    "price"        : data.price
                }
            ]
        });
    });

    // begin_checkout event
    jQuery(document).on('wooptpmBeginCheckout', function (event, data) {

        // console.log('firing google begin_checkout event');
        // console.log(data);

        gtag('event', 'begin_checkout', {
            "items": data
        });
    });

    // set_checkout_option event
    jQuery(document).on('wooptpmFireCheckoutOption', function (event, data) {

        // console.log('firing google set_checkout_option event');
        // console.log(data);

        gtag('event', 'set_checkout_option', {
            "checkout_step"  : data.step,
            "checkout_option": data.checkout_option,
            "value"          : data.value
        });
    });
});