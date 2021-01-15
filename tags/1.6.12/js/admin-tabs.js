jQuery(function () {

    let tabNames = [];

    // get tab names from h2 (section titles)
    jQuery('#wgact_settings_form h2').each(function () {
        tabNames.push(jQuery(this).text());
    });

    // create tab for each title
    tabNames.forEach(
        function (tabName) {
            jQuery(".nav-tab-wrapper").append("<a href=\"#\" class=\"nav-tab\" id=\"tab-" + slugify(tabName) + "\">" + tabName + "</a>");
        });

    // create on click event on tabs that toggles the views
    jQuery(".nav-tab-wrapper a").on('click', function (e) {
        e.preventDefault();

        // show clicked tab as active
        jQuery(this).addClass("nav-tab-active").siblings().removeClass("nav-tab-active");

        // toggle the sections visible / invisible based on clicked tab
        toggleSections(jQuery(this).text(), tabNames);
    });

    // if someone access a plugin tab by deep link, open the right tab
    // or fallback to default (first tab)
    if (getTabParam()) {
        jQuery("#tab-" + getTabParam()).trigger('click');
    } else {
        jQuery("#tab-" + slugify(tabNames[0])).trigger('click');
    }
});

// if a tab parameter is set in the URL return the value
// otherwise return false
function getTabParam() {
    const queryString = window.location.search;
    const urlParams   = new URLSearchParams(queryString);
    return urlParams.get('tab');
}

function toggleSections(tabName, tabNames) {

    jQuery("#wgact_settings_form > h2").nextUntil(".submit").andSelf().css("display", "none")

    let nextTabId = tabNames.findIndex((arrayElement) => arrayElement == tabName) + 1;

    jQuery("#wgact_settings_form > h2:contains(" + tabName + ")").nextUntil("h2:contains(" + tabNames[nextTabId] + "), .submit").andSelf().css("display", "")

    // set the URL with the active tab parameter
    setUrl(tabName);
}

function slugify(text) {
    return text.toLowerCase().replace(/[^\w ]+/g, '').replace(/ +/g, '-');
}

function setUrl(tabName) {

    const queryString = window.location.search;
    const urlParams   = new URLSearchParams(queryString);

    urlParams.delete('tab');

    let tabParam = slugify(tabName);

    history.pushState('', 'WGACT ' + tabName, document.location.pathname + "?" + urlParams + "&tab=" + tabParam);

    // make WP remember which was the selected tab on a save and return to the same tab after saving
    jQuery('input[name ="_wp_http_referer"]').val("/wp-admin/admin.php?page=wgact&tab=" + tabParam + "&settings-updated=true");
}