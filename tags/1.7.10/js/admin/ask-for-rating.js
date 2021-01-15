(function (){

    jQuery('.wgact-rating-success-notice').show();

    // go and rate it or already done
    jQuery(document).on('click', '#rate-it', function (e) {
        process_click(e, 'rating_done');

        let win = window.open('https://wordpress.org/support/view/plugin-reviews/woocommerce-google-adwords-conversion-tracking-tag?rate=5#postform', '_blank');
        win.focus();
    });

    jQuery(document).on('click', '#already-did', function (e) {
        process_click(e, 'rating_done');
    });

    // maybe rate later
    jQuery(document).on('click', '#maybe-later', function (e) {
        process_click(e, 'later');
    });

    function process_click(e, set){

        e.preventDefault();

        let data = {
            'action': 'wgact_dismissed_notice_handler',
            'set': set
        };

        jQuery.post(ajaxurl, data, function(response) {
            // console.log('Got this from the server: ' + response);
            // console.log('update rating done');
        });
        jQuery('.wgact-rating-success-notice').remove();
    }

})();