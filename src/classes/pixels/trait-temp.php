<?php

namespace WGACT\Classes\Pixels;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

trait Trait_Temp
{
    protected function get_compiled_product_id($product_id, $product_sku, $channel = '', $options): string
    {
        // depending on setting use product IDs or SKUs
        if (0 == $this->options['google']['ads']['product_identifier'] || $channel == 'analytics') {
            return (string)$product_id;
        } else if (1 == $this->options['google']['ads']['product_identifier']) {
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