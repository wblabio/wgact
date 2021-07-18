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

class Google_MP_GA4 extends Google_MP
{
    use Trait_Product;
    use Trait_Google;

    protected $event_debug_mode;

    public function __construct($options)
    {
        parent::__construct($options);

        $this->mp_purchase_hit_key       = 'wooptpm_google_analytics_4_mp_purchase_hit';
        $this->mp_full_refund_hit_key    = 'wooptpm_google_analytics_4_mp_full_refund_hit';
        $this->mp_partial_refund_hit_key = 'wooptpm_google_analytics_4_mp_partial_refund_hit';

        $measurement_id = $this->options_obj->google->analytics->ga4->measurement_id;

        $this->cid_key = 'google_cid_' . $measurement_id;

        $server_url             = 'www.google-analytics.com';
        $endpoint               = '/mp/collect';
        $api_secret             = $this->options_obj->google->analytics->ga4->api_secret;
        $this->event_debug_mode = apply_filters('wooptpm_enable_ga_4_mp_event_debug_mode', false);
        $debug                  = $this->use_debug_endpoint ? '/debug' : '';
        $this->server_base_path = 'https://' . $server_url . $debug . $endpoint . '?measurement_id=' . $measurement_id . '&api_secret=' . $api_secret;

        $this->post_request_args['blocking'] = apply_filters('wooptpm_send_http_api_ga_4_requests_blocking', $this->post_request_args['blocking']);
    }

    // We pass the $order and the $cid
    // The $cid is only necessary if it is a subscription renewal order
    // https://developers.google.com/analytics/devguides/collection/protocol/ga4/reference/events#purchase
    public function send_purchase_hit($order, $cid = null)
    {

//        error_log('processing GA 4 Measurement Protocol purchase hit');

        // Only run, if the hit has not been sent already (check in db)
        // Also run it on subscription renewals,
        // but not on orders before premium activation (orders missing a cid)

        if ($this->approve_purchase_hit_processing($order, $cid, $this->cid_key) === false) {
            return;
        }

//        error_log('GA 4, no previous order hit registered, continue...');

        $payload = [
            'client_id'            => $this->get_cid_from_order($order, $this->cid_key),
            'non_personalized_ads' => false,
            'events'               => [
                'name'   => 'purchase',
                'params' => [
                    'transactions_id' => (string)$order->get_order_number(),
                    'value'           => (float)$order->get_total(),
                    'currency'        => (string)$order->get_currency(),
                    'tax'             => (float)$order->get_total_tax(),
                    'shipping'        => (float)$order->get_total_shipping(),
                    'affiliation'     => (string)get_bloginfo('name'),
                    //                    'coupon' => '',
                    'items'           => (array)$this->get_all_order_products($order),
                ],
            ]
        ];

        if ($this->options_obj->google->user_id && $order->get_user_id()) {
            $payload['user_id'] = (string)$order->get_user_id();
        }

        if ($this->event_debug_mode) {
            error_log('GA 4 event debug mode enabled');

            $payload['events']['params']['debug_mode'] = true;
        }

//        error_log(print_r($payload, true));

        $this->send_hit($this->server_base_path, $payload);

        // Now we let the server know, that the hit has already been successfully sent.
        update_post_meta($order->get_id(), $this->mp_purchase_hit_key, true);
    }

    public function send_full_refund_hit($order_id)
    {
        $order = wc_get_order($order_id);

        // only run, if the hit has not been sent already (check in db)
        if (get_post_meta($order->get_id(), $this->mp_full_refund_hit_key)) {
            return;
        }

//        error_log('processing Measure Protocol full refund hit');

        $payload = [
            'client_id' => (string)$this->get_cid_from_order($order, $this->cid_key),
            'events'    => [
                'name'   => 'refund',
                'params' => [
                    'transactions_id' => (string)$order->get_order_number(),
                ],
            ]
        ];

        if ($this->event_debug_mode) {
//            error_log('event debug mode enabled');
            $payload['events']['params']['debug_mode'] = true;
        }

//        error_log(print_r($payload, true));

        $this->send_hit($this->server_base_path, $payload);


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

        $payload = [
            'client_id' => $this->get_cid_from_order($order, $this->cid_key),
            'events'    => [
                'name'   => 'refund',
                'params' => [
                    'transactions_id' => (string)$order->get_order_number(),
                    'items'           => (array)$this->get_all_order_products($refund),
                ],
            ]
        ];

        if ($this->event_debug_mode) {
//            error_log('event debug mode enabled');
            $payload['events']['params']['debug_mode'] = true;
        }

//        error_log(print_r($payload, true));

        $this->send_hit($this->server_base_path, $payload);

        // Now we let the server know, that the hit has already been successfully sent.

        $this->save_partial_refund_hit_to_db($order_id, $refund_id, $this->mp_partial_refund_hit_key);
    }




    protected function get_all_order_products($order): array
    {
        $items = [];

        foreach ($this->wooptpm_get_order_items($order) as $item_id => $item) {

            $order_item_data = $this->get_order_item_data($item);

            $item_details = [
                'item_id'       => $order_item_data['id'],
                'item_name'     => $order_item_data['name'],
                //                'coupon'        => '',
                //                'discount'      => '',
                //                'affiliation'   => '',
                'item_brand'    => $order_item_data['brand'],
                'item_variant'  => $order_item_data['variant'],
                'price'         => $order_item_data['price'],
                //                'currency'      => '',
                'quantity'      => $order_item_data['quantity'],
            ];

            $item_details = $this->add_categories_to_ga4_product_items($item_details, $order_item_data['category_array']);

            array_push($items, $item_details);
        }

        return $items;
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
}