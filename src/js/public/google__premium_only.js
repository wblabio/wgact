(function (wooptpm, $, undefined) {


    wooptpm.setGoogleCidOnServer = function (targetID) {

        try {

            gtag('get', targetID, 'client_id', (clientID) => {
                // console.log('Google cid: ' + clientID);

                // save the state in the database
                let data = {
                    'action'   : 'wooptpm_google_analytics_set_session_cid',
                    'target_id': targetID,
                    'client_id': clientID,
                };

                jQuery.ajax(
                    {
                        type    : "post",
                        dataType: "json",
                        url     : ajax_object.ajax_url,
                        data    : data,
                        success : function (msg) {
                            console.log(msg);
                        }
                    });
            });

        } catch (e) {
            console.log(e);
        }
    }

}(window.wooptpm = window.wooptpm || {}, jQuery));