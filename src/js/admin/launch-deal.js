jQuery(function () {

    jQuery(document).on('click', '#launch-deal-maybe-later', function (e) {
        process_click(e, 'launch-deal-maybe-later');
    });

    jQuery(document).on('click', '#launch-deal-dismiss, .launch-deal-notification > button', function (e) {

        jQuery('.launch-deal-notification').remove();

        process_click(e, 'launch-deal-dismiss');
    });

    function process_click(e, set) {

        e.preventDefault();

        let data = {
            'action': 'wooptpm_launch_deal_notice_handler',
            'set'   : set
        };

        jQuery.post(ajaxurl, data, function (response) {
        });
    }
});