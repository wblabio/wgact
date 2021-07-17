if (typeof varExists !== "function") {
    function varExists(varName) {
        return new Promise(function (resolve, reject) {
            (function waitForJQuery() {
                if (typeof window[varName] !== 'undefined') return resolve();
                setTimeout(waitForJQuery, 30);
            })();
        });
    }
}

varExists('jQuery').then(function () {

    (function (wooptpm, $, undefined) {


        wooptpm.setGoogleCidOnServer = function (targetID) {

            try {

                gtag('get', targetID, 'client_id', (clientID) => {
                    // console.log('Google cid: ' + clientID);

                    let cidSetOnServerCookie = 'wooptpm_cid_' + targetID + '_' + clientID + '_set';

                    if ((window.sessionStorage && window.sessionStorage.getItem(cidSetOnServerCookie)) || wooptpm.getCookie(cidSetOnServerCookie)) {
                        return;
                    }

                    // save the state in the database
                    let data = {
                        'action': 'wooptpm_google_analytics_set_session_cid',
                        // 'nonce'    : wooptpm_google_premium_only_ajax_object.nonce,
                        'target_id': targetID,
                        'client_id': clientID,
                    };

                    jQuery.ajax(
                        {
                            type    : "post",
                            dataType: "json",
                            url     : wooptpm_google_premium_only_ajax_object.ajax_url,
                            data    : data,
                            success : function (response) {
                                // console.log('cid response:');
                                // console.log(response);
                                // console.log(response['cid_set'])

                                if (response['success'] === true) {
                                    if (window.sessionStorage) {
                                        // console.log('setting session storage');
                                        window.sessionStorage.setItem(cidSetOnServerCookie, JSON.stringify(true));
                                    } else {
                                        wooptpm.setCookie(cidSetOnServerCookie, true);
                                    }
                                }
                            },
                            error   : function (response) {
                                console.log(response);
                            },
                        });
                });

            } catch (e) {
                console.log(e);
            }
        }

    }(window.wooptpm = window.wooptpm || {}, jQuery));

}).catch(function () {
    console.log('object couldn\'t be loaded');
})