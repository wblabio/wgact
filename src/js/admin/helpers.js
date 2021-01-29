jQuery(function(){

    // copy debug info textarea
    jQuery("#debug-info-button").click(function () {
        jQuery("#debug-info-textarea").select();
        document.execCommand('copy');
    });

});

