<?php

namespace WGACT\Classes\Pixels;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Facebook_Pixel_Manager extends Pixel
{
    public function __construct($options, $options_obj)
    {
        parent::__construct($options, $options_obj);
        add_action('wp_enqueue_scripts', [$this, 'wooptpm_facebook_front_end_scripts']);
    }

    public function wooptpm_facebook_front_end_scripts()
    {
        wp_enqueue_script('facebook', plugin_dir_url(__DIR__) . '../js/public/facebook.js', [], WGACT_CURRENT_VERSION, false);
    }

    public function inject_everywhere()
    {
        (new Facebook_Browser_Pixel($this->options, $this->options_obj))->inject_everywhere();
    }

    public function inject_search()
    {
        (new Facebook_Browser_Pixel($this->options, $this->options_obj))->inject_search();
    }

    public function inject_product($product_id, $product, $product_attributes)
    {
        (new Facebook_Browser_Pixel($this->options, $this->options_obj))->inject_product($product_id, $product, $product_attributes);
        if (wga_fs()->is__premium_only()) {
            if ($this->options_obj->facebook->microdata) (new Facebook_Microdata($this->options, $this->options_obj))->inject_product($product_id, $product, $product_attributes);
        }
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