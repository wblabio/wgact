<?php

namespace WGACT\Classes\Http;

use WC_Order_Refund;
use WGACT\Classes\Pixels\Google\Trait_Google;
use WGACT\Classes\Pixels\Trait_Product;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// https://developers.google.com/analytics/devguides/collection/protocol/v1
// https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters

// On initial order completion
// woocommerce_order_status_completed
// woocommerce_payment_complete
// https://woocommerce.github.io/code-reference/files/woocommerce-includes-class-wc-order.html#source-view.121

// Subscriptions
// https://stackoverflow.com/a/55912713/4688612
// https://stackoverflow.com/a/42798968/4688612


// https://developer.wordpress.org/plugins/http-api/
// https://stackoverflow.com/a/42868240/4688612
// https://stackoverflow.com/a/31861577/4688612
// WC session storage: https://stackoverflow.com/a/52422613/4688612¿

// https://developers.google.com/gtagjs/reference/api#get

class Google_MP_UA extends Google_MP
{
    use Trait_Product;
    use Trait_Google;

    public function __construct($options)
    {
        parent::__construct($options);

        $this->mp_purchase_hit_key       = 'wooptpm_google_analytics_ua_mp_purchase_hit';
        $this->mp_full_refund_hit_key    = 'wooptpm_google_analytics_ua_mp_full_refund_hit';
        $this->mp_partial_refund_hit_key = 'wooptpm_google_analytics_ua_mp_partial_refund_hit';

        $this->cid_key = 'google_cid_' . $this->options_obj->google->analytics->universal->property_id;

        $server_url             = 'www.google-analytics.com';
        $endpoint               = '/collect';
        $debug                  = $this->use_debug_endpoint ? '/debug' : '';
        $this->server_base_path = 'https://' . $server_url . $debug . $endpoint;

        $this->post_request_args['blocking'] = apply_filters('wooptpm_send_http_api_ga_ua_requests_blocking', $this->post_request_args['blocking'] );
    }

    public function send_purchase_hit($order, $cid = null)
    {
        // only approve, if several conditions are met
        if ($this->approve_purchase_hit_processing($order, $cid, $this->cid_key) === false) {
            return;
        }

//        error_log('processing GA UA Measurement Protocol purchase hit');

        $data_hit_type = [
            'v'   => 1,
            't'   => 'pageview',
            'tid' => (string)$this->options_obj->google->analytics->universal->property_id,
            'ni'  => true, // it's a non-interaction hit
        ];

        $data_user_identifier = $this->get_user_identifier($order, $cid);

        $order_url = $order->get_checkout_order_received_url();

        $data_page = [
            'dh' => (string)parse_url($order_url, PHP_URL_HOST),
            'dp' => (string)parse_url($order_url, PHP_URL_PATH) . '?' . parse_url($order_url, PHP_URL_QUERY),
            'dt' => 'Order Received',
        ];

        $data_transaction = [
            'ti' => (string)$order->get_order_number(),
            'ta' => (string)get_bloginfo('name'),
            'tr' => (float)$order->get_total(),   // transaction revenue
            'tt' => (float)$order->get_total_tax(),    // transaction tax
            'ts' => (float)$order->get_total_shipping(),    // transaction shipping
            //            'tcc' => '',        // coupon code
            'cu' => (string)$order->get_currency(),
            'pa' => 'purchase',
        ];

        $data_products = $this->get_all_order_products($order);

        $payload = array_merge(
            $data_hit_type,
            $data_user_identifier,
            $data_page,
            $data_transaction,
            $data_products
        );

//        error_log(print_r($payload, true));

        $this->send_hit($this->compile_request_url($payload));

        // Now we let the server know, that the hit has already been successfully sent.
        update_post_meta($order->get_id(), $this->mp_purchase_hit_key, true);
    }

    // https://developers.google.com/analytics/devguides/collection/protocol/v1/devguide#measuring-refunds
    public function send_full_refund_hit($order_id)
    {
        $order = wc_get_order($order_id);

        // only run, if the hit has not been sent already (check in db)
        if (get_post_meta($order->get_id(), $this->mp_full_refund_hit_key)) {
            return;
        }

//        error_log('processing Measure Protocol full refund hit');

        $data_hit_type = [
            'v'   => 1,
            't'   => 'event',
            'ec'  => 'Ecommerce',
            'ea'  => 'Refund',
            'tid' => (string)$this->options_obj->google->analytics->universal->property_id,
            'ni'  => true,
        ];

        $data_transaction = [
            'ti' => (string)$order->get_order_number(),
            'pa' => 'refund',
        ];

        $payload = array_merge(
            $data_hit_type,
            $data_transaction
        );

//        error_log(print_r($payload, true));

        $this->send_hit($this->compile_request_url($payload));

        // Now we let the server know, that the hit has already been successfully sent.
        update_post_meta($order->get_id(), $this->mp_full_refund_hit_key, true);
    }

    public function send_partial_refund_hit($order_id, $refund_id)
    {
        $order  = wc_get_order($order_id);
        $refund = new WC_Order_Refund($refund_id);

        // only run, if the hit has not been sent already (check in db)
        if ($this->has_partial_refund_hit_already_been_sent($order_id, $refund_id, $this->mp_partial_refund_hit_key)) {
            return;
        }

//        error_log('processing GA UA Measurement Protocol partial refund hit');

        $data_hit_type = [
            'v'   => 1,
            't'   => 'event',
            'ec'  => 'Ecommerce',
            'ea'  => 'Refund',
            'tid' => (string)$this->options_obj->google->analytics->universal->property_id,
            'ni'  => true, // it's a non-interaction hit
        ];

        $data_transaction = [
            'ti' => (string)$order->get_order_number(),
            'pa' => 'refund',
        ];

        $data_products = $this->get_all_refund_products($refund);

        $payload = array_merge(
            $data_hit_type,
            $data_transaction,
            $data_products
        );

//        error_log(print_r($payload, true));

        $this->send_hit($this->compile_request_url($payload));

        // Now we let the server know, that the hit has already been successfully sent.
        $this->save_partial_refund_hit_to_db($order_id, $refund_id, $this->mp_partial_refund_hit_key);
    }

    protected function compile_request_url($payload): string
    {
        // set the locale to avoid issues on a subset of shops
        // https://www.php.net/manual/en/function.http-build-query.php#123906
        setlocale(LC_ALL, 'us_En');
        return $this->server_base_path . '?' . http_build_query($payload);
    }



    // https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters#pr_id
    protected function get_all_order_products($order): array
    {
        $data       = [];
        $item_index = 1;

        foreach ($this->wooptpm_get_order_items($order) as $item_id => $item) {

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

    protected function get_all_refund_products($refund): array
    {
        $data       = [];
        $item_index = 1;

        foreach ($refund->get_items() as $item_id => $item) {

//            $product = new WC_Product($refund_item->get_product_id());

            $order_item_data = $this->get_order_item_data($item);

            $data['pr' . $item_index . 'id'] = $order_item_data['id'];
            $data['pr' . $item_index . 'qt'] = -1 * $order_item_data['quantity'];

            $item_index++;
        }

        return $data;
    }

    protected function get_user_identifier($order, $cid): array
    {
        $data = [];

        // We only add this if also user_id tracking has been enabled in the shop.
        // Otherwise Google can't attribute the hit to the previous measurements.
        if ($this->options_obj->google->user_id && $order->get_user_id()) {
            $data['uid'] = (string)$order->get_user_id();
        }

        if ($cid) {
            // If this is a subscription renewal we take the cid from the original order
            $data['cid'] = $cid;
        } else {
            // We always send a cid. If we were able successfully capture one from the session,
            // we use that one. Otherwise we send a random cid.
            $data['cid'] = $this->get_cid_from_order($order, $this->cid_key);
        }

        return $data;
    }
}