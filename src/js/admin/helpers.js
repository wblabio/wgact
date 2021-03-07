jQuery(function(){

    // copy debug info textarea
    jQuery("#debug-info-button").click(function () {
        jQuery("#debug-info-textarea").select();
        document.execCommand('copy');
    });

    jQuery("#wgact_pro_version_demo").on('click', function () {
        jQuery("#submit").click();
    });
});

