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

                // save the state in the database
                let data = {
                    'action'   : 'wooptpm_google_analytics_set_session_cid',
                    'nonce': wooptpm_google_premium_only_ajax_object.nonce,
                    'target_id': targetID,
                    'client_id': clientID,
                };

                jQuery.ajax(
                    {
                        type    : "post",
                        dataType: "json",
                        url     : wooptpm_google_premium_only_ajax_object.ajax_url,
                        data    : data,
                        success : function (msg) {
                            // console.log(msg);
                        },
                        error : function (msg) {
                            // console.log(msg);
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