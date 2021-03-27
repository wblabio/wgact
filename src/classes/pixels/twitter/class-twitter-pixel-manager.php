<?php

namespace WGACT\Classes\Pixels\Twitter;

use WGACT\Classes\Pixels\Pixel_Manager_Base;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Twitter_Pixel_Manager extends Pixel_Manager_Base
{
    protected $twitter_pixel;

    public function __construct()
    {
        parent::__construct();

        $this->twitter_pixel = new Twitter_Pixel();
    }

    public function inject_everywhere()
    {
        $this->twitter_pixel->inject_everywhere();
    }

    public function inject_search()
    {
        $this->twitter_pixel->inject_search();
    }

    public function inject_product($product, $product_attributes)
    {
        $this->twitter_pixel->inject_product($product, $product_attributes);
    }

    public function inject_cart($cart, $cart_total)
    {
        $this->twitter_pixel->inject_cart($cart, $cart_total);
    }

    public function inject_order_received_page($order, $order_total, $order_item_ids, $is_new_customer)
    {
        $this->twitter_pixel->inject_order_received_page($order, $order_total, $order_item_ids, $is_new_customer);
    }

    protected function inject_opening_script_tag()
    {
        echo PHP_EOL;
        echo '      <!-- START Twitter scripts -->' . PHP_EOL;
        echo '            <script>';
        echo PHP_EOL;
    }

    protected function inject_closing_script_tag()
    {
        echo PHP_EOL;
        echo '            </script>';
        echo PHP_EOL;
        echo '      <!-- END Twitter scripts -->' . PHP_EOL;
    }
}