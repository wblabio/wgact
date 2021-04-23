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

    public function __construct()
    {
        parent::__construct();

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

    protected function get_cid_from_session()
    {
        if (WC()->session->get($this->cid_key)) {
            return WC()->session->get($this->cid_key);
        } else {
            return bin2hex(random_bytes(10));
        }
    }
}