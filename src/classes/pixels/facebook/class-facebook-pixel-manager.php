<?php

namespace WGACT\Classes\Pixels\Facebook;

use WGACT\Classes\Pixels\Pixel_Manager_Base;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Facebook_Pixel_Manager extends Pixel_Manager_Base
{
    protected $facebook_browser_pixel;

    public function __construct()
    {
        parent::__construct();

        add_action('wp_enqueue_scripts', [$this, 'wooptpm_facebook_front_end_scripts']);

        $this->facebook_browser_pixel = new Facebook_Browser_Pixel();
    }

    public function wooptpm_facebook_front_end_scripts()
    {
        wp_enqueue_script('wooptpm-facebook', plugin_dir_url(__DIR__) . '../../js/public/facebook.js', ['jquery','wooptpm'], WGACT_CURRENT_VERSION, true);

        if (wga_fs()->is__premium_only()) {
            wp_enqueue_script('wooptpm-facebook-premium-only', plugin_dir_url(__DIR__) . '../../js/public/facebook__premium_only.js', ['jquery','wooptpm','wooptpm-premium-only', 'wooptpm-facebook'], WGACT_CURRENT_VERSION, true);
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