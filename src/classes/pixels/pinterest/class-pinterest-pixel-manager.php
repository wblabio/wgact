<?php

namespace WGACT\Classes\Pixels\Pinterest;

use WGACT\Classes\Pixels\Pixel_Manager_Base;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Pinterest_Pixel_Manager extends Pixel_Manager_Base
{
    protected $pinterest_pixel;

    public function __construct()
    {
        parent::__construct();

        $this->pinterest_pixel = new Pinterest_Pixel();
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
        $this->pinterest_pixel->inject_order_received_page($order, $order_total, $is_new_customer);
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