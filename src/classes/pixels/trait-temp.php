<?php

namespace WGACT\Classes\Pixels;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

trait Trait_Temp
{
    private function is_shop_top_page(): bool
    {
        if (
            !is_product() &&
            !is_product_category() &&
            !is_order_received_page() &&
            !is_cart() &&
            !is_search() &&
            is_shop()
        ) {
            return true;
        } else {
            return false;
        }
    }

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

    protected function inject_opening_script_tag()
    {
        echo '   <script>';
    }

    protected function inject_closing_script_tag()
    {
        echo '   </script>';

    }
}