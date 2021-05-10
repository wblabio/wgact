<?php

namespace WGACT\Classes\Http;

use WC_Geolocation;
use WGACT\Classes\Pixels\Trait_Product;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Facebook_CAPI extends Http
{
    use Trait_Product;

    protected $fbp_key;
    protected $fbc_key;
    protected $facebook_key;
    protected $capi_purchase_hit_key;
    protected $test_event_code;
    protected $pixel_name;
    protected $request_url;
    protected $opt_out;

    public function __construct($options)
    {
        parent::__construct($options);

        $pixel_id     = $this->options_obj->facebook->pixel_id;
        $access_token = $this->options_obj->facebook->capi->token;

        $this->fbp_key      = 'facebook_fbp_' . $pixel_id;
        $this->fbc_key      = 'facebook_fbc_' . $pixel_id;
        $this->facebook_key = 'facebook_user_identifiers_' . $pixel_id;

        $this->test_event_code = apply_filters('wooptpm_facebook_capi_test_event_code', false);

        $this->capi_purchase_hit_key = 'wooptpm_facebook_capi_purchase_hit';
        $this->pixel_name            = 'facebook';

        $server_url        = 'graph.facebook.com';
        $api_version       = 'v10.0';
        $endpoint          = 'events';
        $this->request_url = 'https://' . $server_url . '/' . $api_version . '/' . $pixel_id . '/' . $endpoint . '?access_token=' . $access_token;

        $this->opt_out = apply_filters('wooptpm_facebook_capi_ads_delivery_opt_out', false);

//        error_log($this->request_url);

        $this->post_request_args['blocking'] = apply_filters('wooptpm_send_http_api_facebook_capi_requests_blocking', $this->post_request_args['blocking']);
        $this->post_request_args['headers']  = [
            'Content-Type' => 'application/json; charset=utf-8',
        ];

        add_action('wp_ajax_wooptpm_facebook_set_session_identifiers', [$this, 'wooptpm_facebook_set_session_identifiers']);
        add_action('wp_ajax_nopriv_wooptpm_facebook_set_session_identifiers', [$this, 'wooptpm_facebook_set_session_identifiers']);

        add_action('wp_ajax_wooptpm_facebook_capi_event', [$this, 'wooptpm_facebook_capi_event']);
        add_action('wp_ajax_nopriv_wooptpm_facebook_capi_event', [$this, 'wooptpm_facebook_capi_event']);
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

        // privacy filter
        // if user didn't provide fbp he probably doesn't want to be tracked -> stop processing
        // if fbp is available, continue with minimally required identifiers
        // the shop owner can choose to add all available identifiers
        // give the shop owner the choice to filter the user_data, based on IP

        $facebook_identifiers = $this->get_identifiers_from_order($order);

        // If fbp is missing and the store owner didn't instruct to process anonymous sessions, we stop.
        if (!array_key_exists('fbp', $facebook_identifiers) && !$this->options_obj->facebook->capi->user_transparency->process_anonymous_hits) {
//            error_log('fbp missing. Store owner doesn\'t want anonymous hits to be processed. Purchase hit prevented.');
            return;
        }

        // Add event data
        $capi_event_data = [
            'event_name'       => 'Purchase',
            //            'event_time'       => (int)time(), // try to match browser event_time
            'event_time'       => $facebook_identifiers['event_time'], // try to match browser event_time
            'event_id'         => (string)$order->get_order_number(),
            'opt_out'          => (bool)$this->opt_out,
            'action_source'    => 'website',
            //            'event_source_url' => (string)get_site_url(),
            'event_source_url' => $order->get_checkout_order_received_url(),
        ];

        // Add user data
        $capi_event_data['user_data'] = $this->get_user_data($facebook_identifiers, $order);

//        if ($this->get_fbc_from_order($order)) {
//            $event_data['user_data']['fbc'] = $this->get_fbc_from_order($order);
//        }

        // add order data
        $capi_event_data['custom_data'] = [
            'value'        => (float)$order->get_total(),
            'currency'     => (string)$order->get_currency(),
            'content_ids'  => (array)$this->get_order_item_ids($order),
            'content_type' => 'product'
        ];

        // data processing options
        $capi_event_data = $this->add_data_processing_options($capi_event_data);

        $payload = [
            'data' => [$capi_event_data],
        ];

        if ($this->test_event_code) {
            error_log('Facebook CAPI test event code enabled');
            $payload['test_event_code'] = $this->test_event_code;
        }

//        error_log('payload');
//        error_log(print_r($payload, true));

        $this->send_hit($this->request_url, $payload);

        // Now we let the server know, that the hit has already been successfully sent.
        update_post_meta($order->get_id(), $this->capi_purchase_hit_key, true);
    }

    public function send_event_hit($browser_event_data)
    {
//        error_log('processing Facebook CAPI event hit');

        // privacy filter
        // if user didn't provide fbp he probably doesn't want to be tracked -> stop processing
        // if fbp is available, continue with minimally required identifiers
        // the shop owner can choose to add all available identifiers
        // give the shop owner the choice to filter the user_data, based on IP

        // If fbp is missing and the store owner didn't instruct to process anonymous sessions, we stop.
        if (!array_key_exists('fbp', $browser_event_data['user_data']) && !$this->options_obj->facebook->capi->user_transparency->process_anonymous_hits) {
            error_log('fbp missing. Store owner doesn\'t want anonymous hits to be processed. Purchase hit prevented.');
            return;
        }

        // Add event data
        $capi_event_data = [
            'event_name'       => (string)$browser_event_data['event_name'],
            'event_id'         => (string)$browser_event_data['event_id'],
            'event_time'       => (int)time(),
            'opt_out'          => (bool)$this->opt_out,
            'action_source'    => 'website',
            'event_source_url' => (string)$browser_event_data['event_source_url'],
        ];

        // Add user data
        $browser_event_data['user_data']['client_ip_address'] = $this->get_user_ip();
        $capi_event_data['user_data']                         = $this->get_user_data($browser_event_data['user_data']);

        // add product data
        if (array_key_exists('product_data', $browser_event_data)) {
            $capi_event_data['custom_data'] = [
                'value'        => (float)$browser_event_data['product_data']['quantity'] * $browser_event_data['product_data']['price'],
                'currency'     => (string)$browser_event_data['product_data']['currency'],
                'content_name' => (string)$browser_event_data['product_data']['name'],
                'content_ids'  => (array)$browser_event_data['product_id'],
                'content_type' => 'product'
            ];
        }

        // data processing options
        $capi_event_data = $this->add_data_processing_options($capi_event_data);

        $payload = [
            'data' => [$capi_event_data],
        ];

        if ($this->test_event_code) {
            error_log('Facebook CAPI test event code enabled');
            $payload['test_event_code'] = $this->test_event_code;
        }

//        error_log('payload');
//        error_log(print_r($payload, true));

        $this->send_hit($this->request_url, $payload);
    }

    // https://developers.facebook.com/docs/marketing-apis/data-processing-options
    // https://developers.facebook.com/docs/marketing-apis/data-processing-options#conversions-api-and-offline-conversions-api
    protected function add_data_processing_options($capi_event_data): array
    {
        return array_merge($capi_event_data, apply_filters('wooptpm_facebook_capi_data_processing_options', []));
    }

    protected function get_user_data($facebook_identifiers, $order = null): array
    {
        $user_data = [];

//        error_log(print_r($facebook_identifiers, true));

        // If fbp exists we set all real data
        // If fbp doesn't exist, we only set required fields with random data
        if (array_key_exists('fbp', $facebook_identifiers)) {

            // set client_user_agent
            $user_data['client_user_agent'] = $facebook_identifiers['client_user_agent'];

            // set client_ip_address
            if (
                $this->options_obj->facebook->capi->user_transparency->send_additional_client_identifiers &&
                array_key_exists('client_ip_address', $facebook_identifiers) &&
                $facebook_identifiers['client_ip_address']
            ) {
                $user_data['client_ip_address'] = $facebook_identifiers['client_ip_address'];
            }

            // set fbp
            $user_data['fbp'] = $facebook_identifiers['fbp'];
        } else {
            $user_data['fbp']               = $this->get_random_fbp();
            $user_data['client_user_agent'] = (new User_Agent())->get_random_user_agent();
        }

        // set fbc
        if (array_key_exists('fbc', $facebook_identifiers)) $user_data['fbc'] = $facebook_identifiers['fbc'];

        // https://developers.facebook.com/docs/marketing-api/audiences/guides/custom-audiences/#example_sha256
        if ($order) {
            if ($this->options_obj->facebook->capi->user_transparency->send_additional_client_identifiers) {
                // set user_id
                $user_data['external_id'] = hash('sha256', $order->get_user_id());

                // set em (email)
                $wp_user_info    = get_userdata($order->get_user_id());
                $user_data['em'] = hash('sha256', $wp_user_info->user_email);
            }
        } else if (get_current_user_id() !== 0) {
            if ($this->options_obj->facebook->capi->user_transparency->send_additional_client_identifiers) {
                // set user_id
                $user_data['external_id'] = hash('sha256', get_current_user_id());

                // set em (email)
                $wp_user_info    = get_userdata(get_current_user_id());
                $user_data['em'] = hash('sha256', $wp_user_info->user_email);
            }
        }

//        error_log(print_r($user_data, true));

        return $user_data;
    }

    // https://developers.facebook.com/docs/marketing-api/conversions-api/parameters/fbp-and-fbc
    public function wooptpm_facebook_set_session_identifiers()
    {
        if (!check_ajax_referer('wooptpm-facebook-premium-only-nonce', 'nonce', false)) {
            wp_send_json_error('Invalid security token sent.');
            error_log('Invalid security token sent.');
            wp_die();
        }

//        error_log('fbp from browser: ' . $_POST['fbp']);

        $facebook_identifiers = [];

        if (isset($_POST['fbp'])) {
            $facebook_identifiers['fbp'] = filter_var($_POST['fbp'], FILTER_SANITIZE_STRING);
        }

        if (isset($_POST['fbc'])) {
            $facebook_identifiers['fbp'] = filter_var($_POST['fbc'], FILTER_SANITIZE_STRING);
        }

        if ($this->get_user_ip()) {
            $facebook_identifiers['client_ip_address'] = $this->get_user_ip();
        }

        // If the user doesn't provide a fbp we can safely assume he doesn't want to be tracked.
        // But the user agent is a required field in order to send a valid hit to FB.
        // User agents are not exactly unique per user, but unique enough to be able to narrow
        // down the identity of a user well enough. Only few additional fingerprints (like IP) are needed to
        // enable FB to match a user.
        // Since our user, who is not providing fbp, wants to stay anonymous, we don't sent the real
        // user agent to FB, but a random one.
        if (isset($_POST['fbp'])) {
            $facebook_identifiers['client_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        } else {
            $facebook_identifiers['client_user_agent'] = (new User_Agent())->get_random_user_agent();
        }

//        error_log('echo facebook identifiers');
//        error_log(print_r($facebook_identifiers, true));

        WC()->session->set($this->facebook_key, $facebook_identifiers);

        wp_die(); // this is required to terminate immediately and return a proper response
    }

    public function wooptpm_facebook_capi_event()
    {
        if (!check_ajax_referer('wooptpm-facebook-premium-only-nonce', 'nonce', false)) {
            wp_send_json_error('Invalid security token sent.');
            error_log('Invalid security token sent.');
            wp_die();
        }

        if (!isset($_POST['data'])) {
            wp_die();
        }

        $browser_event_data = $_POST['data'];

        // use data to send FB CAPI event
//        error_log('data');
//        error_log(print_r($browser_event_data,true));

        $this->send_event_hit($browser_event_data);

        wp_die(); // this is required to terminate immediately and return a proper response
    }

    protected function get_user_ip(): string
    {
        // only set the IP if it is a public address
        $ip = filter_var(WC_Geolocation::get_ip_address(), FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);

        // Return the IP if it is a public address, otherwise get the external IP
        // Required for testing
        if ($ip) {
            return $ip;
        } else {
            return WC_Geolocation::get_external_ip_address();
        }
//        return filter_var(WC_Geolocation::get_ip_address(), FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    public function set_identifiers_on_order($order)
    {
        // Get the fbp cookie if the client provides one, if not generate an anonymous one
//        $fbp = $this->get_fbp_from_session();
//        update_post_meta($order->get_id(), $this->fbp_key, $fbp);

        // Only save the fbc cookie if one is available
//        if ($this->get_fbc_from_session()) {
//            $fbc = $this->get_fbc_from_session();
//            update_post_meta($order->get_id(), $this->fbc_key, $fbc);
//        }

        $facebook_identifiers = WC()->session->get($this->facebook_key);

        $facebook_identifiers['event_time'] = (int)time();

//        error_log('setting facebook identifiers on order');
//        error_log(print_r($facebook_identifiers, true));

        update_post_meta($order->get_id(), $this->facebook_key, $facebook_identifiers);
    }

    protected function get_identifiers_from_order($order): array
    {
        $facebook_identifiers = get_post_meta($order->get_id(), $this->facebook_key, true);

//        error_log('echo facebook identifiers');
//        error_log(print_r($facebook_identifiers, true));
//        error_log('fbp from server: ' . $facebook_identifiers['fbp']);

        return $facebook_identifiers;

//        return get_post_meta($order->get_id(), $this->facebook_key);
    }

    // Facebook suggests to user their SDK to generate the random fbp
    // but we won't do that. If we want true anonymity we need to generate the random
    // number on our own terms.
    // https://developers.facebook.com/docs/marketing-api/conversions-api/parameters/fbp-and-fbc/
    protected function get_random_fbp(): string
    {
        $random_fbp = [
            'version'         => 'fb',
            'subdomain_index' => 1,
            'creation_time'   => time(),
            'random_number'   => random_int(1000000000, 9999999999),
        ];

        return implode('.', $random_fbp);
    }
}