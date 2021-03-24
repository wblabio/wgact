<?php

namespace WGACT\Classes\Pixels;

use WGACT\Classes\Admin\Environment_Check;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google_Pixel_Manager extends Pixel_Manager_Base
{
    use Trait_Google;

    private $gads;
    private $google_enhanced_ecommerce_pixel;

    public function __construct()
    {
        parent::__construct();

        $this->gads = new Google_Ads();
        $this->google_pixel = new Google_Pixel();
        $this->google_enhanced_ecommerce_pixel = new Google_Enhanced_Ecommerce();
        $this->google_standard_ecommerce_pixel = new Google_Standard_Ecommerce();

        add_action('wp_enqueue_scripts', [$this, 'google_front_end_scripts']);

        if ($this->options_obj->google->analytics->eec) {

            add_action('woocommerce_order_refunded', [$this, 'eec_action_woocommerce_order_refunded'], 10, 2);
            add_action('wp_footer', [$this, 'process_refund_to_frontend']);
            add_action('admin_footer', [$this, 'process_refund_to_frontend']);
        }
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
            wp_enqueue_script('eec', plugin_dir_url(__DIR__) . '../js/public/eec__premium_only.js', [], WGACT_CURRENT_VERSION, false);
            wp_localize_script('eec', 'ajax_object', ['ajax_url' => admin_url('admin-ajax.php')]);
        }
    }

    public function eec_action_woocommerce_order_refunded($order_id, $refund_id)
    {
        // safe refund task into database
        update_post_meta($refund_id, 'wooptpm_refund_processed', false);
    }

    /**
     * Processes all prepared refunds in post_meta and outputs them on the frontend into the dataLayer.
     * We only process this on the frontend since the output on is_order_received_page has a higher chance to get
     * processed properly through GTM.
     */
    public function process_refund_to_frontend()
    {
        global $wpdb;

        // the following condition is to limit running the following script and potentially overload the server
        if (is_admin() || is_order_received_page()) {

            $sql = "SELECT meta_id, post_id FROM wp_postmeta WHERE meta_key = 'wooptpm_refund_processed' AND `meta_value` = false";

            $results = $wpdb->get_results($sql);

            foreach ($results as $result) {

                $refund   = new WC_Order_Refund($result->post_id);
                $order_id = $refund->get_parent_id();

                $refund_items = $refund->get_items();

                $dataLayer_refund_items = [];
                foreach ($refund_items as $refund_item) {

                    $dataLayer_refund_items[] = [
                        'id'       => $refund_item->get_product_id(),
                        'quantity' => $refund_item->get_quantity()
                    ];
                }

                $this->output_refund_to_frontend($order_id, $dataLayer_refund_items);

                update_post_meta($result->post_id, 'wooptpm_refund_processed', true);
            }
        }
    }



    private function inject_phone_conversion_number_html__premium_only()
    {
        ?>

        <script>
            gtag('config', 'AW-<?php echo $this->options_obj->google->ads->conversion_id ?>/<?php echo $this->options_obj->google->ads->conversion_label ?>', {
                'phone_conversion_number': '<?php echo $this->options_obj->google->ads->phone_conversion_number ?>'
            });
        </script>
        <?php
    }

    private function inject_borlabs_consent_mode_update()
    {
        ?>

        <script>
            (function updateGoogleConsentMode() {
                if (typeof BorlabsCookie == "undefined" || typeof gtag == "undefined") {
                    window.setTimeout(updateGoogleConsentMode, 50);
                } else {
                    if (window.BorlabsCookie.checkCookieGroupConsent('statistics')) {
                        gtag('consent', 'update', {
                            'analytics_storage': 'granted'
                        });
                    }

                    if (window.BorlabsCookie.checkCookieGroupConsent('marketing')) {
                        gtag('consent', 'update', {
                            'ad_storage': 'granted'
                        });
                    }
                }
            })();
        </script>
        <?php
    }

    public function inject_product_category()
    {
        $this->gads->inject_product_category();

        if (wga_fs()->is__premium_only()) {
            if ($this->options_obj->google->analytics->eec) $this->google_enhanced_ecommerce_pixel->inject_product_list_object('product_category');
        }
    }

    public function inject_product_tag()
    {
        if (wga_fs()->is__premium_only()) {
            if ($this->options_obj->google->analytics->eec) $this->google_enhanced_ecommerce_pixel->inject_product_list_object('product_tag');
        }
    }

    public function inject_shop_top_page()
    {
        if (wga_fs()->is__premium_only()) {
            if ($this->options_obj->google->analytics->eec) $this->google_enhanced_ecommerce_pixel->inject_product_list_object('shop');
        }
    }

    public function inject_search()
    {
        $this->gads->inject_search();

        if (wga_fs()->is__premium_only()) {
            if ($this->options_obj->google->analytics->eec) $this->google_enhanced_ecommerce_pixel->inject_product_list_object('search');
        }
    }

    public function inject_product($product_id, $product, $product_attributes)
    {
        $this->gads->inject_product($product_id, $product, $product_attributes);

        if (wga_fs()->is__premium_only()) {
            if ($this->options_obj->google->analytics->eec) $this->google_enhanced_ecommerce_pixel->inject_product($product_id, $product, $product_attributes);
        }
    }

    public function inject_cart($cart, $cart_total)
    {
        $this->gads->inject_cart($cart, $cart_total);

        if (wga_fs()->is__premium_only()) {
            if ($this->options_obj->google->analytics->eec) $this->google_enhanced_ecommerce_pixel->inject_cart($cart, $cart_total);
        }
    }

    public function inject_order_received_page($order, $order_total, $order_item_ids, $is_new_customer)
    {
        if ($this->options_obj->google->ads->conversion_id) $this->gads->inject_order_received_page($order, $order_total, $order_item_ids, $is_new_customer);
        if ($this->is_google_analytics_active()) {

            // this is the same code for standard and eec, therefore using the same for both
            $this->google_standard_ecommerce_pixel->inject_order_received_page($order, $order_total, $order_item_ids, $is_new_customer);
        }
    }
}

