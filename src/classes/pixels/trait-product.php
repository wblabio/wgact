<?php

namespace WGACT\Classes\Pixels;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

trait Trait_Product
{
    // https://stackoverflow.com/a/56278308/4688612
    // https://stackoverflow.com/a/39034036/4688612
    public function get_brand_name($product_id): string
    {
        return $this->get_brand_by_taxonomy($product_id, 'product_brand') ?: // for Woocommerce Brands plugin
            $this->get_brand_by_taxonomy($product_id, 'yith_product_brand') ?: // for YITH WooCommerce Brands plugin
                $this->get_brand_by_taxonomy($product_id, 'pa_brand') ?: // for a custom product attribute
                    ''; // default value
    }

    public function get_brand_by_taxonomy($product_id, $taxonomy): string
    {
        if (taxonomy_exists($taxonomy)) {
            $brand_names = wp_get_post_terms($product_id, $taxonomy, ['fields' => 'names']);
            return reset($brand_names);
        } else {
            return '';
        }
    }
}