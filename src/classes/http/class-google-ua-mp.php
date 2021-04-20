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

    public function __construct()
    {
        parent::__construct();
    }

    public function send_purchase_hit($order)
    {
        // https://developer.wordpress.org/plugins/http-api/
        // https://stackoverflow.com/a/42868240/4688612
        // https://stackoverflow.com/a/31861577/4688612
        // WC session storage: https://stackoverflow.com/a/52422613/4688612¿
        // woocommerce_order_status_completed
        // woocommerce_payment_complete

        // https://developers.google.com/gtagjs/reference/api#get
        //
        //get the cid in the browser
        // gtag('get', 'UA-XXXXXXXX-Y', 'client_id', (clientID) => {
        //  sendOfflineEvent(clientID, "tutorial_begin")
        // });

        error_log('test mp');

        // only run, if the hit has not been sent already (check in db)

        // respect browser privacy settings
        // basically, if a visitor is not logged in and has blocked GA then don't send the hit
        // unless the shop owner overrides this for the shop
        if ($this->visitor_does_not_want_to_track_google_analytics() && $this->full_tracking_enabled() === false) {
            return;
        }

        // if available, save the cid in the order
        $this->save_cid_in_order_if_available($order);


        $google_host     = 'www.google-analytics.com';
        $google_endpoint = '/collect';
        $hit_testing     = true;
        $debug           = $hit_testing ? '/debug' : '';

        $url = 'https://' . $google_host . $debug . $google_endpoint;

        error_log('url: ' . $url);

        error_log('sending purchase hit');

        error_log('random cid: ' . bin2hex(random_bytes(10)));
//        $response = wp_remote_get( 'https://api.github.com/users/blobaugh' );
//        $body     = wp_remote_retrieve_body( $response );
//
//        error_log($body);

        $data_hit_type = [
            'v'   => 1,
            't'   => 'pageview',
            'tid' => (string)$this->options_obj->google->analytics->universal->property_id,
        ];


        $data_user_identifier = $this->get_user_identifier();

        // save the user identifier on the order in order to use it for subsequent subscription purchases

        $order_url = $order->get_checkout_order_received_url();

        $data_page = [
            'dh' => (string)parse_url($order_url, PHP_URL_HOST),
            'dp' => (string)parse_url($order_url, PHP_URL_PATH) . '?' . parse_url($order_url, PHP_URL_QUERY),
            'dt' => 'Order Received',
        ];

        $data_transaction = [
            'ti'  => (int)$order->get_order_number(),
            'ta'  => (string)get_bloginfo('name'),
            'tr'  => '37.39',   // transaction revenue
            'tt'  => '2.85',    // transaction tax
            'ts'  => '5.34',    // transaction shipping
            'tcc' => '',        // coupon code
            'cu'  => (string)$order->get_currency(),
            'pa'  => 'purchase',
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

        // maybe set the locale: https://www.php.net/manual/en/function.http-build-query.php#123906
        setlocale(LC_ALL, 'us_En');
        $request_url = $url . '?' . http_build_query($payload);

        error_log('request url: ' . $request_url);
//        $response = wp_remote_post($request_url , $this->post_request_args);
//        error_log(print_r($response, true));

        // if response is valid, save that the hit has been sent

        // if the response is invalid, queue for later
        // (retry 5 times. earliest in 5 minutes, then in an hour, then in 12 hours, then in a day, then in two days)

    }

    protected function get_products($order): array
    {
        $data       = [];
        $item_index = 1;

        foreach ($order->get_items() as $item_id => $item) {

            $product   = $item->get_product();
            $dyn_r_ids = $this->get_dyn_r_ids($product);

//            $name = '';

//            error_log('type: ' . $product->get_type());
            if ($product->get_type() === 'variation') {
                $parent_product = wc_get_product($product->get_parent_id());
                $name           = $parent_product->get_name();
            } else {
                $name = $product->get_name();
            }

            $data['pr' . $item_index . 'id'] = (string)$dyn_r_ids[$this->get_ga_id_type()];
            $data['pr' . $item_index . 'nm'] = $name;

            // https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters#pr_ca
            $data['pr' . $item_index . 'ca'] = implode(' | ', $this->get_product_category($product->get_id()));
            $data['pr' . $item_index . 'br'] = (string)$this->get_brand_name($product->get_id());
            $data['pr' . $item_index . 'va'] = $this->get_formatted_variant_text($product);
            $data['pr' . $item_index . 'ps'] = $item->get_quantity();

            $item_index++;
        }

        return $data;
    }

    protected function save_cid_in_order_if_available($order)
    {
        if (WC()->session->get('google_analytics_cid')) {

            update_post_meta($order->get_id(), 'google_analytics_cid', WC()->session->get('google_analytics_cid'));
        }
    }

    protected function get_user_identifier(): array
    {
        $data = [];

        if ($this->options_obj->google->user_id && is_user_logged_in()) {
            $data['uid'] = get_current_user_id();
        } else {
            $data['cid'] = $this->get_cid();
        }

        return $data;
    }

    protected function get_cid()
    {
        if (WC()->session->get('google_analytics_cid')) {
            return WC()->session->get('google_analytics_cid');
        } else {
            return bin2hex(random_bytes(10));
        }
    }

    protected function visitor_does_not_want_to_track_google_analytics(): bool
    {
        if (!WC()->session->get('google_analytics_cid')) {
            return true;
        } else {
            return false;
        }
    }
}