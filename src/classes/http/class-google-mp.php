<?php

namespace WGACT\Classes\Http;

use WC_Order_Refund;
use WGACT\Classes\Pixels\Google\Trait_Google;
use WGACT\Classes\Pixels\Trait_Product;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google_MP extends Http
{
//    use Trait_Product;
//    use Trait_Google;

    protected $cid_key;
    protected $cid;
    protected $use_debug_endpoint;

    public function __construct($options)
    {
        parent::__construct($options);

        add_action('wp_ajax_wooptpm_google_analytics_set_session_cid', [$this, 'wooptpm_google_analytics_set_session_cid']);
        add_action('wp_ajax_nopriv_wooptpm_google_analytics_set_session_cid', [$this, 'wooptpm_google_analytics_set_session_cid']);

        $this->use_debug_endpoint = apply_filters('wooptpm_google_mp_use_debug_endpoint', false);
    }

    protected function has_partial_refund_hit_already_been_sent($order_id, $refund_id, $mp_partial_refund_hit_key): bool
    {
        $post_meta = get_post_meta($order_id, $mp_partial_refund_hit_key, true);

        if ($post_meta) {
            return (in_array($refund_id, $post_meta));
        } else {
            return false;
        }
    }

    protected function save_partial_refund_hit_to_db($order_id, $refund_id, $mp_partial_refund_hit_key)
    {
        $post_meta = get_post_meta($order_id, $mp_partial_refund_hit_key, true);
        if (!is_array($post_meta)) $post_meta = [];
        $post_meta[] = $refund_id;
        update_post_meta($order_id, $mp_partial_refund_hit_key, $post_meta);
    }

    public function wooptpm_google_analytics_set_session_cid()
    {
//        error_log('set cid');

//        if (!check_ajax_referer('wooptpm-google-premium-only-nonce', 'nonce', false)) {
//            wp_send_json_error('Invalid security token sent.');
//            error_log('Invalid security token sent.');
//            wp_die();
//        }

        $target_id = filter_var($_POST['target_id'], FILTER_SANITIZE_STRING);
        $client_id = filter_var($_POST['client_id'], FILTER_SANITIZE_STRING);

//        error_log('target_id: ' . $target_id);
//        error_log('client_id: ' . $client_id);

        if (!WC()->session->has_session()) {
            WC()->session->set_customer_session_cookie(true);
        }

//        error_log('setting ga cid on session. Target ID: ' . $target_id . ', cid: ' . $client_id);

        WC()->session->set('google_cid_' . $target_id, $client_id);

        wp_send_json_success();

        wp_die(); // this is required to terminate immediately and return a proper response
    }

    public function set_cid_on_order($order, $cid_key)
    {
        // Get the cid if the client provides one, if not generate an anonymous one
        $cid = $this->get_cid_from_session($cid_key);

        if ($cid) {
            if (apply_filters('wooptpm_get_ga_cid_logger', false)) {
                wc_get_logger()->debug('Successfully received cid from session: ' . $cid, ['source' => 'wooptpm-cid']);
            }
        } else if ($_COOKIE['_ga']) {
            $cid = substr($_COOKIE['_ga'], 6);
            if (apply_filters('wooptpm_get_ga_cid_logger', false)) {
                wc_get_logger()->debug('Couldn\'t retrieve cid from WC session. Getting it from $_COOKIE[\'_ga\']: ' . $cid, ['source' => 'wooptpm-cid']);
            }
        } else {
            $cid = $this->get_random_cid();
            if (apply_filters('wooptpm_get_ga_cid_logger', false)) {
                wc_get_logger()->debug('Couldn\'t retrieve cid from WC session nor from $_COOKIE[\'_ga\']. Setting random cid: ' . $cid, ['source' => 'wooptpm-cid']);
            }
        }

        update_post_meta($order->get_id(), $cid_key, $cid);
    }

    public function get_cid_from_order($order, $cid_key): string
    {
        $cid = get_post_meta($order->get_id(), $cid_key, true);

        if ($cid) {
//            error_log('cid found: ' . $cid . ', cid_key: ' . $cid_key);
            return $cid;
        } else {
//            error_log('cid not found. Returning random');
            if (apply_filters('wooptpm_get_ga_cid_logger', false)) {
                wc_get_logger()->debug('Couldn\'t retrieve cid from order. Setting random cid', ['source' => 'wooptpm-cid']);
            }
            return $this->get_random_cid();
        }
    }

    public function is_cid_set_on_order($order, $cid_key): bool
    {
        $cid = get_post_meta($order->get_id(), $cid_key, true);

        if ($cid) {
            return true;
        } else {
            return false;
        }
    }

    protected function approve_purchase_hit_processing($order, $cid, $cid_key): bool
    {
        // Only approve, if the hit has not been sent already (check in db)
        // Also approve subscription renewals (cid is missing),
        // but don't approve normal orders before premium activation where the cid is missing

        if (
            get_post_meta($order->get_id(), $this->mp_purchase_hit_key) ||
            (
                $cid === null &&
                $this->is_cid_set_on_order($order, $cid_key) === false
            )
        ) {
            return false;
        } else {
            return true;
        }
    }

    protected function get_cid_from_session($cid_key)
    {
        if (WC()->session->get($cid_key)) {
            return WC()->session->get($cid_key);
        } else {
//            return bin2hex(random_bytes(10));
            return false;
        }
    }

    protected function get_random_cid(): string
    {
        return random_int(1000000000, 9999999999) . '.' . time();
    }
}