<?php

namespace WGACT\Classes\Pixels;

use WC_Order;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Pixel_Manager
{
    protected $options;
    protected $options_obj;
    protected $cart;
    protected $facebook_active;
    protected $google_active;

    public function __construct($options)
    {
        $this->options     = $options;

        $this->options_obj = json_decode(json_encode($this->options));

        $this->options_obj->shop->currency = new \stdClass();
        $this->options_obj->shop->currency = get_woocommerce_currency();

        $this->facebook_active = !empty($this->options_obj->facebook->pixel_id);
        $this->google_active   = $this->google_active();

        add_action('wp_head', function () {
            $this->inject_head_pixels();
        });

        if (did_action('wp_body_open')) {
            add_action('wp_body_open', function () {
                $this->inject_body_pixels();
            });
        }
    }

    public function inject_head_pixels()
    {
        global $woocommerce;

        $cart       = $woocommerce->cart->get_cart();
        $cart_total = WC()->cart->get_cart_contents_total();


        $this->inject_noptimize_opening_tag();

        if ($this->google_active) (new Google($this->options, $this->options_obj))->inject_everywhere();
        if ($this->facebook_active) (new Facebook_Pixel_Manager($this->options, $this->options_obj))->inject_everywhere();


        if (is_product_category()) {

            if ($this->google_active) (new Google($this->options, $this->options_obj))->inject_product_category();

        } elseif (is_search()) {

            if ($this->google_active) (new Google($this->options, $this->options_obj))->inject_search();
            if ($this->facebook_active) (new Facebook_Pixel_Manager($this->options, $this->options_obj))->inject_search();

        } elseif (is_product() && (!isset($_POST['add-to-cart']))) {

            $product_id = get_the_ID();
            $product    = wc_get_product($product_id);

            if (is_bool($product)) {
//               error_log( 'WooCommerce detects the page ID ' . $product_id . ' as product, but when invoked by wc_get_product( ' . $product_id . ' ) it returns no product object' );
                return;
            }

            if ($this->google_active) (new Google($this->options, $this->options_obj))->inject_product($product_id, $product);
            if ($this->facebook_active) (new Facebook_Pixel_Manager($this->options, $this->options_obj))->inject_product($product_id, $product);

        } elseif (is_cart()) {

            if ($this->google_active) (new Google($this->options, $this->options_obj))->inject_cart($cart, $cart_total);
            if ($this->facebook_active) (new Facebook_Pixel_Manager($this->options, $this->options_obj))->inject_cart($cart, $cart_total);

        } elseif (is_order_received_page()) {

            // get order from URL and evaluate order total
            $order_key = $_GET['key'];
            $order     = new WC_Order(wc_get_order_id_by_order_key($order_key));

            $order_total = 0 == $this->options_obj->shop->order_total_logic ? $order->get_subtotal() - $order->get_total_discount() : $order->get_total();

            $order_item_ids = $this->get_order_item_ids($order);

            if ($this->google_active) (new Google($this->options, $this->options_obj))->inject_order_received_page($order, $order_total, $order_item_ids);
            if ($this->facebook_active) (new Facebook_Pixel_Manager($this->options, $this->options_obj))->inject_order_received_page($order, $order_total, $order_item_ids);

        }


        $this->inject_noptimize_closing_tag();
    }

    private function inject_body_pixels()
    {
//        (new Google())->inject_google_optimize_anti_flicker_snippet();
    }

    private function inject_noptimize_opening_tag()
    {
        ?>
        <!--noptimize--><?php
    }

    private function inject_noptimize_closing_tag()
    {
        ?>
        <!--/noptimize-->
        <?php
    }

    protected function get_order_item_ids($order)
    {
        $order_items       = $order->get_items();
        $order_items_array = [];

        foreach ((array)$order_items as $item) {
            array_push($order_items_array, (string)$item['product_id']);
        }

        // apply filter to the $order_items_array array
        $order_items_array = apply_filters('wgact_filter', $order_items_array, 'order_items_array');

        return $order_items_array;
    }

    private function google_active(): bool
    {
        if ($this->options_obj->google->analytics->universal->property_id) {
            return true;
        } elseif ($this->options_obj->google->analytics->ga4->measurement_id) {
            return true;
        } elseif ($this->options_obj->google->ads->conversion_id) {
            return true;
        } else {
            return false;
        }
    }

}