<?php

namespace WGACT\Classes\Pixels;

use WC_Geolocation;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Pixel
{
    protected $add_cart_data;
    protected $aw_merchant_id;
    protected $conversion_id;
    protected $conversion_label;
    protected $dynamic_remarketing;
    protected $google_business_vertical;
    protected $gtag_deactivation;
    protected $ip;
    protected $order_total_logic;
    protected $product_identifier;
    protected $options;
    protected $options_obj;

    public function __construct($options, $options_obj)
    {
        $this->options     = $options;
        $this->options_obj = $options_obj;

        $this->order_total_logic        = $this->options['shop']['order_total_logic'];
        $this->add_cart_data            = $this->options['google']['ads']['add_cart_data'];
        $this->aw_merchant_id           = $this->options['google']['ads']['aw_merchant_id'];
        $this->conversion_id            = $this->options['google']['ads']['conversion_id'];
        $this->conversion_label         = $this->options['google']['ads']['conversion_label'];
        $this->dynamic_remarketing      = $this->options['google']['ads']['dynamic_remarketing'];
        $this->product_identifier       = $this->options['google']['ads']['product_identifier'];
        $this->gtag_deactivation        = $this->options['google']['gtag']['deactivation'];
    }

    // get an array with all product categories
    protected function get_product_category($product_id): array
    {
        $prod_cats        = get_the_terms($product_id, 'product_cat');
        $prod_cats_output = [];

        // only continue with the loop if one or more product categories have been set for the product
        if (!empty($prod_cats)) {
            foreach ((array)$prod_cats as $key) {
                array_push($prod_cats_output, $key->name);
            }

            // apply filter to the $prod_cats_output array
            $prod_cats_output = apply_filters('wgact_filter', $prod_cats_output, 'prod_cats_output');
        }

        return $prod_cats_output;
    }

    // get an array with all cart product ids
    public function get_cart_ids($cart): array
    {
        // error_log(print_r($cart, true));
        // initiate product identifier array
        $cart_items = [];

        // go through the array and get all product identifiers
        foreach ((array)$cart as $item) {

            $product_id = $item['product_id'];
            $product    = wc_get_product($product_id);

            $product_id_compiled = $this->get_compiled_product_id($item['product_id'], $product->get_sku());

            array_push($cart_items, $product_id_compiled);
        }

        return $cart_items;
    }

    protected function get_visitor_country()
    {
        if ($this->isLocalhost()) {
            $this->ip = WC_Geolocation::get_external_ip_address();
        } else {
            $this->ip = WC_Geolocation::get_ip_address();
        }

        $location = WC_Geolocation::geolocate_ip($this->ip);

        return $location['country'];
    }

    protected function isLocalhost(): bool
    {
        return in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']);
    }

    protected function get_compiled_product_id($product_id, $product_sku): string
    {
        // depending on setting use product IDs or SKUs
        if (0 == $this->product_identifier) {
            return (string)$product_id;
        } else if (1 == $this->product_identifier) {
            return (string)'woocommerce_gpf_' . $product_id;
        } else {
            if ($product_sku) {
                return (string)$product_sku;
            } else {
                return (string)$product_id;
            }
        }
    }
}