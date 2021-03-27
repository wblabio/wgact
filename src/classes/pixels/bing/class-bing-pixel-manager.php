<?php

namespace WGACT\Classes\Pixels\Bing;

use WGACT\Classes\Pixels\Pixel_Manager_Base;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Bing_Pixel_Manager extends Pixel_Manager_Base
{
    protected $bing_pixel;

    public function __construct()
    {
        parent::__construct();

        $this->bing_pixel = new Bing_Pixel();
    }

    public function inject_everywhere()
    {
        $this->bing_pixel->inject_everywhere();
    }

    public function inject_product_category()
    {
        $this->bing_pixel->inject_product_category();
    }

    public function inject_search()
    {
        $this->bing_pixel->inject_search();
    }

    public function inject_product($product, $product_attributes)
    {
        $this->bing_pixel->inject_product($product, $product_attributes);
    }

    public function inject_cart($cart, $cart_total)
    {
        $this->bing_pixel->inject_cart($cart, $cart_total);
    }

    public function inject_order_received_page($order, $order_total, $order_item_ids, $is_new_customer)
    {
        $this->bing_pixel->inject_order_received_page($order, $order_total, $order_item_ids, $is_new_customer);
    }

    protected function inject_opening_script_tag()
    {
        echo PHP_EOL;
        echo '      <!-- START Bing scripts -->' . PHP_EOL;
        echo '            <script>';
        echo PHP_EOL;
    }

    protected function inject_closing_script_tag()
    {
        echo PHP_EOL;
        echo '            </script>';
        echo PHP_EOL;
        echo '      <!-- END Bing scripts -->' . PHP_EOL;
    }
}