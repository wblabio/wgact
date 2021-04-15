<?php

namespace WGACT\Classes\Pixels\Google;

use WC_Order;
use WC_Order_Refund;
use WC_Product;
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
//    private $google_analytics_ua_refund_pixel;
    private $google_analytics_4_eec_pixel;

    public function __construct()
    {
        parent::__construct();

        $this->google_pixel = new Google();
        if ($this->is_google_ads_active()) if ($this->is_google_ads_active()) $this->google_ads_pixel = new Google_Ads();

        add_action('wp_enqueue_scripts', [$this, 'google_front_end_scripts']);

        if (!$this->options_obj->google->analytics->eec) {
            $this->google_analytics_ua_standard_pixel = new Google_Analytics_UA_Standard();
            $this->google_analytics_4_standard_pixel  = new Google_Analytics_4_Standard();

        } else if (wga_fs()->is__premium_only() && $this->options_obj->google->analytics->eec) {

            $this->google_analytics_ua_eec_pixel = new Google_Analytics_UA_EEC();
//            $this->google_analytics_ua_refund_pixel = new Google_Analytics_UA_Refund_Pixel();

            $this->google_analytics_4_eec_pixel = new Google_Analytics_4_EEC();

            add_action('woocommerce_order_refunded', [$this, 'google_analytics_eec_action_woocommerce_order_refunded__premium_only'], 10, 2);
//            add_action('wp_login', [$this,'output_gtag_login']);
        }

        add_action('init', [$this, 'run_on_init']);
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
            wp_enqueue_script('ga-ua-eec', plugin_dir_url(__DIR__) . '../../js/public/google-ga-ua-eec__premium_only.js', [], WGACT_CURRENT_VERSION, true);
            wp_localize_script('ga-ua-eec', 'ajax_object', ['ajax_url' => admin_url('admin-ajax.php')]);

            wp_enqueue_script('ga4-eec', plugin_dir_url(__DIR__) . '../../js/public/google-ga-4-eec__premium_only.js', [], WGACT_CURRENT_VERSION, true);
            wp_localize_script('ga4-eec', 'ajax_object', ['ajax_url' => admin_url('admin-ajax.php')]);
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
        if ($this->is_dynamic_remarketing_active()) $this->google_ads_pixel->inject_product($product, $product_attributes);

        if (wga_fs()->is__premium_only() && $this->options_obj->google->analytics->eec) {

            if ($this->is_google_analytics_ua_active()) $this->google_analytics_ua_eec_pixel->inject_product($product, $product_attributes);
            if ($this->is_google_analytics_4_active()) $this->google_analytics_4_eec_pixel->inject_product($product, $product_attributes);
        }
    }

    public function inject_cart($cart, $cart_total)
    {
        // all handled on front-end
    }

    public function inject_order_received_page($order, $order_total, $is_new_customer)
    {
        if ($this->is_google_ads_active()) $this->google_ads_pixel->inject_order_received_page($order, $order_total, $is_new_customer);

        if (!$this->options_obj->google->analytics->eec) {
            if ($this->is_google_analytics_ua_active()) $this->google_analytics_ua_standard_pixel->inject_order_received_page($order, $order_total, $is_new_customer);
            if ($this->is_google_analytics_4_active()) $this->google_analytics_4_standard_pixel->inject_order_received_page($order, $order_total, $is_new_customer);
        } else if (wga_fs()->is__premium_only()) {
            if ($this->is_google_analytics_ua_active()) $this->google_analytics_ua_eec_pixel->inject_order_received_page($order, $order_total, $is_new_customer);
            if ($this->is_google_analytics_4_active()) $this->google_analytics_4_eec_pixel->inject_order_received_page($order, $order_total, $is_new_customer);
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
        echo '//# sourceURL=wooptpmGoogleInlineScripts.js' . PHP_EOL;
        echo '            </script>';
        echo PHP_EOL . PHP_EOL;
        echo '      <!-- END Google scripts -->' . PHP_EOL;
    }
}

