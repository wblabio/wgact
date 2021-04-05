<?php

namespace WGACT\Classes\Pixels;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

trait Trait_Product
{
    protected function get_variation_or_product_id($item, $variations_output = true)
    {
        if ($item['variation_id'] <> 0 && $variations_output == true) {
            return $item['variation_id'];
        } else {
            return $item['product_id'];
        }
    }

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

    // get an array with all product categories
    public function get_product_category($product_id): array
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

    protected function get_compiled_product_id($product_id, $product_sku, $channel = '', $options): string
    {
        // depending on setting use product IDs or SKUs
        if (0 == $this->options['google']['ads']['product_identifier'] || $channel === 'ga_ua' || $channel === 'ga_4') {
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

    protected function get_dyn_r_ids($product): array
    {
        $dyn_r_ids = [
            'post_id' => (string)$product->get_id(),
            'sku'     => (string) $product->get_sku() ? $product->get_sku() : $product->get_id(),
            'gpf'     => 'woocommerce_gpf_' . (string)$product->get_id(),
        ];

        // if you want to add a custom dyn_r_id for each product
        $dyn_r_ids = apply_filters('wooptpm_product_ids', $dyn_r_ids, $product);

        return $dyn_r_ids;
    }


}