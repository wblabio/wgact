<?php

namespace WGACT\Classes\Pixels\Google;

use WC_Order;
use WC_Order_Refund;
use WC_Product;
use WGACT\Classes\Http\Google_MP_GA4;
use WGACT\Classes\Http\Google_MP_UA;
use WGACT\Classes\Pixels\Pixel_Manager_Base;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google_Pixel_Manager extends Pixel_Manager_Base
{
    use Trait_Google;

    private $google_pixel;
    private $google_ads_pixel;
    private $google_analytics_ua_standard_pixel;
    private $google_analytics_4_standard_pixel;
    private $google_analytics_ua_eec_pixel;
    private $google_analytics_ua_http_mp;
    private $google_analytics_4_http_mp;
//    private $google_analytics_ua_refund_pixel;
    private $google_analytics_4_eec_pixel;

    public function __construct()
    {
        parent::__construct();

        $this->google_pixel = new Google();
        if ($this->is_google_ads_active()) if ($this->is_google_ads_active()) $this->google_ads_pixel = new Google_Ads();

        add_action('wp_enqueue_scripts', [$this, 'google_front_end_scripts']);


        if (!wga_fs()->is__premium_only() || !$this->options_obj->google->analytics->eec) {
            if ($this->is_google_analytics_ua_active()) $this->google_analytics_ua_standard_pixel = new Google_Analytics_UA_Standard();
            if ($this->is_google_analytics_4_active()) $this->google_analytics_4_standard_pixel = new Google_Analytics_4_Standard();
        } else {

            $this->google_analytics_ua_eec_pixel = new Google_Analytics_UA_EEC();
//            $this->google_analytics_ua_refund_pixel = new Google_Analytics_UA_Refund_Pixel();

            $this->google_analytics_4_eec_pixel = new Google_Analytics_4_EEC();

            if ($this->is_google_analytics_active()) {

                // woocommerce_order_status_refunded
                // woocommerce_order_refunded
                // woocommerce_order_partially_refunded
                // https://github.com/woocommerce/woocommerce/blob/b19500728b4b292562afb65eb3a0c0f50d5859de/includes/wc-order-functions.php#L614
                // woocommerce_order_fully_refunded
                // https://github.com/woocommerce/woocommerce/blob/b19500728b4b292562afb65eb3a0c0f50d5859de/includes/wc-order-functions.php#L616
                // how to tell if order is fully refunded
                // https://github.com/woocommerce/woocommerce/blob/b19500728b4b292562afb65eb3a0c0f50d5859de/includes/wc-order-functions.php#L774

                $this->google_analytics_ua_http_mp = new Google_MP_UA();
                $this->google_analytics_4_http_mp  = new Google_MP_GA4();

//                error_log('running mp scripts');

//                add_action('woocommerce_order_refunded', [$this, 'google_analytics_eec_action_woocommerce_order_refunded__premium_only'], 10, 2);

                // Save the Google cid on the order so that we can use it later when the order gets paid or completed
                // https://woocommerce.github.io/code-reference/files/woocommerce-includes-class-wc-checkout.html#source-view.403
                add_action('woocommerce_checkout_order_created', [$this, 'google_analytics_save_cid_on_order__premium_only']);

                // Process the purchase through the GA Measurement Protocol when they are paid,
                // or when they are manually completed.
                add_action('woocommerce_payment_complete', [$this, 'google_analytics_mp_report_purchase__premium_only']);
                add_action('woocommerce_order_status_completed', [$this, 'google_analytics_mp_report_purchase__premium_only']);

                // Process total an partial refunds
                add_action('woocommerce_order_fully_refunded', [$this, 'google_analytics_mp_send_full_refund__premium_only'], 10, 2);
                add_action('woocommerce_order_partially_refunded', [$this, 'google_analytics_mp_send_partial_refund__premium_only'], 10, 2);

                // Process subscription renewals
                // https://docs.woocommerce.com/document/subscriptions/develop/action-reference/
                add_action('woocommerce_subscription_renewal_payment_complete', [$this, 'google_analytics_mp_report_subscription_purchase_renewal__premium_only']);
            }
        }

        add_action('init', [$this, 'run_on_init']);
    }

    public function google_analytics_mp_send_partial_refund__premium_only($order_id, $refund_id)
    {
        if ($this->is_google_analytics_ua_active()) $this->google_analytics_ua_http_mp->send_partial_refund_hit($order_id, $refund_id);
        if ($this->is_google_analytics_4_mp_active()) $this->google_analytics_4_http_mp->send_partial_refund_hit($order_id, $refund_id);
    }

    public function google_analytics_mp_send_full_refund__premium_only($order_id, $refund_id)
    {
        if ($this->is_google_analytics_ua_active()) $this->google_analytics_ua_http_mp->send_full_refund_hit($order_id);
        if ($this->is_google_analytics_4_mp_active()) $this->google_analytics_4_http_mp->send_full_refund_hit($order_id);
    }

    public function google_analytics_mp_report_subscription_purchase_renewal__premium_only($subscription, $renewal_order)
    {
        $parent_order = $subscription->get_parent();

        // Get cid from parent order
        $cid = $this->google_analytics_ua_http_mp->get_cid_from_order($parent_order);

        if ($this->is_google_analytics_ua_active()) $this->google_analytics_ua_http_mp->send_purchase_hit($renewal_order, $cid);
        if ($this->is_google_analytics_4_mp_active()) $this->google_analytics_4_http_mp->send_purchase_hit($renewal_order, $cid);
    }

    public function google_analytics_save_cid_on_order__premium_only($order)
    {
        if ($this->is_google_analytics_ua_active()) $this->google_analytics_ua_http_mp->set_cid_on_order($order);
        if ($this->is_google_analytics_4_mp_active()) $this->google_analytics_4_http_mp->set_cid_on_order($order);
    }

    public function google_analytics_mp_report_purchase__premium_only($order_id)
    {
        $order = wc_get_order($order_id);

        // The Measurement Protocol has only been enabled for EEC
        if ($this->is_google_analytics_ua_active()) $this->google_analytics_ua_http_mp->send_purchase_hit($order);
        if ($this->is_google_analytics_4_mp_active()) $this->google_analytics_4_http_mp->send_purchase_hit($order);
    }

    public function run_on_init()
    {
        // function to only fire gtag login once after login
        // and then wait for the next session
        if (is_user_logged_in()) {
            if (!isset($_COOKIE['gtag_logged_in'])) {
                add_action('wp_footer', [$this, 'output_gtag_login']);
                // the cookie expires after the session
                setcookie('gtag_logged_in', 'true');
            }
        }
    }

    public function output_gtag_login()
    {
        $data = [
            'user_id' => get_current_user_id(),
        ];

        ?>

        <script>
            gtag('event', 'login', <?php echo json_encode($data) ?>);
        </script>
        <?php
    }

    public function inject_everywhere()
    {
        $this->google_pixel->inject_everywhere();
    }

//    public function inject_product_category()
//    {
//        $this->google_pixel->inject_product_category();
//    }

    public function google_front_end_scripts()
    {
        if (wga_fs()->is__premium_only()) {

            if ($this->options_obj->google->analytics->universal->property_id || $this->options_obj->google->analytics->ga4->measurement_id) {
                wp_enqueue_script('wooptpm-google-premium', plugin_dir_url(__DIR__) . '../../js/public/google__premium_only.js', ['jquery', 'wooptpm', 'wooptpm-premium-only'], WGACT_CURRENT_VERSION, false);
                wp_localize_script(
                    'wooptpm-google-premium',
                    'wooptpm_google_premium_ajax_object',
                    [
                        'ajax_url' => admin_url('admin-ajax.php'),
                        'nonce' => wp_create_nonce( 'wooptpm-google-premium-nonce' ),
                    ]
                );
            }

            if ($this->options_obj->google->analytics->universal->property_id) {
                wp_enqueue_script('wooptpm-ga-ua-eec', plugin_dir_url(__DIR__) . '../../js/public/google-ga-ua-eec__premium_only.js', ['jquery', 'wooptpm', 'wooptpm-premium-only'], WGACT_CURRENT_VERSION, true);
                wp_localize_script('wooptpm-ga-ua-eec', 'ajax_object', ['ajax_url' => admin_url('admin-ajax.php')]);
            }

            if ($this->options_obj->google->analytics->ga4->measurement_id) {
                wp_enqueue_script('wooptpm-ga4-eec', plugin_dir_url(__DIR__) . '../../js/public/google-ga-4-eec__premium_only.js', ['jquery', 'wooptpm', 'wooptpm-premium-only'], WGACT_CURRENT_VERSION, true);
                wp_localize_script('wooptpm-ga4-eec', 'ajax_object', ['ajax_url' => admin_url('admin-ajax.php')]);
            }
        }
    }

    public function google_analytics_eec_action_woocommerce_order_refunded__premium_only($order_id, $refund_id)
    {
        // safe refund task into database
        update_post_meta($refund_id, 'wooptpm_refund_processed', false);
    }

    public function inject_product_category()
    {
        // all handled on front-end
    }

    public function inject_product_tag()
    {
        // all handled on front-end
    }

    public function inject_shop_top_page()
    {
        // all handled on front-end
    }

    public function inject_search()
    {
        // all handled on front-end
    }

    public function inject_product($product, $product_attributes)
    {
        // handled on front-end
    }

    public function inject_cart($cart, $cart_total)
    {
        // all handled on front-end
    }

    public function inject_order_received_page($order, $order_total, $is_new_customer)
    {
        if ($this->is_google_ads_active()) $this->google_ads_pixel->inject_order_received_page($order, $order_total, $is_new_customer);

        if (!wga_fs()->is__premium_only() || !$this->options_obj->google->analytics->eec) {
            if ($this->is_google_analytics_ua_active()) $this->google_analytics_ua_standard_pixel->inject_order_received_page($order, $order_total, $is_new_customer);
            if ($this->is_google_analytics_4_active()) $this->google_analytics_4_standard_pixel->inject_order_received_page($order, $order_total, $is_new_customer);
        } else {

            if ($this->is_google_analytics_4_active() && !$this->options_obj->google->analytics->ga4->api_secret) $this->google_analytics_4_eec_pixel->inject_order_received_page($order, $order_total, $is_new_customer);
        }
    }

    protected function inject_opening_script_tag()
    {
        echo PHP_EOL;
        echo '      <!-- START Google scripts -->' . PHP_EOL;
//        echo PHP_EOL;
    }

    protected function inject_closing_script_tag()
    {
        echo PHP_EOL;
        echo "\t\t" . '//# sourceURL=wooptpmGoogleInlineScripts.js' . PHP_EOL;
        echo "\t\t" . '</script>';
        echo PHP_EOL . PHP_EOL;
        echo "\t" . '<!-- END Google scripts -->' . PHP_EOL;
    }
}

