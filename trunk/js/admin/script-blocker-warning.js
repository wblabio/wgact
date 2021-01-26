function wgact_hide_script_blocker_warning(){
    jQuery('#script-blocker-notice').hide();
}

// try to hide as soon as this script is loaded
// might be too early in some cases, as the HTML is not rendered yet
wgact_hide_script_blocker_warning();

// if all other earlier attempts to hide did fail
// run the function after entire DOM has been loaded
jQuery(function(){
    wgact_hide_script_blocker_warning();
});
