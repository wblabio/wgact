<?php

namespace WGACT\Classes\Pixels;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Facebook_Pixel_Manager extends Pixel
{
    public function inject_everywhere()
    {
        (new Facebook_Browser_Pixel($this->options, $this->options_obj))->inject_everywhere();
    }

    public function inject_search()
    {
        (new Facebook_Browser_Pixel($this->options, $this->options_obj))->inject_search();
    }

    public function inject_product($product_id, $product)
    {
        (new Facebook_Browser_Pixel($this->options, $this->options_obj))->inject_product($product_id, $product);
    }

    public function inject_cart($cart, $cart_total)
    {
        (new Facebook_Browser_Pixel($this->options, $this->options_obj))->inject_cart($cart, $cart_total);
    }

    public function inject_order_received_page($order, $order_total, $order_item_ids)
    {
        (new Facebook_Browser_Pixel($this->options, $this->options_obj))->inject_order_received_page($order, $order_total, $order_item_ids);
    }
}