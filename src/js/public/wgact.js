wgact = function () {

    const wgactDeduper = {
        keyName          : '_wgact_order_ids',
        cookieExpiresDays: 365
    };

    function writeOrderIdToStorage(orderId, expireDays = 365) {

        // save the order ID in the browser storage

        if (!window.Storage) {
            let expiresDate = new Date();
            expiresDate.setDate(expiresDate.getDate() + wgactDeduper.cookieExpiresDays);

            let ids = [];
            if (checkCookie()) {
                ids = JSON.parse(getCookie(wgactDeduper.keyName));
            }

            if (!ids.includes(orderId)) {
                ids.push(orderId);
                document.cookie = wgactDeduper.keyName + '=' + JSON.stringify(ids) + ';expires=' + expiresDate.toUTCString();
            }

        } else {
            if (localStorage.getItem(wgactDeduper.keyName) === null) {
                let ids = [];
                ids.push(orderId);
                window.localStorage.setItem(wgactDeduper.keyName, JSON.stringify(ids));

            } else {
                let ids = JSON.parse(localStorage.getItem(wgactDeduper.keyName));
                if (!ids.includes(orderId)) {
                    ids.push(orderId);
                    window.localStorage.setItem(wgactDeduper.keyName, JSON.stringify(ids));
                }
            }
        }

        if (typeof wgactStoreOrderIdOnServer === 'function' && wgact_order_deduplication) {
            wgactStoreOrderIdOnServer(orderId);
        }
    }

    function getCookie(cname) {
        let name = cname + "=";
        let ca   = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) === 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }

    function checkCookie() {
        let key = getCookie(wgactDeduper.keyName);
        return key !== "";
    }

    function isOrderIdStored(orderId) {

        if (wgact_order_deduplication) {
            if (!window.Storage) {

                if (checkCookie()) {
                    let ids = JSON.parse(getCookie(wgactDeduper.keyName));
                    return ids.includes(orderId);
                } else {
                    return false;
                }
            } else {
                if (localStorage.getItem(wgactDeduper.keyName) !== null) {
                    let ids = JSON.parse(localStorage.getItem(wgactDeduper.keyName));
                    return ids.includes(orderId);
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    return {
        writeOrderIdToStorage: writeOrderIdToStorage,
        isOrderIdStored      : isOrderIdStored
    }

}();

// fire view_item_list on product page to add related, upsell and cross-sell items to the remarketing list
jQuery(function () {

    if (wooptpmDataLayer.pixels && wooptpmDataLayer.pixels.dynamic_remarketing && wooptpmDataLayer.shop.page_type === 'product') {

        // reduce wooptpmDataLayer.visible_products to only the ones displayed on the front-end

        for (const [key, value] of Object.entries(wooptpmDataLayer.visible_products)) {

            if( ! jQuery('.post-' + key)[0]){
                delete wooptpmDataLayer.visible_products[key];
            }
        }

        // create gtag object with all wooptpmDataLayer.visible_products and fire
        gtag('event', 'view_item_list', {
            "items": [get_view_item_products(wooptpmDataLayer.visible_products)]
        });
    }
});

// fire view_item_list on cart page to add related, upsell items to the remarketing list
jQuery(function () {

    if (wooptpmDataLayer.pixels && wooptpmDataLayer.pixels.dynamic_remarketing && wooptpmDataLayer.shop.page_type === 'cart') {

        // create gtag object with all wooptpmDataLayer.visible_products and fire
        gtag('event', 'view_item_list', {
            "items": [get_view_item_products(wooptpmDataLayer.upsell_products)]
        });
    }
});

function get_view_item_products(productList) {

    let data = [];

    for (const [key, value] of Object.entries(productList)) {

        data.push({
            'id'      : value['id'],
            'name'    : value['name'],
            'brand'   : value['brand'],
            'category': value['category'],
            // 'list_position': '', // probably doesn't make much sense on the product page
            'quantity': 1,
            'price'   : value['price'],
            // 'list_name'    : '' // probably doesn't make much sense on the product page
        });

    }
    // console.log(data);
    return data;
}

jQuery(function () {

    // remove_from_cart event

    jQuery(document).on('click', '.remove_from_cart_button, .remove', function (e) {

        let cartItemKey;

        if (wooptpmDataLayer['shop']['page_type'] === 'cart') {
            let href         = new URL(jQuery(this).attr('href'));
            let searchParams = new URLSearchParams(href.search);
            cartItemKey      = searchParams.get('remove_item');
            removeProductFromCartTrigger(cartItemKey);
        } else if (wooptpmDataLayer.cart_item_keys && wooptpmDataLayer.cart_item_keys[jQuery(this).data('cart_item_key')] !== undefined) {
            removeProductFromCartTrigger(jQuery(this).data('cart_item_key'));
        } else {
            removeProductFromCartTrigger(null, null, jQuery(this).data('product_id'));
        }
    });
});

function removeProductFromCartTrigger(cartItemKey, quantityToRemove = null, productId = null) {

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

    let data = {
        "id"  : productId.toString(),
        "name": wooptpmDataLayer['cart'][productId]['name'],
        // "list_name": wooptpmDataLayer['shop']['list_name'], // doesn't make sense on mini_cart
        "brand"   : wooptpmDataLayer['cart'][productId]['brand'],
        "category": wooptpmDataLayer['cart'][productId]['category'],
        // "variant": "Black",
        // "list_position": wooptpmDataLayer['cart'][productId]['position'], // doesn't make sense on mini_cart
        "quantity": quantity,
        "price"   : wooptpmDataLayer['cart'][productId]['price']
    };

    jQuery(document).trigger('wooptpmRemoveFromCart', data);

    if (quantityToRemove == null) {
        delete wooptpmDataLayer['cart'][productId];
        if (cartItemKey) {
            delete wooptpmDataLayer['cart_item_keys'][cartItemKey];
        }
    } else {
        wooptpmDataLayer['cart'][productId]['quantity'] = wooptpmDataLayer['cart'][productId]['quantity'] - quantity;
    }
}

jQuery(function () {

    // add_to_cart event

    jQuery(document).on('click', '.ajax_add_to_cart, .single_add_to_cart_button', function (e) {

        // console.log('test x');
        // alert('test');
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

function addProductToCart(productId, quantity, variationId = null) {

    // alert('productId: ' + productId + ' | qty: ' + quantity);

    let id = '';

    if (variationId !== null) {
        id = variationId;
    } else {
        id = productId;
    }

    let data = {
        "id"       : id.toString(),
        "name"     : wooptpmDataLayer['visible_products'][productId]['name'],
        "list_name": wooptpmDataLayer['shop']['list_name'], // maybe remove if in cart
        "brand"    : wooptpmDataLayer['visible_products'][productId]['brand'],
        "category" : wooptpmDataLayer['visible_products'][productId]['category'],
        // "variant": "Black",
        "list_position": wooptpmDataLayer['visible_products'][productId]['position'],
        "quantity"     : quantity,
        "price"        : wooptpmDataLayer['visible_products'][productId]['price']
    };

    jQuery(document).trigger('wooptpmAddToCart', data);

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

            let name      = jQuery(this).closest('.product');
            let classes   = name.attr('class');
            let regex     = /(?<=post-)\d+/gm;
            let productId = classes.match(regex)[0];

            let data = {
                "id"       : productId.toString(),
                "name"     : wooptpmDataLayer['visible_products'][productId]['name'],
                "list_name": wooptpmDataLayer['shop']['list_name'],
                "brand"    : wooptpmDataLayer['visible_products'][productId]['brand'],
                "category" : wooptpmDataLayer['visible_products'][productId]['category'],
                // "variant": "Black",
                "list_position": wooptpmDataLayer['visible_products'][productId]['position'],
                "quantity"     : 1,
                "price"        : wooptpmDataLayer['visible_products'][productId]['price']
            };

            jQuery(document).trigger('wooptpmSelectContent', data);
        });
    }
});

jQuery(function () {

    // begin_checkout event

    jQuery(document).one('click', '.checkout-button, .cart-checkout-button, .button.checkout', function (e) {

        jQuery(document).trigger('wooptpmBeginCheckout', getCartItems());
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


function isEmail(email) {
    // https://emailregex.com/
    let regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return regex.test(email);
}

function fireCheckoutOption(step, checkout_option = null, value = null) {

    let data = {
        'step': step,
        'checkout_option': checkout_option,
        'value': value
    };

    jQuery(document).trigger('wooptpmFireCheckoutOption', data);
}

jQuery(function () {

    // set_checkout_option event

    // track checkout option event: entered valid billing email
    jQuery(document).on('input', '#billing_email', function () {

        if (isEmail(jQuery(this).val())) {
            fireCheckoutOption(2);
        }
    });

    // track checkout option event: purchase click
    let payment_method_selected = false;

    jQuery(document).on('click', '.wc_payment_methods', function () {

        fireCheckoutOption(3, jQuery("input[name='payment_method']:checked").val());
        payment_method_selected = true;
    });

    // track checkout option event: purchase click
    jQuery(document).one('click', '#place_order', function () {

        if (payment_method_selected === false) {

            fireCheckoutOption(3, jQuery("input[name='payment_method']:checked").val());
        }

        fireCheckoutOption(4);
    });
});


jQuery(function () {

    // update cart event

    jQuery(document).on('click', "[name='update_cart']", function (e) {

        jQuery('.cart_item').each(function () {
            let href         = new URL(jQuery(this).find('.remove').attr('href'));
            let searchParams = new URLSearchParams(href.search);
            let cartItemKey  = searchParams.get('remove_item');
            // alert('cart_item_key: ' + cartItemKey);
            let productId    = wooptpmDataLayer['cart_item_keys'][cartItemKey]['id'];

            let quantity = jQuery(this).find('.qty').val();

            // alert ('quantity: ' + quantity);

            if (quantity == 0) {
                removeProductFromCartTrigger(cartItemKey);
            } else if (quantity < wooptpmDataLayer['cart'][productId]['quantity']) {
                removeProductFromCartTrigger(cartItemKey, wooptpmDataLayer['cart'][productId]['quantity'] - quantity);
            } else if (quantity > wooptpmDataLayer['cart'][productId]['quantity']) {
                addProductToCart(productId, quantity - wooptpmDataLayer['cart'][productId]['quantity']);
            }
        });
    });
});
