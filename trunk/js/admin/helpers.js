
jQuery('#script-blocker-notice').hide();

jQuery(function(){

    // run immediately
    // might not work if html parsing is not as fast
    jQuery('#script-blocker-notice').hide();

    // copy debug info textarea
    jQuery("#debug-info-button").click(function () {
        jQuery("#debug-info-textarea").select();
        document.execCommand('copy');
    });

});

