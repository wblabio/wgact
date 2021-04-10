<?php

add_filter('wooptpm_view_item_list_trigger_settings', 'wooptpm_view_item_list_trigger_settings');
function wooptpm_view_item_list_trigger_settings($settings)
{
    $settings['testMode']        = true;
//    $settings['backgroundColor'] = 'rgba(60,179,113)';
//    $settings['opacity']         = 0.5;
//    $settings['repeat']          = true;
//    $settings['threshold']       = 1;

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

add_filter('wooptpm_product_id_type_for_google_analytics', 'wooptpm_product_id_type_for_google_analytics');
function wooptpm_product_id_type_for_google_analytics()
{
    return 'sku';
}

// add_filter('wgact_google_ads_conversion_identifiers', 'wgact_add_conversion_identifiers');
function wgact_add_conversion_identifiers($conversion_identifiers)
{
    return array_replace($conversion_identifiers, [
        'CONVERSION_ID_2' => 'CONVERSION_LABEL_2',
        'CONVERSION_ID_3' => 'CONVERSION_LABEL_2'
    ]);
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