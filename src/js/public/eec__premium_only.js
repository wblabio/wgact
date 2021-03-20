jQuery(function () {

    get_cart_items_from_back_end();
});

function get_cart_items_from_back_end() {
    // get all cart items from the backend

    let data = {
        'action': 'wooptpm_get_cart_items',
    };

    jQuery.ajax(
        {
            type    : "get",
            dataType: "json",
            url     : ajax_object.ajax_url,
            data    : data,
            success : function (cart_items) {
                // save all cart items into wooptpmDataLayer
                wooptpmDataLayer['cart']           = cart_items['cart'];
                wooptpmDataLayer['cart_item_keys'] = cart_items['cart_item_keys'];

            }
        });
}

jQuery(function () {

    // select_content event

    // only allow the script to be fired on the following pages
    let allowed_pages = ['shop', 'product_category', 'product_tag', 'search'];

    if (allowed_pages.includes(wooptpmDataLayer['shop']['page_type'])) {

        jQuery('.product').on('click', function (e) {

            // try {
            let name      = jQuery(this).closest('.product');
            let classes   = name.attr('class');
            let regex     = /(?<=post-)\d+/gm;
            let productId = classes.match(regex)[0];

            gtag('event', 'select_content', {
                "content_type": "product",
                "items"       : [
                    {
                        "id"       : productId.toString(),
                        "name"     : wooptpmDataLayer['visible_products'][productId]['name'],
                        "list_name": wooptpmDataLayer['shop']['list_name'],
                        "brand"    : wooptpmDataLayer['visible_products'][productId]['brand'],
                        "category" : wooptpmDataLayer['visible_products'][productId]['category'],
                        // "variant": "Black",
                        "list_position": wooptpmDataLayer['visible_products'][productId]['position'],
                        "quantity"     : 1,
                        "price"        : wooptpmDataLayer['visible_products'][productId]['price']
                    }
                ]
            });
            // } catch (e) {
            //     console.log('woopt-pm error: couldn\'t execute select_content script');
            //     console.log(e);
            // }
        });
    }
});

jQuery(function () {

    // add_to_cart event

    jQuery(document).on('click', '.ajax_add_to_cart, .single_add_to_cart_button', function (e) {

        // console.log('test x');
        // alert('test');
        // try {
        if (wooptpmDataLayer.shop.product_type !== 'grouped') {

            let productId = null;

            let quantity = 1;

            if (wooptpmDataLayer['shop']['page_type'] === 'product') {
                quantity = jQuery('.input-text.qty').val();
            }

            if (wooptpmDataLayer['shop']['page_type'] !== 'product') {
                productId = jQuery(this).data('product_id');
                addProductToCart(productId, quantity);
            } else if (wooptpmDataLayer['shop']['product_type'] === 'variable') {
                productId       = jQuery("[name='product_id']").val();
                let variationId = jQuery("[name='variation_id']").val();
                addProductToCart(productId, quantity, variationId);

            } else {
                productId = jQuery(this).val();
                addProductToCart(productId, quantity);
            }
        } else {
            jQuery('.woocommerce-grouped-product-list-item').each(function () {
                let quantity  = jQuery(this).find('.input-text.qty').val();
                let classes   = jQuery(this).attr('class');
                let regex     = /(?<=post-)\d+/gm;
                let productId = classes.match(regex)[0];
                addProductToCart(productId, quantity);
            });
        }

        // } catch (e) {
        //     console.log('woopt-pm error: couldn\'t execute add_to_cart script');
        //     console.log(e);
        // }
    });

    // if someone clicks anywhere on a custom /?add-to-cart=123 link
    // trigger the add to cart event
    jQuery(document).one('click', function (e) {

        if (jQuery(this)[0].URL) {

            let href         = new URL(jQuery(this)[0].URL);
            let searchParams = new URLSearchParams(href.search);

            if (searchParams.has('add-to-cart')) {

                let productId = searchParams.get('add-to-cart');
                addProductToCart(productId, 1);
            }
        }
    });
});

jQuery(function () {

    // remove_from_cart event

    jQuery(document).on('click', '.remove_from_cart_button, .remove', function (e) {

        // console.log('test x');
        // alert('test');
        // try {
        // let productId = jQuery(this).data('product_id');

        let cartItemKey;

        if (wooptpmDataLayer['shop']['page_type'] === 'cart') {
            let href         = new URL(jQuery(this).attr('href'));
            let searchParams = new URLSearchParams(href.search);
            cartItemKey      = searchParams.get('remove_item');
            removeProductFromCart(cartItemKey);
        } else if (wooptpmDataLayer.cart_item_keys && wooptpmDataLayer.cart_item_keys[jQuery(this).data('cart_item_key')] !== undefined) {
            removeProductFromCart(jQuery(this).data('cart_item_key'));
        } else {
            removeProductFromCart(null, null, jQuery(this).data('product_id'));
        }

        // } catch (e) {
        //     console.log('woopt-pm error: couldn\'t execute remove_from_cart script');
        //     console.log(e);
        // }
    });
});

jQuery(function () {

    // update cart event

    jQuery(document).on('click', "[name='update_cart']", function (e) {

        // try {

        jQuery('.cart_item').each(function () {
            let href         = new URL(jQuery(this).find('.remove').attr('href'));
            let searchParams = new URLSearchParams(href.search);
            let cartItemKey  = searchParams.get('remove_item');
            // alert('cart_item_key: ' + cartItemKey);
            let productId    = wooptpmDataLayer['cart_item_keys'][cartItemKey]['id'];

            let quantity = jQuery(this).find('.qty').val();

            // alert ('quantity: ' + quantity);

            if (quantity == 0) {
                removeProductFromCart(cartItemKey);
            } else if (quantity < wooptpmDataLayer['cart'][productId]['quantity']) {
                removeProductFromCart(cartItemKey, wooptpmDataLayer['cart'][productId]['quantity'] - quantity);
            } else if (quantity > wooptpmDataLayer['cart'][productId]['quantity']) {
                addProductToCart(productId, quantity - wooptpmDataLayer['cart'][productId]['quantity']);
            }
        });
        // } catch (e) {
        //     console.log('woopt-pm error: couldn\'t execute update cart script');
        //     console.log(e);
        // }
    });
});

function removeProductFromCart(cartItemKey, quantityToRemove = null, productId = null) {

    if (productId == null) {
        productId = wooptpmDataLayer['cart_item_keys'][cartItemKey]['id'];
    }

    let quantity;


    if (quantityToRemove == null) {
        quantity = wooptpmDataLayer['cart'][productId]['quantity'];
    } else {
        quantity = quantityToRemove;
    }

    // alert ('product_id: ' + productId + ' | qty: ' + quantity);

    gtag('event', 'remove_from_cart', {
        "items": [
            {
                "id"  : productId.toString(),
                "name": wooptpmDataLayer['cart'][productId]['name'],
                // "list_name": wooptpmDataLayer['shop']['list_name'], // doesn't make sense on mini_cart
                "brand"   : wooptpmDataLayer['cart'][productId]['brand'],
                "category": wooptpmDataLayer['cart'][productId]['category'],
                // "variant": "Black",
                // "list_position": wooptpmDataLayer['cart'][productId]['position'], // doesn't make sense on mini_cart
                "quantity": quantity,
                "price"   : wooptpmDataLayer['cart'][productId]['price']
            }
        ]
    });

    if (quantityToRemove == null) {
        delete wooptpmDataLayer['cart'][productId];
        if (cartItemKey) {
            delete wooptpmDataLayer['cart_item_keys'][cartItemKey];
        }
    } else {
        wooptpmDataLayer['cart'][productId]['quantity'] = wooptpmDataLayer['cart'][productId]['quantity'] - quantity;
    }
}

function addProductToCart(productId, quantity, variationId = null) {

    // alert('productId: ' + productId + ' | qty: ' + quantity);

    let id = '';

    if (variationId !== null) {
        id = variationId;
    } else {
        id = productId;
    }

    gtag('event', 'add_to_cart', {
        "items": [
            {
                "id"       : id.toString(),
                "name"     : wooptpmDataLayer['visible_products'][productId]['name'],
                "list_name": wooptpmDataLayer['shop']['list_name'], // maybe remove if in cart
                "brand"    : wooptpmDataLayer['visible_products'][productId]['brand'],
                "category" : wooptpmDataLayer['visible_products'][productId]['category'],
                // "variant": "Black",
                "list_position": wooptpmDataLayer['visible_products'][productId]['position'],
                "quantity"     : quantity,
                "price"        : wooptpmDataLayer['visible_products'][productId]['price']
            }
        ]
    });

    // add product to cart wooptpmDataLayer['cart']

    // if the product already exists in the object, only add the additional quantity
    // otherwise create that product object in the wooptpmDataLayer['cart']
    if (wooptpmDataLayer['cart'] !== undefined && wooptpmDataLayer['cart'][id] !== undefined) {
        wooptpmDataLayer['cart'][id]['quantity'] = wooptpmDataLayer['cart'][id]['quantity'] + quantity;
    } else {

        // Object.assign(wooptpmDataLayer['cart'], {
        //     id: {
        //         'id'      : id,
        //         'name'    : wooptpmDataLayer['visible_products'][productId]['name'],
        //         'brand'   : wooptpmDataLayer['visible_products'][productId]['brand'],
        //         'category': wooptpmDataLayer['visible_products'][productId]['category'],
        //         'quantity': quantity,
        //         'price'   : wooptpmDataLayer['visible_products'][productId]['price']
        //     }
        // });

        if (!wooptpmDataLayer.cart) {

            wooptpmDataLayer['cart'] = {
                [id]: {
                    'id'      : id,
                    'name'    : wooptpmDataLayer['visible_products'][productId]['name'],
                    'brand'   : wooptpmDataLayer['visible_products'][productId]['brand'],
                    'category': wooptpmDataLayer['visible_products'][productId]['category'],
                    'quantity': quantity,
                    'price'   : wooptpmDataLayer['visible_products'][productId]['price']
                }
            };

        } else {

            wooptpmDataLayer.cart[id] = {
                'id'      : id,
                'name'    : wooptpmDataLayer['visible_products'][productId]['name'],
                'brand'   : wooptpmDataLayer['visible_products'][productId]['brand'],
                'category': wooptpmDataLayer['visible_products'][productId]['category'],
                'quantity': quantity,
                'price'   : wooptpmDataLayer['visible_products'][productId]['price']
            };
        }
    }
}

jQuery(function () {

    // begin_checkout event

    jQuery(document).one('click', '.checkout-button, .cart-checkout-button, .button.checkout', function (e) {

        gtag('event', 'begin_checkout', {
            "items": getCartItems()
        });
    });
});

function getCartItems() {
    let data = [];

    for (const [productId, product] of Object.entries(wooptpmDataLayer.cart)) {

        data.push({
            'id'  : product.id,
            'name': product.name,
            // 'list_name': '',
            'brand'   : product.brand,
            'category': product.category,
            // 'variant'      : product.variant,
            // 'list_position': 1,
            'quantity': product.quantity,
            'price'   : product.price
        });
    }
    return data;
}