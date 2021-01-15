(function (){

    // go and rate it or already done
    jQuery(document).on('click', '#rate-it, #already-did', function (e) {

        e.preventDefault();

        let data = {
            'action': 'wgact_dismissed_notice_handler',
            'set': 'rating_done'
        };

        jQuery.post(ajaxurl, data, function(response) {
            // console.log('Got this from the server: ' + response);
            // console.log('update rating done');
        });
        jQuery('.wgact-rating-success-notice').remove();

        let win = window.open('https://wordpress.org/support/view/plugin-reviews/woocommerce-google-adwords-conversion-tracking-tag?rate=5#postform', '_blank');
        win.focus();

    });

    // maybe rate later
    jQuery(document).on('click', '#maybe-later', function (e) {
        e.preventDefault();

        let data = {
            'action': 'wgact_dismissed_notice_handler',
            'set': 'later'
        };

        jQuery.post(ajaxurl, data, function(response) {
            // console.log('Got this from the server: ' + response);
        });
        jQuery('.wgact-rating-success-notice').remove();

    });

})();