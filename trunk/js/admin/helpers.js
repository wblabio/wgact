(function(){

    // run immediately
    // might not work if html parsing is not as fast
    jQuery('#script-blocker-notice').hide();

    // run one mre time after html parsing has been done
    // in case the first run was not successful
    jQuery('document').ready(function(){
        jQuery('#script-blocker-notice').hide();
    });

    // copy debug info textarea
    jQuery("#debug-info-button").click(function () {
        jQuery("#debug-info-textarea").select();
        document.execCommand('copy');
    });

})();

