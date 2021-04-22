<?php

namespace WGACT\Classes\Http;

use WGACT\Classes\Pixels\Google\Trait_Google;
use WGACT\Classes\Pixels\Trait_Product;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google_UA_MP extends Http
{
    use Trait_Product;
    use Trait_Google;

    protected $cid_key;
    protected $cid;
    protected $url;

    public function __construct()
    {
        parent::__construct();

        $this->mp_purchase_hit_key = 'wooptpm_google_analytics_ua_mp_purchase_hit';
        $this->cid_key             = 'google_cid_' . $this->options_obj->google->analytics->universal->property_id;

        $google_host     = 'www.google-analytics.com';
        $google_endpoint = '/collect';
        $hit_testing     = false;
        $debug           = $hit_testing ? '/debug' : '';

        $this->url = 'https://' . $google_host . $debug . $google_endpoint;


        add_action('wp_ajax_wooptpm_google_analytics_set_session_cid', [$this, 'wooptpm_google_analytics_set_session_cid']);
        add_action('wp_ajax_nopriv_wooptpm_google_analytics_set_session_cid', [$this, 'wooptpm_google_analytics_set_session_cid']);
    }

    public function wooptpm_google_analytics_set_session_cid()
    {
        $target_id = $_POST['target_id'];
        $client_id = $_POST['client_id'];

//        error_log('target_id: ' . $target_id);
//        error_log('client_id: ' . $client_id);

        WC()->session->set('google_cid_' . $target_id, $client_id);

        wp_die(); // this is required to terminate immediately and return a proper response
    }

    public function send_purchase_hit($order, $cid = null)
    {
        // https://developers.google.com/analytics/devguides/collection/protocol/v1
        // https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters

        // Subscriptions
        // send a non-interaction hit for subsequent purchase conversions
        // https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters#ni
        // woocommerce_order_status_completed
        // woocommerce_payment_complete
        // https://woocommerce.github.io/code-reference/files/woocommerce-includes-class-wc-order.html#source-view.121


        // https://developer.wordpress.org/plugins/http-api/
        // https://stackoverflow.com/a/42868240/4688612
        // https://stackoverflow.com/a/31861577/4688612
        // WC session storage: https://stackoverflow.com/a/52422613/4688612¿

        // https://developers.google.com/gtagjs/reference/api#get

        // only run, if the hit has not been sent already (check in db)
        if (get_post_meta($order->get_id(), $this->mp_purchase_hit_key)) {
            return;
        }

//        error_log('processing Measure Protocol hit');

        $data_hit_type = [
            'v'   => 1,
            't'   => 'pageview',
            'tid' => (string)$this->options_obj->google->analytics->universal->property_id,
            'ni'  => true, // it's a non-interaction hit
        ];

        $data_user_identifier = $this->get_user_identifier($order, $cid);

        // save the user identifier on the order in order to use it for subsequent subscription purchases

        $order_url = $order->get_checkout_order_received_url();

        $data_page = [
            'dh' => (string)parse_url($order_url, PHP_URL_HOST),
            'dp' => (string)parse_url($order_url, PHP_URL_PATH) . '?' . parse_url($order_url, PHP_URL_QUERY),
            'dt' => 'Order Received',
        ];

        $data_transaction = [
            'ti' => (int)$order->get_order_number(),
            'ta' => (string)get_bloginfo('name'),
            'tr' => (float)$order->get_total(),   // transaction revenue
            'tt' => (float)$order->get_total_tax(),    // transaction tax
            'ts' => (float)$order->get_total_shipping(),    // transaction shipping
            //            'tcc' => '',        // coupon code
            'cu' => (string)$order->get_currency(),
            'pa' => 'purchase',
        ];

        $data_products = $this->get_products($order);

        $payload = array_merge(
            $data_hit_type,
            $data_user_identifier,
            $data_page,
            $data_transaction,
            $data_products
        );

        error_log(print_r($payload, true));

        // set the locale to avoid issues on a subset of shops
        // https://www.php.net/manual/en/function.http-build-query.php#123906
        setlocale(LC_ALL, 'us_En');
        $request_url = $this->url . '?' . http_build_query($payload);

//        error_log('request url: ' . $request_url);
//        error_log('sending purchase hit');

        wp_safe_remote_post($request_url, $this->post_request_args);

//        error_log('hit was sent');

//        if we're sending the request non-blocking we won't receive a response back
//        error_log(print_r($response, true));
//        error_log('response code: ' . wp_remote_retrieve_response_code($response));

        // Now we let the server know, that the hit has already been successfully sent.
        update_post_meta($order->get_id(), $this->mp_purchase_hit_key, true);
    }

    public function set_cid_on_order($order)
    {
        // Get the cid if the client provides one, if not generate an anonymous one
        $this->cid = $this->get_cid_from_session();

        update_post_meta($order->get_id(), $this->cid_key, $this->cid);
    }

    public function get_cid_from_order($order)
    {
        return get_post_meta($order->get_id(), $this->cid_key, true);
    }

    // https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters#pr_id
    protected function get_products($order): array
    {
        $data       = [];
        $item_index = 1;

        foreach ($order->get_items() as $item_id => $item) {

            $order_item_data = $this->get_order_item_data($item);

            $data['pr' . $item_index . 'id'] = $order_item_data['id'];
            $data['pr' . $item_index . 'nm'] = $order_item_data['name'];
            $data['pr' . $item_index . 'va'] = $order_item_data['variant'];
            $data['pr' . $item_index . 'br'] = $order_item_data['brand'];
            $data['pr' . $item_index . 'ca'] = $order_item_data['category'];
            $data['pr' . $item_index . 'qt'] = $order_item_data['quantity'];
            $data['pr' . $item_index . 'pr'] = $order_item_data['price'];

            $item_index++;
        }

        return $data;
    }

    protected function get_user_identifier($order, $cid): array
    {
        $data = [];

        // We only add this if also user_id tracking has been enabled in the shop.
        // Otherwise Google can't attribute the hit to the previous measurements.
        if ($this->options_obj->google->user_id && is_user_logged_in()) {
            $data['uid'] = get_current_user_id();
        }

        if ($cid) {
            // If this is a subscription renewal we take the cid from the original order
            $data['cid'] = $cid;
        } else {
            // We always send a cid. If we were able successfully capture one from the session,
            // we use that one. Otherwise we send a random cid.
            $data['cid'] = $this->get_cid_from_order($order);
        }

        return $data;
    }

    protected function get_cid_from_session()
    {
        if (WC()->session->get($this->cid_key)) {
            return WC()->session->get($this->cid_key);
        } else {
            return bin2hex(random_bytes(10));
        }
    }

    protected function visitor_does_not_want_to_track_google_analytics(): bool
    {
        if (!WC()->session->get($this->cid_key)) {
            return true;
        } else {
            return false;
        }
    }
}