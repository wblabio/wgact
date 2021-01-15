
// copy debug info textarea
(function(){
    jQuery("#debug-info-button").click(function () {
        jQuery("#debug-info-textarea").select();
        document.execCommand('copy');
    });
})();

