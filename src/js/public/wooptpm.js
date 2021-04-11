(function (wooptpm, $, undefined) {
    const wgactDeduper = {
        keyName          : '_wgact_order_ids',
        cookieExpiresDays: 365
    };

    // wooptpm.loadPageProductsFromBackend = function () {
    //
    //     // collect all products on page
    //     let productList = wooptpm.collectProductsFromPage();
    //     // compare and remove the products which are already in the data layer
    //     // get the product from the back-end
    //     // save the products in the data layer
    // }
    //
    // wooptpm.collectProductsFromPage = function () {
    //
    //     // get all add to cart buttons with /add-to-cart links
    // }

    wooptpm.writeOrderIdToStorage = function (orderId, expireDays = 365) {

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

        if (typeof wooptpm.storeOrderIdOnServer === 'function' && wooptpmDataLayer.orderDeduplication) {
            wooptpm.storeOrderIdOnServer(orderId);
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

    wooptpm.isOrderIdStored = function (orderId) {

        if (wooptpmDataLayer.orderDeduplication) {
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

    wooptpm.isEmail = function (email) {
        // https://emailregex.com/
        let regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return regex.test(email);
    }

    wooptpm.removeProductFromCart = function (cartItemKey, quantityToRemove = null, productId = null) {

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
            "id"       : productId.toString(),
            "dyn_r_ids": wooptpmDataLayer['cart'][productId]['dyn_r_ids'],
            "name"     : wooptpmDataLayer['cart'][productId]['name'],
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

    // add_to_cart
    wooptpm.addProductToCart = function (productId, quantity, variationId = null) {

        // alert('productId: ' + productId + ' | qty: ' + quantity);

        let id = '';

        if (wooptpmDataLayer.general.variationsOutput  && variationId !== null) {
            id = variationId;
        } else {
            id = productId;
        }

        let data = {
            "id"       : id.toString(),
            "dyn_r_ids": wooptpmDataLayer['products'][id]['dyn_r_ids'],
            "name"     : wooptpmDataLayer['products'][id]['name'],
            "list_name": wooptpmDataLayer['shop']['list_name'], // maybe remove if in cart
            "brand"    : wooptpmDataLayer['products'][id]['brand'],
            "category" : wooptpmDataLayer['products'][id]['category'],
            // "variant": "Black",
            "list_position": wooptpmDataLayer['products'][id]['position'],
            "quantity"     : quantity,
            "price"        : wooptpmDataLayer['products'][id]['price'],
            "currency"     : wooptpmDataLayer.shop.currency
        };

        jQuery(document).trigger('wooptpmAddToCart', data);

        // add product to cart wooptpmDataLayer['cart']

        // if the product already exists in the object, only add the additional quantity
        // otherwise create that product object in the wooptpmDataLayer['cart']
        if (wooptpmDataLayer['cart'] !== undefined && wooptpmDataLayer['cart'][id] !== undefined) {
            wooptpmDataLayer['cart'][id]['quantity'] = wooptpmDataLayer['cart'][id]['quantity'] + quantity;
        } else {

            if (!wooptpmDataLayer.cart) {

                wooptpmDataLayer['cart'] = {
                    [id]: {
                        'id'       : id,
                        'dyn_r_ids': wooptpmDataLayer['products'][id]['dyn_r_ids'],
                        'name'     : wooptpmDataLayer['products'][id]['name'],
                        'brand'    : wooptpmDataLayer['products'][id]['brand'],
                        'category' : wooptpmDataLayer['products'][id]['category'],
                        'quantity' : quantity,
                        'price'    : wooptpmDataLayer['products'][id]['price']
                    }
                };

            } else {

                wooptpmDataLayer.cart[id] = {
                    'id'       : id,
                    'dyn_r_ids': wooptpmDataLayer['products'][id]['dyn_r_ids'],
                    'name'     : wooptpmDataLayer['products'][id]['name'],
                    'brand'    : wooptpmDataLayer['products'][id]['brand'],
                    'category' : wooptpmDataLayer['products'][id]['category'],
                    'quantity' : quantity,
                    'price'    : wooptpmDataLayer['products'][id]['price']
                };
            }
        }
    }

    wooptpm.getCartItemsFromBackEnd = function () {
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

    wooptpm.fireCheckoutOption = function (step, checkout_option = null, value = null) {

        let data = {
            'step'           : step,
            'checkout_option': checkout_option,
            'value'          : value
        };

        jQuery(document).trigger('wooptpmFireCheckoutOption', data);
    }


    wooptpm.getPostIdFromString = function (string) {
        return string.match(/(post-)(\d+)/)[2];
    }

    wooptpm.triggerViewItemList = function (productId) {

        let data = {
            "id"       : productId.toString(),
            "dyn_r_ids": wooptpmDataLayer['products'][productId]['dyn_r_ids'],
            "name"     : wooptpmDataLayer['products'][productId]['name'],
            "list_name": wooptpmDataLayer['shop']['list_name'], // maybe remove if in cart
            "brand"    : wooptpmDataLayer['products'][productId]['brand'],
            "category" : wooptpmDataLayer['products'][productId]['category'],
            // "variant": "Black",
            "list_position": wooptpmDataLayer['products'][productId]['position'],
            "quantity"     : 1,
            "price"        : wooptpmDataLayer['products'][productId]['price'],
            "currency"     : wooptpmDataLayer.shop.currency
        };

        jQuery(document).trigger('wooptpmViewItemList', data);
    }

    wooptpm.viewItemListTriggerTestMode = function (target) {

        jQuery(target).css({"position": "relative"});
        jQuery(target).append('<div id="viewItemListTriggerOverlay"></div>')
        jQuery(target).find('#viewItemListTriggerOverlay').css({
            "z-index"         : "10",
            "display"         : "block",
            "position"        : "absolute",
            "height"          : "100%",
            "top"             : "0",
            "left"            : "0",
            "right"           : "0",
            "opacity"         : wooptpmDataLayer.viewItemListTrigger.opacity,
            "background-color": wooptpmDataLayer.viewItemListTrigger.backgroundColor,
        })
    }

    let timeouts = {};

    wooptpm.observerCallback = function (entries, observer) {

        entries.forEach((entry) => {
            let elementId = jQuery(entry.target).data('ioid');
            let productId = jQuery(entry.target).find('.add_to_cart_button, .product_type_grouped').data('product_id');

            if (entry.isIntersecting) {

                timeouts[elementId] = setTimeout(() => {
                    wooptpm.triggerViewItemList(productId);
                    if (wooptpmDataLayer.viewItemListTrigger.testMode) wooptpm.viewItemListTriggerTestMode(entry.target);
                    if (wooptpmDataLayer.viewItemListTrigger.repeat === false) observer.unobserve(entry.target);
                }, wooptpmDataLayer.viewItemListTrigger.timeout)

            } else {

                clearTimeout(timeouts[elementId])
                if (wooptpmDataLayer.viewItemListTrigger.testMode) jQuery(entry.target).find('#viewItemListTriggerOverlay').remove();
            }
        });
    }

    // return {
    // writeOrderIdToStorage  : writeOrderIdToStorage,
    // isOrderIdStored        : isOrderIdStored,
    // isEmail                : isEmail,
    // removeProductFromCart  : removeProductFromCart,
    // getViewItemProducts    : getViewItemProducts,
    // addProductToCart       : addProductToCart,
    // getCartItemsFromBackEnd: getCartItemsFromBackEnd,
    // fireCheckoutOption     : fireCheckoutOption,
    // getCartItems           : getCartItems
    // }

}(window.wooptpm = window.wooptpm || {}, jQuery));


jQuery(function () {
// jQuery(window).on('load', function () {

    // fire view_item_list only on products that have become visible
    const io = new IntersectionObserver(wooptpm.observerCallback, {threshold: wooptpmDataLayer.viewItemListTrigger.threshold});

    let ioid = 0;
    document.querySelectorAll('.wc-block-grid__product, .product:not(.product-category)')
        .forEach(elem => {

            // Skip first element on a product page
            // because we don't want to measure the main product
            if(wooptpmDataLayer.shop.page_type === 'product' && ioid === 0) return ioid++;

            // jQuery(elem).attr('data-ioid', ioid++);
            jQuery(elem).data('ioid', ioid++);

            io.observe(elem)
        });

    // remove_from_cart event
    jQuery(document).on('click', '.remove_from_cart_button, .remove', function (e) {

        let cartItemKey;

        if (wooptpmDataLayer['shop']['page_type'] === 'cart') {
            let href         = new URL(jQuery(this).attr('href'));
            let searchParams = new URLSearchParams(href.search);
            cartItemKey      = searchParams.get('remove_item');
            wooptpm.removeProductFromCart(cartItemKey);
        } else if (wooptpmDataLayer.cart_item_keys && wooptpmDataLayer.cart_item_keys[jQuery(this).data('cart_item_key')] !== undefined) {
            wooptpm.removeProductFromCart(jQuery(this).data('cart_item_key'));
        } else {
            wooptpm.removeProductFromCart(null, null, jQuery(this).data('product_id'));
        }
    });


    // add_to_cart event
    jQuery(document).on('click', '.add_to_cart_button:not(.product_type_variable), .ajax_add_to_cart, .single_add_to_cart_button', function (e) {

        if (wooptpmDataLayer['shop']['page_type'] === 'product') {

            // first process related and upsell products
            if (typeof jQuery(this).attr('href') !== 'undefined' && jQuery(this).attr('href').includes('add-to-cart')) {
                // alert('add-to-cart on upsell and related products');
                let quantity  = 1;
                let productId = jQuery(this).data('product_id');
                // alert('productId: ' + productId);
                wooptpm.addProductToCart(productId, quantity);
            } else {

                if (wooptpmDataLayer.shop.product_type === 'simple') {

                    // alert('test');
                    let quantity  = Number(jQuery('.input-text.qty').val());
                    let productId = jQuery(this).val();
                    // alert('productId: ' + productId);
                    wooptpm.addProductToCart(productId, quantity);

                } else if (wooptpmDataLayer['shop']['product_type'] === 'variable') {

                    // alert('variable');
                    let quantity    = Number(jQuery('.input-text.qty').val());
                    let productId   = jQuery("[name='product_id']").val();
                    let variationId = jQuery("[name='variation_id']").val();
                    wooptpm.addProductToCart(productId, quantity, variationId);

                } else if (wooptpmDataLayer.shop.product_type === 'grouped') {

                    // alert('grouped');

                    jQuery('.woocommerce-grouped-product-list-item').each(function () {
                        let quantity  = Number(jQuery(this).find('.input-text.qty').val());
                        let classes   = jQuery(this).attr('class');
                        let productId = wooptpm.getPostIdFromString(classes);
                        wooptpm.addProductToCart(productId, quantity);
                    });
                }
            }
        } else {

            // alert('non product page');

            let quantity  = 1;
            let productId = jQuery(this).data('product_id');
            // alert('productId: ' + productId);
            wooptpm.addProductToCart(productId, quantity);
        }
    });

    // if someone clicks anywhere on a custom /?add-to-cart=123 link
    // trigger the add to cart event
    jQuery(document).one('click', function (e) {


        if (jQuery(this)[0].URL) {

            let href         = new URL(jQuery(this)[0].URL);
            let searchParams = new URLSearchParams(href.search);

            if (searchParams.has('add-to-cart')) {
                // alert('non product page, /?add-to-cart=123 link');

                let productId = searchParams.get('add-to-cart');
                wooptpm.addProductToCart(productId, 1);
            }
        }
    });


    // select_content event
    // only allow the script to be fired on the following pages
    let allowed_pages = ['shop', 'product_category', 'product_tag', 'search', 'product_shop', 'product'];

    if (allowed_pages.includes(wooptpmDataLayer['shop']['page_type'])) {

        jQuery(document).on('click', '.woocommerce-LoopProduct-link', function (e) {

            let name      = jQuery(this).closest('.product');
            let classes   = name.attr('class');
            let productId = wooptpm.getPostIdFromString(classes);

            let data = {
                'id'       : productId.toString(),
                'dyn_r_ids': wooptpmDataLayer['products'][productId]['dyn_r_ids'],
                'name'     : wooptpmDataLayer['products'][productId]['name'],
                'list_name': wooptpmDataLayer['shop']['list_name'],
                'brand'    : wooptpmDataLayer['products'][productId]['brand'],
                'category' : wooptpmDataLayer['products'][productId]['category'],
                // "variant": "Black",
                'list_position': wooptpmDataLayer['products'][productId]['position'],
                'quantity'     : 1,
                'price'        : wooptpmDataLayer['products'][productId]['price']
            };

            jQuery(document).trigger('wooptpmSelectContent', data);
        });
    }

    // begin_checkout event
    jQuery(document).one('click', '.checkout-button, .cart-checkout-button, .button.checkout', function (e) {

        jQuery(document).trigger('wooptpmBeginCheckout');
    });

    // set_checkout_option event
    // track checkout option event: entered valid billing email
    jQuery(document).on('input', '#billing_email', function () {

        if (wooptpm.isEmail(jQuery(this).val())) {
            wooptpm.fireCheckoutOption(2);
        }
    });

    // track checkout option event: purchase click
    let payment_method_selected = false;

    jQuery(document).on('click', '.wc_payment_methods', function () {

        wooptpm.fireCheckoutOption(3, jQuery("input[name='payment_method']:checked").val());
        payment_method_selected = true;
    });

    // track checkout option event: purchase click
    jQuery(document).one('click', '#place_order', function () {

        if (payment_method_selected === false) {

            wooptpm.fireCheckoutOption(3, jQuery("input[name='payment_method']:checked").val());
        }

        wooptpm.fireCheckoutOption(4);
    });

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
                wooptpm.removeProductFromCart(cartItemKey);
            } else if (quantity < wooptpmDataLayer['cart'][productId]['quantity']) {
                wooptpm.removeProductFromCart(cartItemKey, wooptpmDataLayer['cart'][productId]['quantity'] - quantity);
            } else if (quantity > wooptpmDataLayer['cart'][productId]['quantity']) {
                wooptpm.addProductToCart(productId, quantity - wooptpmDataLayer['cart'][productId]['quantity']);
            }
        });
    });
});


jQuery(window).on('load', function () {
    // populate the wooptpmDataLayer with the cart items
    wooptpm.getCartItemsFromBackEnd();

    // wooptpm.loadPageProductsFromBackend();
});