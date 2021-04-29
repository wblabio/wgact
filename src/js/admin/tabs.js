jQuery(function () {

    let sections = [];
    let subsections = {};

    // hide unnecessary elements
    jQuery('.section').closest('tr').hide();

    // collect information on sections
    jQuery('.section').each(function () {
        sections.push({
            'slug': jQuery(this).data('sectionSlug'),
            'title': jQuery(this).data('sectionTitle'),
        });
    });

    // collect information on subsections
    jQuery('.subsection').each(function () {

        subsections[jQuery(this).data('sectionSlug')] = subsections[jQuery(this).data('sectionSlug')] || [];

        subsections[jQuery(this).data('sectionSlug')].push({
                'title': jQuery(this).data('subsectionTitle'),
                'slug': jQuery(this).data('subsectionSlug')
        });
    });

    // create tabs for sections
    sections.forEach(
        function (section) {
            jQuery(".nav-tab-wrapper").append("<a href=\"#\" class=\"nav-tab\" data-section-slug=\"" + section['slug'] + "\">" + section['title'] + "</a>");
        });

    // create tabs for each subsections
    jQuery(".nav-tab-wrapper").after(createSubtabUlHtml(subsections));

    // create on-click events on section tabs that toggle the views
    jQuery(".nav-tab-wrapper a").on('click', function (e) {
        e.preventDefault();

        // show clicked tab as active
        jQuery(this).addClass("nav-tab-active").siblings().removeClass("nav-tab-active");

        // toggle the sections visible / invisible based on clicked tab

        let sectionSlug = jQuery(this).data('section-slug')
        toggleSections(sectionSlug, sections);

        // if subsection exists, click on first subsection
        if(sectionSlug in subsections){
            jQuery("ul[data-section-slug=" + sectionSlug + "]").children(':first').trigger('click')
        }
    });

    // create on-click events on subsection tabs that toggle the views
    jQuery(".subnav-li").on('click', function (e) {
        e.preventDefault();

        // jQuery(this).hide();
        jQuery(this)
            .addClass('subnav-li-active').removeClass('subnav-li-inactive')
            .siblings()
            .addClass('subnav-li-inactive').removeClass('subnav-li-active');

        toggleSubsection(jQuery(this).parent().data('section-slug'), jQuery(this).data('subsection-slug'));
    });

    // if someone access a plugin tab by deep link, open the right tab
    // or fallback to default (first tab)

    // if deeplink is being opened open the according section and subsection
    if (getSectionParams()) {

        let sectionParams = getSectionParams();

        jQuery("a[data-section-slug=" + sectionParams['section'] + "]").trigger('click');

        if(sectionParams['subsection'] !== false){
            jQuery("ul[data-section-slug=" + sectionParams['section'] + "]").children("[data-subsection-slug=" + sectionParams['subsection'] + "]").trigger('click')
        }
    } else {
        jQuery("a[data-section-slug=" + sections[0]['slug'] + "]").trigger('click');
    }
});

// creates the html with all subsection elements
function createSubtabUlHtml(subsections){

    let subsectionsKeys = Object.keys(subsections);

    let html = '';

    subsectionsKeys.forEach(function(subsectionKey){
        html += '<ul class="subnav-tabs" data-section-slug="' + subsectionKey + '">';

        let subtabs = subsections[subsectionKey];

        subtabs.forEach(function(subtab){
            html += '<li class="subnav-li subnav-li-inactive" data-subsection-slug="' + subtab['slug'] + '">' + subtab['title'] + '</li>'
        });

        html += '</ul>';
    });

    return html;
}

// if section (and subsection) URL parameters are set,
// return them, otherwise return false
function getSectionParams() {
    const queryString = window.location.search;
    const urlParams   = new URLSearchParams(queryString);

    if(urlParams.get('section')) {
        return {
            'section': urlParams.get('section'),
            'subsection': urlParams.get('subsection')
        };
    } else {
        return false;
    }
}

// toggles the sections
function toggleSections(sectionSlug, sections) {

    jQuery("#wgact_settings_form > h2").nextUntil(".submit").andSelf().hide();
    jQuery(".subnav-tabs").hide();
    jQuery(".subnav-tabs[data-section-slug=" + sectionSlug + "]").show();

    let sectionPos = sections.findIndex((arrayElement) => arrayElement['slug'] === sectionSlug);

    jQuery("div[data-section-slug=" + sectionSlug + "]").closest("table").prevAll("h2:first").next().nextUntil("h2, .submit").andSelf().show();

    // set the URL with the active tab parameter
    setUrl(sections[sectionPos]['slug']);
}

function toggleSubsection(sectionSlug, subsectionSlug){

    jQuery("#wgact_settings_form > h2").nextUntil(".submit").andSelf().hide();
    jQuery("[data-section-slug=" + sectionSlug + "][data-subsection-slug=" + subsectionSlug + "]").closest("tr").siblings().andSelf().hide();

    jQuery("[data-section-slug=" + sectionSlug + "][data-subsection-slug=" + subsectionSlug + "]").closest("table").show();
    jQuery("[data-section-slug=" + sectionSlug + "][data-subsection-slug=" + subsectionSlug + "]").closest("tr").nextUntil(jQuery("[data-section-slug=" + sectionSlug + "][data-subsection-slug]").closest('tr')).show();

    // set the URL with the active tab parameter
    setUrl(sectionSlug, subsectionSlug);
}

// sets the new URL parameters
function setUrl(sectionSlug, subsectionSlug = "") {

    const queryString = window.location.search;
    const urlParams   = new URLSearchParams(queryString);

    urlParams.delete('section');
    urlParams.delete('subsection');

    let newParams = "section=" + sectionSlug;
    newParams += subsectionSlug ? "&subsection=" + subsectionSlug : "";

    history.pushState('', 'WGACT ' + sectionSlug, document.location.pathname + "?page=wgact&" + newParams);

    // make WP remember which was the selected tab on a save and return to the same tab after saving
    jQuery('input[name ="_wp_http_referer"]').val( getAdminPath() + "?page=wgact&" + newParams + "&settings-updated=true");
}

function getAdminPath(){
    let url = new URL(jQuery('#wp-admin-canonical').attr('href'));
    return url.pathname;
}