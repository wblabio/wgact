<?php

namespace WGACT\Classes\Pixels\Facebook;

use WGACT\Classes\Http\Facebook_CAPI;
use WGACT\Classes\Pixels\Pixel_Manager_Base;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Facebook_Pixel_Manager extends Pixel_Manager_Base
{
    protected $facebook_browser_pixel;
    protected $facebook_capi;

    public function __construct($options)
    {
        parent::__construct($options);

        add_action('wp_enqueue_scripts', [$this, 'wooptpm_facebook_front_end_scripts']);

        $this->facebook_browser_pixel = new Facebook_Browser_Pixel($options);
        if (wga_fs()->is__premium_only() && $this->options_obj->facebook->capi->token) {

            $this->facebook_capi = new Facebook_CAPI($options);

            // Save the Facebook session identifiers on the order so that we can use them later when the order gets paid or completed
            // https://woocommerce.github.io/code-reference/files/woocommerce-includes-class-wc-checkout.html#source-view.403
            add_action('woocommerce_checkout_order_created', [$this, 'facebook_save_session_identifiers_on_order__premium_only']);

            // Process the purchase through Facebook CAPI when they are paid,
            // or when they are manually completed.

            add_action('woocommerce_order_status_on-hold', [$this, 'facebook_capi_report_purchase__premium_only']);
            add_action('woocommerce_order_status_processing', [$this, 'facebook_capi_report_purchase__premium_only']);
            add_action('woocommerce_payment_complete', [$this, 'facebook_capi_report_purchase__premium_only']);
            add_action('woocommerce_order_status_completed', [$this, 'facebook_capi_report_purchase__premium_only']);

            // Process subscription renewals
            // https://docs.woocommerce.com/document/subscriptions/develop/action-reference/
//        add_action('woocommerce_subscription_renewal_payment_complete', [$this, 'facebook_capi_report_subscription_purchase_renewal__premium_only']);
        }

    }

    public function facebook_save_session_identifiers_on_order__premium_only($order)
    {
        $this->facebook_capi->set_identifiers_on_order($order);
    }

    public function facebook_capi_report_purchase__premium_only($order_id)
    {
        $order = wc_get_order($order_id);

        $this->facebook_capi->send_purchase_hit($order);
    }

    public function wooptpm_facebook_front_end_scripts()
    {
        wp_enqueue_script('wooptpm-facebook', plugin_dir_url(__DIR__) . '../../js/public/facebook.js', ['jquery', 'wooptpm'], WGACT_CURRENT_VERSION, true);

        if (wga_fs()->is__premium_only()) {
            wp_enqueue_script('wooptpm-facebook-premium-only', plugin_dir_url(__DIR__) . '../../js/public/facebook__premium_only.js', ['jquery', 'jquery-cookie', 'wooptpm', 'wooptpm-premium-only', 'wooptpm-facebook'], WGACT_CURRENT_VERSION, true);

            wp_localize_script(
                'wooptpm-facebook-premium-only',
                'wooptpm_facebook_premium_only_ajax_object',
                [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce'    => wp_create_nonce('wooptpm-facebook-premium-only-nonce'),
                ]
            );
        }
    }

    public function inject_everywhere()
    {
        $this->facebook_browser_pixel->inject_everywhere();
    }

    public function inject_search()
    {
        $this->facebook_browser_pixel->inject_search();
    }

    public function inject_product($product, $product_attributes)
    {
        $this->facebook_browser_pixel->inject_product($product, $product_attributes);
    }

    public function inject_cart($cart, $cart_total)
    {
        $this->facebook_browser_pixel->inject_cart($cart, $cart_total);
    }

    public function inject_order_received_page($order, $order_total, $is_new_customer)
    {
        $this->facebook_browser_pixel->inject_order_received_page($order, $order_total, $is_new_customer);
    }

    protected function inject_opening_script_tag()
    {
        echo PHP_EOL;
        echo '      <!-- START Facebook scripts -->' . PHP_EOL;
        echo '            <script>';
        echo PHP_EOL;
    }

    protected function inject_closing_script_tag()
    {
        echo PHP_EOL;
        echo '            </script>';
        echo PHP_EOL;
        echo '      <!-- END Facebook scripts -->' . PHP_EOL;
    }

    protected function inject_closing_script_after_tag()
    {

    }
}