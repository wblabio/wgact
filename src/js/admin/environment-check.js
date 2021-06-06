jQuery(function () {

    // disable WP Rocket JavaScript concatenation
    jQuery(document).on('click', '#wgact-wp-rocket-js-concatenation-disable', function (e) {
        e.preventDefault();

        let data = {
            'action': 'environment_check_handler',
            'set'   : 'disable_wp_rocket_javascript_concatenation'
        };

        wgact_send_ajax_data(data);
    });

    // dismiss WP Rocket JavaScript concatenation error
    jQuery(document).on('click', '#wgact-dismiss-wp-rocket-js-concatenation-error', function (e) {
        e.preventDefault();

        let data = {
            'action': 'environment_check_handler',
            'set'   : 'dismiss_wp_rocket_javascript_concatenation_error'
        };

        wgact_send_ajax_data(data);
    });

    // disable WP Rocket JavaScript concatenation
    jQuery(document).on('click', '#wgact-litespeed-inline-js-dom-ready-disable', function (e) {
        e.preventDefault();

        let data = {
            'action': 'environment_check_handler',
            'set'   : 'disable_litespeed_inline_js_dom_ready'
        };

        wgact_send_ajax_data(data);
    });

    // dismiss WP Rocket JavaScript concatenation error
    jQuery(document).on('click', '#wgact-dismiss-litespeed-inline-js-dom-ready-error', function (e) {
        e.preventDefault();

        let data = {
            'action': 'environment_check_handler',
            'set'   : 'dismiss_litespeed_inline_js_dom_ready'
        };

        wgact_send_ajax_data(data);
    });

    // dismiss PayPal standard payment gateway warning
    jQuery(document).on('click', '#wooptpm-paypal-standard-error-dismissal-button', function (e) {
        e.preventDefault();

        let data = {
            'action': 'environment_check_handler',
            'set'   : 'dismiss_paypal_standard_warning'
        };

        wgact_send_ajax_data(data);
    });

});

function wgact_send_ajax_data(data){
    jQuery.post(ajaxurl, data, function (response) {
        // console.log('Got this from the server: ' + response);
        // console.log('update rating done');
        location.reload();
    });
}