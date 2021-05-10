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

    // console.log('jq loaded');

    (function (wooptpm, $, undefined) {

        const wgactDeduper = {
            keyName          : '_wgact_order_ids',
            cookieExpiresDays: 365
        };

        wooptpm.objectExists = function (obj) {

            for (let i = 1; i < arguments.length; i++) {
                if (!obj.hasOwnProperty(arguments[i])) {
                    return false;
                }
                obj = obj[arguments[i]];
            }
            return true;
        }

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

            // console.log('deduper: ' + wooptpmDataLayer.orderDeduplication);
            if (wooptpmDataLayer.orderDeduplication) {
                // console.log('order deduplication: on');
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
                console.log('order deduplication: off');
                return false;
            }
        }

        wooptpm.isEmail = function (email) {
            // https://emailregex.com/
            let regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return regex.test(email);
        }

        wooptpm.removeProductFromCart = function (productId, quantityToRemove = null) {

            try {

                if (!productId) throw Error('Wasn\'t able to retrieve a productId');

                productId = getIdBasedOndVariationsOutputSetting(productId);

                if (!productId) throw Error('Wasn\'t able to retrieve a productId');

                let quantity;

                if (quantityToRemove == null) {
                    quantity = wooptpmDataLayer.cart[productId].quantity;
                } else {
                    quantity = quantityToRemove;
                }

                // console.log('product_id: ' + productId + ' | qty: ' + quantity);
                // console.log(productId);
                // console.log(wooptpmDataLayer.cart);
                // console.log(wooptpmDataLayer.cart[productId]);

                if (wooptpmDataLayer.cart[productId]) {

                    let data = {
                        "id"       : productId.toString(),
                        "dyn_r_ids": wooptpmDataLayer.cart[productId].dyn_r_ids,
                        "name"     : wooptpmDataLayer.cart[productId].name,
                        // "list_name": wooptpmDataLayer.shop.list_name, // doesn't make sense on mini_cart
                        "brand"   : wooptpmDataLayer.cart[productId].brand,
                        "category": wooptpmDataLayer.cart[productId].category,
                        "variant" : wooptpmDataLayer.cart[productId].variant,
                        // "list_position": wooptpmDataLayer.cart[productId].position, // doesn't make sense on mini_cart
                        "quantity": quantity,
                        "price"   : wooptpmDataLayer.cart[productId].price
                    };

                    // console.log('removing');
                    // console.log(data);

                    jQuery(document).trigger('wooptpmRemoveFromCart', data);

                    if (quantityToRemove == null) {
                        delete wooptpmDataLayer.cart[productId];
                    } else {
                        wooptpmDataLayer.cart[productId].quantity = wooptpmDataLayer.cart[productId].quantity - quantity;
                    }
                }
            } catch (e) {
                console.log(e);
            }
        }

        getIdBasedOndVariationsOutputSetting = function (productId) {

            try {
                if (wooptpmDataLayer.general.variationsOutput) {
                    // console.log('test');
                    return productId;
                } else {
                    if (wooptpmDataLayer.products[productId].isVariation) {
                        return wooptpmDataLayer.products[productId].parentId;
                    } else {
                        return productId;
                    }
                }
            } catch (e) {
                console.log(e);
            }
        }

        // add_to_cart
        wooptpm.addProductToCart = function (productId, quantity) {

            try {
                // console.log('productId: ' + productId + ' | qty: ' + quantity);
                // console.log('productId: ' + productId + ' | variationId: ' + variationId + ' | qty: ' + quantity);

                if (!productId) throw Error('Wasn\'t able to retrieve a productId');

                productId = getIdBasedOndVariationsOutputSetting(productId);

                if (!productId) throw Error('Wasn\'t able to retrieve a productId');

                if (wooptpmDataLayer.products[productId]) {

                    let data = {
                        "id"           : productId.toString(),
                        "dyn_r_ids"    : wooptpmDataLayer.products[productId].dyn_r_ids,
                        "name"         : wooptpmDataLayer.products[productId].name,
                        "list_name"    : wooptpmDataLayer.shop.list_name, // maybe remove if in products
                        "brand"        : wooptpmDataLayer.products[productId].brand,
                        "category"     : wooptpmDataLayer.products[productId].category,
                        "variant"      : wooptpmDataLayer.products[productId].variant,
                        "list_position": wooptpmDataLayer.products[productId].position,
                        "quantity"     : quantity,
                        "price"        : wooptpmDataLayer.products[productId].price,
                        "currency"     : wooptpmDataLayer.shop.currency,
                    };

                    // console.log(data);

                    jQuery(document).trigger('wooptpmAddToCart', data);

                    // add product to cart wooptpmDataLayer['cart']

                    // if the product already exists in the object, only add the additional quantity
                    // otherwise create that product object in the wooptpmDataLayer['cart']
                    if (wooptpmDataLayer.cart !== undefined && wooptpmDataLayer.cart[productId] !== undefined) {
                        wooptpmDataLayer.cart[productId].quantity = wooptpmDataLayer.cart[productId].quantity + quantity;
                    } else {

                        if (!wooptpmDataLayer.cart) {

                            wooptpmDataLayer['cart'] = {
                                [productId]: {
                                    'id'       : productId,
                                    'dyn_r_ids': wooptpmDataLayer.products[productId].dyn_r_ids,
                                    'name'     : wooptpmDataLayer.products[productId].name,
                                    'brand'    : wooptpmDataLayer.products[productId].brand,
                                    'category' : wooptpmDataLayer.products[productId].category,
                                    "variant"  : wooptpmDataLayer.products[productId].variant,
                                    'quantity' : quantity,
                                    'price'    : wooptpmDataLayer.products[productId].price
                                }
                            };

                        } else {

                            wooptpmDataLayer.cart[productId] = {
                                'id'       : productId,
                                'dyn_r_ids': wooptpmDataLayer.products[productId].dyn_r_ids,
                                'name'     : wooptpmDataLayer.products[productId].name,
                                'brand'    : wooptpmDataLayer.products[productId].brand,
                                'category' : wooptpmDataLayer.products[productId].category,
                                "variant"  : wooptpmDataLayer.products[productId].variant,
                                'quantity' : quantity,
                                'price'    : wooptpmDataLayer.products[productId].price
                            };
                        }
                    }
                }
            } catch (e) {
                console.log(e);
            }
        }

        wooptpm.getCartItemsFromBackEnd = function () {
            // get all cart items from the backend

            try {
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
                            // console.log(cart_items['cart']);
                            wooptpmDataLayer.cart     = cart_items['cart'];
                            // wooptpmDataLayer.products = {... wooptpmDataLayer.products, ...cart_items['cart']};
                            wooptpmDataLayer.products = Object.assign({}, wooptpmDataLayer.products, cart_items['cart']);
                        }
                    });
            } catch (e) {
                console.log(e);
            }
        }

        wooptpm.fireCheckoutOption = function (step, checkout_option = null, value = null) {

            let data = {
                'step'           : step,
                'checkout_option': checkout_option,
                'value'          : value
            };

            jQuery(document).trigger('wooptpmFireCheckoutOption', data);
        }

        wooptpm.fireCheckoutProgress = function (step) {

            let data = {
                'step': step,
            };

            jQuery(document).trigger('wooptpmFireCheckoutProgress', data);
        }

        wooptpm.getPostIdFromString = function (string) {
            // console.log(string);
            try {
                return string.match(/(post-)(\d+)/)[2];
            } catch (e) {
                console.log(e);
            }
        }

        wooptpm.triggerViewItemList = function (productId) {

            // productId = null;

            if (!productId) throw Error('Wasn\'t able to retrieve a productId');

            productId = getIdBasedOndVariationsOutputSetting(productId);

            if (!productId) throw Error('Wasn\'t able to retrieve a productId');

            jQuery(document).trigger('wooptpmViewItemList', wooptpm.getProductDataForViewItemEvent(productId));
        }

        wooptpm.getProductDataForViewItemEvent = function (productId) {

            if (!productId) throw Error('Wasn\'t able to retrieve a productId');

            try {
                if (wooptpmDataLayer.products[productId]) {
                    return {
                        "id"           : productId.toString(),
                        "dyn_r_ids"    : wooptpmDataLayer.products[productId].dyn_r_ids,
                        "name"         : wooptpmDataLayer.products[productId].name,
                        "list_name"    : wooptpmDataLayer.shop.list_name, // maybe remove if in cart
                        "brand"        : wooptpmDataLayer.products[productId].brand,
                        "category"     : wooptpmDataLayer.products[productId].category,
                        "variant"      : wooptpmDataLayer.products[productId].variant,
                        "list_position": wooptpmDataLayer.products[productId].position,
                        "quantity"     : 1,
                        "price"        : wooptpmDataLayer.products[productId].price,
                        "currency"     : wooptpmDataLayer.shop.currency,
                    };
                }
            } catch (e) {
                console.log(e);
            }
        }

        wooptpm.getMainProductIdFromProductPage = function () {
            try {
                if (wooptpmDataLayer.shop.product_type === 'simple') {
                    return jQuery('.single_add_to_cart_button').val();
                } else if (wooptpmDataLayer.shop.product_type === 'variable') {
                    return jQuery('.woocommerce-variation-add-to-cart').find('[name="add-to-cart"]').val();
                } else if (wooptpmDataLayer.shop.product_type === 'grouped') {
                    return jQuery('.grouped_form').find('[name="add-to-cart"]').val();
                } else {
                    return false;
                }
            } catch (e) {
                console.log(e);
            }
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

        wooptpm.getSearchTermFromUrl = function () {
            try {
                let urlParameters = new URLSearchParams(window.location.search)
                return urlParameters.get('s');
            } catch (e) {
                console.log(e);
            }
        }

        // we need this to track timeouts for intersection observers
        let ioTimeouts = {};

        wooptpm.observerCallback = function (entries, observer) {

            entries.forEach((entry) => {

                try {
                    let productId;

                    let elementId = jQuery(entry.target).data('ioid');

                    // Get the productId from next element, if wooptpmProductId is a sibling, like in Gutenberg blocks
                    // otherwise go search in children, like in regular WC loop items
                    if (jQuery(entry.target).next('.wooptpmProductId').length) {
                        // console.log('test 1');
                        productId = jQuery(entry.target).next('.wooptpmProductId').data('id');
                    } else {
                        productId = jQuery(entry.target).find('.wooptpmProductId').data('id');
                    }

                    // productId = null;

                    if (!productId) throw Error('wooptpmProductId element not found');

                    if (entry.isIntersecting) {

                        // console.log('prodid: ' + productId);
                        ioTimeouts[elementId] = setTimeout(() => {
                            //                 console.log('prodid: ' + productId);
                            wooptpm.triggerViewItemList(productId);
                            if (wooptpmDataLayer.viewItemListTrigger.testMode) wooptpm.viewItemListTriggerTestMode(entry.target);
                            if (wooptpmDataLayer.viewItemListTrigger.repeat === false) observer.unobserve(entry.target);
                        }, wooptpmDataLayer.viewItemListTrigger.timeout)

                    } else {

                        clearTimeout(ioTimeouts[elementId])
                        if (wooptpmDataLayer.viewItemListTrigger.testMode) jQuery(entry.target).find('#viewItemListTriggerOverlay').remove();
                    }
                } catch (e) {
                    console.log(e);
                }
            });
        }

        // fire view_item_list only on products that have become visible
        let io;
        let ioid = 0;
        let allIoElementsToWatch;

        let getAllElementsToWatch = function () {

            allIoElementsToWatch = jQuery('.wooptpmProductId')
                .map(function (i, elem) {
                    // console.log(elem);
                    if (
                        jQuery(elem).parent().hasClass('type-product') ||
                        jQuery(elem).parent().hasClass('product') ||
                        jQuery(elem).parent().hasClass('product-item-inner')
                    ) {
                        // console.log(elem);
                        return jQuery(elem).parent();
                    } else if (
                        jQuery(elem).prev().hasClass('wc-block-grid__product') ||
                        jQuery(elem).prev().hasClass('product') ||
                        jQuery(elem).prev().hasClass('product-small') ||
                        jQuery(elem).prev().hasClass('woocommerce-LoopProduct-link')
                    ) {
                        return jQuery(this).prev();
                    }
                });
        }

        wooptpm.startIntersectionObserverToWatch = function () {

            try {
                // enable view_item_list test mode from browser
                let urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('vildemomode')) wooptpmDataLayer.viewItemListTrigger.testMode = true;

                // set up intersection observer
                io = new IntersectionObserver(wooptpm.observerCallback, {
                    threshold: wooptpmDataLayer.viewItemListTrigger.threshold,
                });

                getAllElementsToWatch();

                // console.log(allElementsToWatch);

                allIoElementsToWatch.each(function (i, elem) {
                    // console.log(elem[0]);
                    // jQuery(elem[0]).attr('data-ioid', ioid++);
                    jQuery(elem[0]).data('ioid', ioid++);

                    io.observe(elem[0])
                });
            } catch (e) {
                console.log(e);
            }
        }

        // watch DOM for new lazy loaded products and add them to the intersection observer
        wooptpm.startProductsMutationObserverToWatch = function () {

            try {
                // Pass in the target node, as well as the observer options

                // selects the most common parent node
                // https://stackoverflow.com/a/7648323/4688612
                let productsNode = jQuery('.wooptpmProductId:eq(0)').parents().has(jQuery('.wooptpmProductId:eq(1)').parents()).first()

                if (productsNode.length) {
                    productsMutationObserver.observe(productsNode[0], {
                        attributes   : true,
                        childList    : true,
                        characterData: true
                    });
                }
            } catch (e) {
                console.log(e);
            }
        }

        // Create an observer instance
        let productsMutationObserver = new MutationObserver(function (mutations) {

            mutations.forEach(function (mutation) {
                let newNodes = mutation.addedNodes; // DOM NodeList
                if (newNodes !== null) { // If there are new nodes added
                    let nodes = jQuery(newNodes); // jQuery set
                    nodes.each(function () {
                        if (
                            jQuery(this).hasClass("type-product") ||
                            jQuery(this).hasClass("product-small") ||
                            jQuery(this).hasClass('wc-block-grid__product')
                        ) {
                            // check if the node has a child or sibling wooptpmProductId
                            // if yes add it to the intersectionObserver
                            if (hasWooptpmProductIdElement(this)) {
                                jQuery(this).data('ioid', ioid++);
                                io.observe(this)
                            }
                        }
                    });
                }
            });
        });

        let hasWooptpmProductIdElement = function (elem) {
            return !!(jQuery(elem).find('.wooptpmProductId').length ||
                jQuery(elem).siblings('.wooptpmProductId').length);
        }

        // wooptpm['load'] = {
        //     base: true
        // };

        window['wooptpmLoaded'] = {};

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

// run when window ready
    jQuery(function () {
// jQuery(window).on('load', function () {

        // watch for products visible in viewport
        wooptpm.startIntersectionObserverToWatch();

        // watch for lazy loaded products
        wooptpm.startProductsMutationObserverToWatch();

        let body     = jQuery('body');
        let products = jQuery('.products, .product');
        // remove_from_cart event
        body.on('click', '.remove_from_cart_button, .remove', function (e) {

            try {
                // console.log('remove_from_cart: ' + jQuery(this).data('product_id'));

                wooptpm.removeProductFromCart(jQuery(this).data('product_id'));

            } catch (e) {
                console.log(e);
            }
        });


        // add_to_cart event
        body.on('click', '.add_to_cart_button:not(.product_type_variable), .ajax_add_to_cart, .single_add_to_cart_button', function (e) {

            try {
                // console.log('add_to_cart');

                if (wooptpmDataLayer.shop.page_type === 'product') {

                    // first process related and upsell products
                    if (typeof jQuery(this).attr('href') !== 'undefined' && jQuery(this).attr('href').includes('add-to-cart')) {
                        // console.log('add-to-cart on upsell and related products');
                        let quantity  = 1;
                        let productId = jQuery(this).data('product_id');
                        // console.log('productId: ' + productId);
                        wooptpm.addProductToCart(productId, quantity);
                    } else {

                        if (wooptpmDataLayer.shop.product_type === 'simple') {

                            // console.log('test');
                            let quantity  = Number(jQuery('.input-text.qty').val());
                            let productId = jQuery(this).val();
                            // console.log('productId: ' + productId);
                            wooptpm.addProductToCart(productId, quantity);

                        } else if (wooptpmDataLayer.shop.product_type === 'variable') {

                            // console.log('variable');

                            let quantity  = Number(jQuery('.input-text.qty').val());
                            let productId = jQuery("[name='variation_id']").val();
                            wooptpm.addProductToCart(productId, quantity);

                        } else if (wooptpmDataLayer.shop.product_type === 'grouped') {

                            // console.log('grouped');

                            jQuery('.woocommerce-grouped-product-list-item').each(function () {
                                let quantity  = Number(jQuery(this).find('.input-text.qty').val());
                                let classes   = jQuery(this).attr('class');
                                let productId = wooptpm.getPostIdFromString(classes);
                                wooptpm.addProductToCart(productId, quantity);
                            });
                        }
                    }
                } else {

                    // console.log('non product page');

                    let quantity  = 1;
                    let productId = jQuery(this).data('product_id');
                    // console.log('productId: ' + productId);
                    wooptpm.addProductToCart(productId, quantity);
                }
            } catch (e) {
                console.log(e);
            }
        });

        // if someone clicks anywhere on a custom /?add-to-cart=123 link
        // trigger the add to cart event
        body.one('click', function (e) {

            try {
                if (jQuery(this)[0].URL) {

                    let href         = new URL(jQuery(this)[0].URL);
                    let searchParams = new URLSearchParams(href.search);

                    if (searchParams.has('add-to-cart')) {
                        // console.log('non product page, /?add-to-cart=123 link');

                        let productId = searchParams.get('add-to-cart');
                        wooptpm.addProductToCart(productId, 1);
                    }
                }
            } catch (e) {
                console.log(e);
            }

        });


        // select_content GA UA event
        // select_item GA 4 event
        // jQuery(document).on('click', '.woocommerce-LoopProduct-link, .wc-block-grid__product, .product-small.box', function (e) {
        body.on('click', '.woocommerce-LoopProduct-link, .wc-block-grid__product, .product, .product-small, .type-product', function (e) {

            try {

                // On some pages the event fires multiple times, and on product pages
                // even on page load. Using e.stopPropagation helps to prevent this,
                // but I dont know why. We don't even have to use this, since only a real
                // product click yields a valid productId. So we filter the invalid click
                // events out later down the code. I'll keep it that way because this is
                // the most compatible way across shops.
                // e.stopPropagation();

                // console.log('select_content and select_item');

                let productId = jQuery(this).nextAll('.wooptpmProductId:first').data('id');
                // console.log('select_content and select_item: ' + productId);


                // On product pages, for some reason, the click event is triggered on the main product on page load.
                // In that case no ID is found. But we can discard it, since we only want to trigger the event on
                // related products, which are found below.
                if (productId) {

                    // console.log('select_content and select_item: ' + productId);

                    productId = getIdBasedOndVariationsOutputSetting(productId);

                    if (!productId) throw Error('Wasn\'t able to retrieve a productId');

                    // console.log('prodid: ' + productId);

                    if (wooptpmDataLayer.products && wooptpmDataLayer.products[productId]) {

                        let data = {
                            "id"           : productId.toString(),
                            "dyn_r_ids"    : wooptpmDataLayer.products[productId].dyn_r_ids,
                            "name"         : wooptpmDataLayer.products[productId].name,
                            "list_name"    : wooptpmDataLayer.shop.list_name,
                            "brand"        : wooptpmDataLayer.products[productId].brand,
                            "category"     : wooptpmDataLayer.products[productId].category,
                            "variant"      : wooptpmDataLayer.products[productId].variant,
                            "list_position": wooptpmDataLayer.products[productId].position,
                            "quantity"     : 1,
                            "price"        : wooptpmDataLayer.products[productId].price
                        };

                        jQuery(document).trigger('wooptpmSelectContentGaUa', data);
                        jQuery(document).trigger('wooptpmSelectItem', data);
                    }
                }
            } catch (e) {
                console.log(e);
            }
        });

        // begin_checkout event
        body.one('click', '.checkout-button, .cart-checkout-button, .button.checkout', function (e) {

            // console.log('begin_checkout');

            jQuery(document).trigger('wooptpmBeginCheckout');
        });

        let emailSelected = false;

        // checkout_progress event
        // track checkout option event: entered valid billing email
        body.on('input', '#billing_email', function () {

            if (wooptpm.isEmail(jQuery(this).val())) {
                // wooptpm.fireCheckoutOption(2);
                wooptpm.fireCheckoutProgress(2);
                emailSelected = true;
            }
        });

        // track checkout option event: purchase click
        let paymentMethodSelected = false;

        body.on('click', '.wc_payment_methods', function () {

            if (paymentMethodSelected === false) {
                wooptpm.fireCheckoutProgress(3);
            }

            wooptpm.fireCheckoutOption(3, jQuery("input[name='payment_method']:checked").val());
            paymentMethodSelected = true;
        });

        // track checkout option event: purchase click
        body.one('click', '#place_order', function () {

            if (emailSelected === false) {
                wooptpm.fireCheckoutProgress(2);
            }

            if (paymentMethodSelected === false) {
                wooptpm.fireCheckoutProgress(3);
                wooptpm.fireCheckoutOption(3, jQuery("input[name='payment_method']:checked").val());
            }

            wooptpm.fireCheckoutProgress(4);
        });

        // update cart event
        body.on('click', "[name='update_cart']", function (e) {

            try {
                jQuery('.cart_item').each(function () {

                    let productId = jQuery(this).find('[data-product_id]').data('product_id');

                    let quantity = jQuery(this).find('.qty').val();

                    if (quantity === 0) {
                        wooptpm.removeProductFromCart(productId);
                    } else if (quantity < wooptpmDataLayer.cart[productId].quantity) {
                        wooptpm.removeProductFromCart(productId, wooptpmDataLayer.cart[productId].quantity - quantity);
                    } else if (quantity > wooptpmDataLayer.cart[productId].quantity) {
                        wooptpm.addProductToCart(productId, quantity - wooptpmDataLayer.cart[productId].quantity);
                    }
                });
            } catch (e) {
                console.log(e);
            }
        });

        // Fired when the user selects all the required dropdowns / attributes
        // https://stackoverflow.com/a/27849208/4688612
        jQuery(".single_variation_wrap").on("show_variation", function (event, variation) {

            try {
                // Fired when the user selects all the required dropdowns / attributes
                // console.log('product selected');
                // console.log(variation);

                let productId = getIdBasedOndVariationsOutputSetting(variation.variation_id);

                if (!productId) throw Error('Wasn\'t able to retrieve a productId');

                if (wooptpmDataLayer.products && wooptpmDataLayer.products[productId]) {

                    // console.log('productId: ' + productId);

                    let data = {
                        "id"           : productId.toString(),
                        "dyn_r_ids"    : wooptpmDataLayer.products[productId].dyn_r_ids,
                        "name"         : wooptpmDataLayer.products[productId].name,
                        "list_name"    : wooptpmDataLayer.shop.list_name,
                        "brand"        : wooptpmDataLayer.products[productId].brand,
                        "category"     : wooptpmDataLayer.products[productId].category,
                        "variant"      : wooptpmDataLayer.products[productId].variant,
                        "list_position": wooptpmDataLayer.products[productId].position,
                        "quantity"     : 1,
                        "price"        : wooptpmDataLayer.products[productId].price
                    };

                    jQuery(document).trigger('wooptpmViewItem', data);
                }
            } catch (e) {
                console.log(e);
            }
        });

        // add_to_wishlist
        body.on('click', '.add_to_wishlist, .wl-add-to', function () {
            try {
                // console.log('add_to_wishlist');
                // console.log('this:' + jQuery(this).data('product-id'));

                let productId;

                if (jQuery(this).data('productid')) { // for the WooCommerce wishlist plugin
                    productId = jQuery(this).data('productid');
                } else if (jQuery(this).data('product-id')) {  // for the YITH wishlist plugin
                    productId = jQuery(this).data('product-id');
                }

                if (!productId) throw Error('Wasn\'t able to retrieve a productId');

                let product = {
                    "id"           : productId.toString(),
                    "dyn_r_ids"    : wooptpmDataLayer.products[productId].dyn_r_ids,
                    "name"         : wooptpmDataLayer.products[productId].name,
                    "list_name"    : wooptpmDataLayer.shop.list_name,
                    "brand"        : wooptpmDataLayer.products[productId].brand,
                    "category"     : wooptpmDataLayer.products[productId].category,
                    "variant"      : wooptpmDataLayer.products[productId].variant,
                    "list_position": wooptpmDataLayer.products[productId].position,
                    "quantity"     : 1,
                    "price"        : wooptpmDataLayer.products[productId].price
                };

                // console.log('add_to_wishlist');
                // console.log(product);

                jQuery(document).trigger('wooptpmAddToWishlist', product);
            } catch (e) {
                console.log(e);
            }
        })
    });


    jQuery(window).on('load', function () {
        // populate the wooptpmDataLayer with the cart items
        // console.log('getting cart');

        try {
            wooptpm.getCartItemsFromBackEnd();
        } catch (e) {
            console.log(e);
        }
        // wooptpm.loadPageProductsFromBackend();
    });

}).catch(function () {
    console.log('object couldn\'t be loaded');
})