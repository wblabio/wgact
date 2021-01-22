jQuery(function () {

    // disable WP Rocket JavaScript concatenation
    jQuery(document).on('click', '#wgact-wp-rocket-js-concatenation-disable', function (e) {
        e.preventDefault();

        let data = {
            'action': 'environment_check_handler',
            'set'   : 'disable_wp_rocket_javascript_concatenation'
        };

        jQuery.post(ajaxurl, data, function (response) {
            // console.log('Got this from the server: ' + response);
            // console.log('update rating done');
            location.reload();
        });

    });
});