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