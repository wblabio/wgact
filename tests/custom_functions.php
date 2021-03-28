<?php

add_filter('wooptpm_dyn_r_product_ids', 'return_wooptpm_dyn_r_product_ids', 10, 2);
function return_wooptpm_dyn_r_product_ids($dyn_r_ids, $product)
{
    // error_log(print_r($dyn_r_ids,true ));
    $dyn_r_ids['custom1'] = 'custm_googXX_' . $product->get_id();
    $dyn_r_ids['custom2'] = 'custm_fbXX_' . $product->get_id();
    $dyn_r_ids['custom3'] = 'custm_pinterestXX_' . $product->get_id();


    // error_log(print_r($dyn_r_ids, true));
    return $dyn_r_ids;
}


add_filter('wooptpm_dyn_r_google_ads_id_type', 'return_wooptpm_dyn_r_google_id_type');
function return_wooptpm_dyn_r_google_id_type($id_type): string
{
    return 'custom1';
}

add_filter('wooptpm_dyn_r_facebook_id_type', 'return_wooptpm_dyn_r_facebook_id_type');
function return_wooptpm_dyn_r_facebook_id_type($id_type): string
{
    return 'custom2';
}

add_filter('wooptpm_dyn_r_pinterest_id_type', 'return_wooptpm_dyn_r_pinterest_id_type');
function return_wooptpm_dyn_r_pinterest_id_type($id_type): string
{
    return 'custom3';
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