<?php


namespace WGACT\Classes\Pixels;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google_Pixel_Manager extends Pixel
{
    public function inject_everywhere()
    {
        (new Google_Ads($this->options, $this->options_obj))->inject_everywhere();
//        (new Google_Enhanced_Ecommerce($this->options, $this->options_obj))->inject_everywhere();
    }

    public function inject_search()
    {
        (new Google_Ads($this->options, $this->options_obj))->inject_search();
//        (new Google_Enhanced_Ecommerce($this->options, $this->options_obj))->inject_search();
    }

    public function inject_product($product_id, $product, $product_attributes)
    {
        (new Google_Ads($this->options, $this->options_obj))->inject_product($product_id, $product, $product_attributes);
//        (new Google_Enhanced_Ecommerce($this->options, $this->options_obj))->inject_product($product_id, $product, $product_attributes);
    }

    public function inject_cart($cart, $cart_total)
    {
        (new Google_Ads($this->options, $this->options_obj))->inject_cart($cart, $cart_total);
//        (new Google_Enhanced_Ecommerce($this->options, $this->options_obj))->inject_cart($cart, $cart_total);
    }

    public function inject_order_received_page($order, $order_total, $order_item_ids, $is_new_customer)
    {
        (new Google_Ads($this->options, $this->options_obj))->inject_order_received_page($order, $order_total, $order_item_ids, $is_new_customer);
        if ($this->options_obj->google->analytics->eec == false) (new Google_Standard_Ecommerce($this->options, $this->options_obj))->inject_order_received_page($order, $order_total, $order_item_ids, $is_new_customer);
//        (new Google_Enhanced_Ecommerce($this->options, $this->options_obj))->inject_order_received_page($order, $order_total, $order_item_ids, $is_new_customer);
    }
}

