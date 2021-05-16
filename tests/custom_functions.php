<?php

//add_filter('wooptpm_google_cross_domain_linker_settings', function (){
//
//    return [
//        "domains" => [
//            'wordpress.test',
//            'example.store.co.uk',
//            'example.store.com',
//        ]
//    ];
//});

//add_filter('wooptpm_track_mini_cart', '__return_false');

//add_filter('wooptpm_send_http_api_facebook_capi_requests_blocking', '__return_true');
add_filter('wooptpm_facebook_capi_purchase_logging', '__return_true');

add_filter('wooptpm_facebook_capi_test_event_code', function () {
    return 'TEST38471';
});

//add_filter('wooptpm_facebook_capi_user_transparency_settings', function ($user_transparency_settings) {
//
//    $user_transparency_settings['process_anonymous_hits'] = true;
//    $user_transparency_settings['use_client_ip_address']  = true;
//    $user_transparency_settings['use_client_email']       = true;
//    $user_transparency_settings['use_client_shop_id']     = true;
//
//    return $user_transparency_settings;
//});

//add_filter('wooptpm_facebook_capi_data_processing_options', function () {
//    return [
//        'data_processing_options'         => ['LDU'],
//        'data_processing_options_country' => 1,
//        'data_processing_options_state'   => 1000,
//    ];
//});

add_filter('wooptpm_enable_ga_4_mp_event_debug_mode', '__return_true');
//add_filter('wooptpm_send_http_api_ga_4_requests_blocking', '__return_true');

//add_filter('wooptpm_view_item_list_trigger_settings', 'wooptpm_view_item_list_trigger_settings');
function wooptpm_view_item_list_trigger_settings($settings)
{
    $settings['testMode']        = true;
    $settings['backgroundColor'] = 'blue';
    $settings['opacity']         = 0.5;
    $settings['repeat']          = true;
    $settings['threshold']       = 0.8;
    $settings['timeout']         = 200;

    return $settings;
}


// http://hookr.io/filters/wc_get_template_part/
// http://hookr.io/plugins/woocommerce/3.0.6/files/includes-class-wc-shortcodes/

// define the woocommerce_after_shop_loop_item callback
function action_woocommerce_after_shop_loop_item()
{
    global $product;
    // make action magic happen here...
//    error_log('new test: ' . $product->get_id());

    echo "xfxf";
}


// add the action
//add_action( 'woocommerce_after_shop_loop_item', 'action_woocommerce_after_shop_loop_item', 10, 1 );


function wc_add_date_to_gutenberg_block($html, $data, $product)
{
    error_log('test');

    return $html . "xfxf";
}

//add_filter("woocommerce_blocks_product_grid_item_html", "wc_add_date_to_gutenberg_block", 10, 3);


if (isset($_GET["dynr"])) {
    add_filter('wooptpm_product_ids', 'return_wooptpm_dyn_r_product_ids', 10, 2);
    function return_wooptpm_dyn_r_product_ids($dyn_r_ids, $product)
    {
        $dyn_r_ids['custom1'] = 'custm_googXX_' . $product->get_id();
        $dyn_r_ids['custom2'] = 'custm_fbXX_' . $product->get_id();
        $dyn_r_ids['custom3'] = 'custm_pinterestXX_' . $product->get_id();

        return $dyn_r_ids;
    }

//    add_filter('wooptpm_product_id_type_for_google', 'product_id_type_output_for_google');
//    function product_id_type_output_for_google(): string
//    {
//        return 'custom1';
//    }

    add_filter('wooptpm_product_id_type_for_google_ads', 'product_id_type_output_for_google_ads');
    function product_id_type_output_for_google_ads(): string
    {
        return 'custom1';
    }

    add_filter('wooptpm_product_id_type_for_facebook', 'product_id_type_output_for_facebook');
    function product_id_type_output_for_facebook(): string
    {
        return 'sku';
    }

    add_filter('wooptpm_product_id_type_for_pinterest', 'product_id_type_output_for_pinterest');
    function product_id_type_output_for_pinterest(): string
    {
        return 'custom3';
    }
}

if (isset($_GET["conversion_prevention_filter"])) {
    add_filter('wgact_conversion_prevention', '__return_true');
}

//add_filter('wooptpm_product_id_type_for_google_analytics', 'wooptpm_product_id_type_for_google_analytics');
function wooptpm_product_id_type_for_google_analytics()
{
    return 'sku';
}

// add_filter('wgact_google_ads_conversion_identifiers', 'wgact_google_ads_conversion_identifiers');
function wgact_google_ads_conversion_identifiers($conversion_identifiers)
{
    $conversion_identifiers['CONVERSION_ID_2'] = 'CONVERSION_LABEL_2';
    $conversion_identifiers['CONVERSION_ID_3'] = 'CONVERSION_LABEL_3';
    return $conversion_identifiers;
}

// add_filter('wgdr_third_party_cookie_prevention', '__return_true');
// add_filter('wgact_cookie_prevention', '__return_true');


// add_filter('woopt_pm_analytics_parameters', 'adjust_analytics_parameters', 10,2);
//function adjust_analytics_parameters($analytics_parameters, $analytics_id)
//{
//    if ('G-YQBXCRGVLT' == $analytics_id) {
//        unset($analytics_parameters['anonymize_ip']);
//        // $analytics_parameters['link_attribution'] = 'true';
//        unset($analytics_parameters['link_attribution']);
//    }
//
//    return $analytics_parameters;
//}
//
//function adjust_analytics_parameters($analytics_parameters, $analytics_id)
//{
//    unset($analytics_parameters['anonymize_ip']);
//    // $analytics_parameters['link_attribution'] = 'true';
//    // unset($analytics_parameters['link_attribution']);
//    return $analytics_parameters;
//}
//
//function adjust_analytics_parameters($analytics_parameters, $analytics_id)
//{
//    if ('UA-39746956-9' == $analytics_id) {
//        error_log($analytics_id);
//        // unset($analytics_parameters['link_attribution']);
//        $analytics_parameters['link_attribution'] = [
//            'cookie_name'    => '_gaela',
//            'cookie_expires' => 60,
//            'levels'         => 2
//        ];
//    }
//    return $analytics_parameters;
//}