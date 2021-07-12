<?php

namespace WGACT\Classes\Pixels\Pinterest;

use WGACT\Classes\Pixels\Pixel_Manager_Base;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Pinterest_Pixel_Manager extends Pixel_Manager_Base
{
    protected $pinterest_pixel;

    public function __construct($options)
    {
        parent::__construct($options);

        $this->pinterest_pixel = new Pinterest_Pixel($options);

        add_action('wp_enqueue_scripts', [$this, 'wooptpm_pinterest_front_end_scripts__premium_only']);
    }

    public function wooptpm_pinterest_front_end_scripts__premium_only()
    {
//        wp_enqueue_script('wooptpm-pinterest-premium-only', plugin_dir_url(__DIR__) . '../../js/public/pinterest__premium_only.js', ['jquery', 'wooptpm'], WGACT_CURRENT_VERSION, true);
        wp_enqueue_script('wooptpm-pinterest-premium-only', WOOPTPM_PLUGIN_DIR_PATH . 'js/public/pinterest__premium_only.js', ['jquery', 'wooptpm'], WGACT_CURRENT_VERSION, true);
    }

    public function inject_everywhere()
    {
        $this->pinterest_pixel->inject_everywhere();
    }

    public function inject_product_category()
    {
        $this->pinterest_pixel->inject_product_category();
    }

    public function inject_search()
    {
        $this->pinterest_pixel->inject_search();
    }

    public function inject_product($product, $product_attributes)
    {
        $this->pinterest_pixel->inject_product($product, $product_attributes);
    }

    public function inject_cart($cart, $cart_total)
    {
        $this->pinterest_pixel->inject_cart($cart, $cart_total);
    }

    public function inject_order_received_page($order, $order_total, $is_new_customer)
    {
        $this->pinterest_pixel->inject_order_received_page($order, $order_total);
    }

    protected function inject_opening_script_tag()
    {
        echo PHP_EOL;
        echo '      <!-- START Pinterest scripts -->' . PHP_EOL;
        echo '            <script>';
        echo PHP_EOL;
    }

    protected function inject_closing_script_tag()
    {
        echo PHP_EOL;
        echo '            </script>';
        echo PHP_EOL;
        echo '      <!-- END Pinterest scripts -->' . PHP_EOL;
    }
}