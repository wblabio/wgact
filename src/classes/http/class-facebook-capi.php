<?php

namespace WGACT\Classes\Http;

use WGACT\Classes\Pixels\Trait_Product;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Facebook_CAPI extends Http
{
    use Trait_Product;

    // https://developers.facebook.com/docs/marketing-api/conversions-api/parameters/fbp-and-fbc

    // !! make sure to retrieve the cookie for the domain of the shop

    protected $fbp_key;
    protected $fbc_key;
    protected $capi_purchase_hit_key;
    protected $test_event_code;
    protected $pixel_name;
    protected $request_url;

    public function __construct()
    {
        parent::__construct();

        $pixel_id     = $this->options_obj->facebook->pixel_id;
        $access_token = $this->options_obj->facebook->capi_token;

        $this->fbp_key               = 'facebook_fbp_' . $pixel_id;
        $this->fbc_key               = 'facebook_fbc_' . $pixel_id;
        $this->capi_purchase_hit_key = 'wooptpm_facebook_capi_purchase_hit';
        $this->test_event_code       = apply_filters('wooptpm_facebook_capi_test_event_code', false);
        $this->pixel_name            = 'facebook';

        $server_url        = 'graph.facebook.com';
        $api_version       = 'v10.0';
        $endpoint          = 'events';
        $this->request_url = 'https://' . $server_url . '/' . $api_version . '/' . $pixel_id . '/' . $endpoint . '?access_token=' . $access_token;

//        error_log($this->request_url);

        $this->post_request_args['blocking'] = apply_filters('wooptpm_send_http_api_facebook_capi_requests_blocking', $this->post_request_args['blocking']);
        $this->post_request_args['headers']  = [
            'Content-Type' => 'application/json; charset=utf-8',
        ];

        add_action('wp_ajax_wooptpm_facebook_set_session_identifiers', [$this, 'wooptpm_facebook_set_session_identifiers']);
        add_action('wp_ajax_nopriv_wooptpm_facebook_set_session_identifiers', [$this, 'wooptpm_facebook_set_session_identifiers']);
    }

    // We pass the $order, $fbp and $fbc
    // $fbp and $fbc are only necessary if it is a subscription renewal order
    // https://developers.facebook.com/docs/marketing-api/conversions-api/using-the-api#send
    // https://developers.facebook.com/docs/marketing-api/conversions-api/parameters
    // https://developers.facebook.com/docs/marketing-api/conversions-api/parameters/server-event#event-name
    public function send_purchase_hit($order, $fbp = null, $fbc = null)
    {
//        error_log('processing Facebook CAPI purchase hit');
        // only run, if the hit has not been sent already (check in db)
//        error_log('key exists: ' . get_post_meta($order->get_id(), $this->capi_purchase_hit_key, true));

        if (get_post_meta($order->get_id(), $this->capi_purchase_hit_key)) {
//            error_log('Facebook CAPI purchase hit already processed');
            return;
        } else {
//            error_log('Facebook CAPI purchase hit not yet processed. Continue...');
        }


        $event_data = [
            'event_name'       => 'Purchase',
            'event_time'       => (int)time(),
            'event_id'         => (string)$order->get_order_number(),
            'event_source_url' => '',
            'opt_out'          => false,
            'action_source'    => 'website',
            'event_source_url' => get_site_url(),
        ];

        // add user data
        $event_data['user_data'] = [
            //                'client_ip_address' => '',
                            'client_user_agent' => $_SERVER['HTTP_USER_AGENT'],
            //                'em'                => '', // email hashed
            'fbp' => $fbp ? $fbp : $this->get_fbp_from_order($order),
        ];

        if ($this->get_fbc_from_order($order)) {
            $event_data['user_data']['fbc'] = $this->get_fbc_from_order($order);
        }

        // add order data
        $event_data['custom_data'] = [
            'value'        => (float)$order->get_total(),
            'currency'     => (string)$order->get_currency(),
            'content_ids'  => (array)$this->get_order_item_ids($order),
            'content_type' => 'product'
        ];

        // data processing options
        // https://developers.facebook.com/docs/marketing-apis/data-processing-options#conversions-api-and-offline-conversions-api

        $payload = [
            'data' => [$event_data],
        ];

        if ($this->test_event_code) {
            error_log('Facebook CAPI test event code enabled');
            $payload['test_event_code'] = $this->test_event_code;
        }

//        error_log(print_r($payload, true));

        $this->send_hit($this->request_url, $payload);

        // Now we let the server know, that the hit has already been successfully sent.
        update_post_meta($order->get_id(), $this->capi_purchase_hit_key, true);
    }

    protected function get_all_order_products($order): array
    {
        $items = [];

        foreach ($order->get_items() as $item_id => $item) {

            $order_item_data = $this->get_order_item_data($item);

            $item_details = [
                'id'         => $order_item_data['id'],
                //                'item_name'     => $order_item_data['name'],
                //                'coupon'        => '',
                //                'discount'      => '',
                //                'affiliation'   => '',
                //                'item_brand'    => $order_item_data['brand'],
                //                'item_category' => $order_item_data['category'],
                //                'item_variant'  => $order_item_data['variant'],
                'item_price' => $order_item_data['price'],
                //                'currency'      => '',
                'quantity'   => $order_item_data['quantity'],
            ];

            array_push($items, $item_details);
        }

        return $items;
    }


    public function wooptpm_facebook_set_session_identifiers()
    {
        if (!check_ajax_referer('wooptpm-facebook-premium-only-nonce', 'nonce', false)) {
            wp_send_json_error('Invalid security token sent.');
            error_log('Invalid security token sent.');
            wp_die();
        }

        if (isset($_POST['fbp'])) {
            $fbp = filter_var($_POST['fbp'], FILTER_SANITIZE_STRING);
            WC()->session->set($this->fbp_key, $fbp);
        }

        if (isset($_POST['fbc'])) {
            $fbc = filter_var($_POST['fbc'], FILTER_SANITIZE_STRING);
            WC()->session->set($this->fbc_key, $fbc);
        }

        wp_die(); // this is required to terminate immediately and return a proper response
    }

    public function set_identifiers_on_order($order)
    {
        // Get the fbp cookie if the client provides one, if not generate an anonymous one
        $fbp = $this->get_fbp_from_session();
        update_post_meta($order->get_id(), $this->fbp_key, $fbp);

        // Only save the fbc cookie if one is available
        if ($this->get_fbc_from_session()) {
            $fbc = $this->get_fbc_from_session();
            update_post_meta($order->get_id(), $this->fbc_key, $fbc);
        }
    }

    protected function get_fbp_from_session()
    {
        if (WC()->session->get($this->fbp_key)) {
            return WC()->session->get($this->fbp_key);
        } else {
            return $this->get_random_fbp();
        }
    }

    public function get_fbp_from_order($order): string
    {
        $fbp = get_post_meta($order->get_id(), $this->fbp_key, true);

        if ($fbp) {
            return $fbp;
        } else {
            return $this->get_random_fbp();
        }
    }

    public function get_fbc_from_order($order): string
    {
        $fbc = get_post_meta($order->get_id(), $this->fbc_key, true);

        if ($fbc) {
            return $fbc;
        } else {
            return false;
        }
    }

    protected function get_fbc_from_session()
    {
        if (WC()->session->get($this->fbc_key)) {
            return WC()->session->get($this->fbc_key);
        } else {
            return false;
        }
    }

    protected function get_random_fbp(): string
    {
        // Facebook suggests to user their SDK to generate the random number
        // but we won't do that. If we want anonymity we need to generate the random
        // number on our own terms.
        // https://developers.facebook.com/docs/marketing-api/conversions-api/parameters/fbp-and-fbc/
        $random_fbp = [
            'version'         => 'fb',
            'subdomain_index' => 1,
            'creation_time'   => time(),
            'random_number'   => random_int(1000000000, 9999999999),
        ];

        return implode('.', $random_fbp);
    }
}