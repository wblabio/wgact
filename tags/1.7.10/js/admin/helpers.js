
// copy debug info textarea
(function(){

    jQuery('#script-blocker-notice').hide();

    jQuery("#debug-info-button").click(function () {
        jQuery("#debug-info-textarea").select();
        document.execCommand('copy');
    });

})();

