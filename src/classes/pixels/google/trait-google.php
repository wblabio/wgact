<?php

namespace WGACT\Classes\Pixels\Google;

use WGACT\Classes\Admin\Environment_Check;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

trait Trait_Google
{
    protected function get_ga_id_type(): string
    {
        $ga_id_type = 'post_id';

        $ga_id_type = apply_filters('wooptpm_product_id_type_for_google_analytics', $ga_id_type);

        return $ga_id_type;
    }

    protected function google_active(): bool
    {
        if ($this->options_obj->google->analytics->universal->property_id) {
            return true;
        } elseif ($this->options_obj->google->analytics->ga4->measurement_id) {
            return true;
        } elseif ($this->options_obj->google->ads->conversion_id) {
            return true;
        } else {
            return false;
        }
    }

    protected function is_google_ads_active(): bool
    {
        if ($this->options['google']['ads']['conversion_id']) {

            return true;
        } else {
            return false;
        }
    }

    private function is_dynamic_remarketing_active(): bool
    {
        if ($this->options_obj->google->ads->dynamic_remarketing && $this->options_obj->google->ads->conversion_id) {
            return true;
        } else {
            return false;
        }
    }

//    protected function is_google_ads_active(): bool
//    {
//        if ($this->options_obj->google->ads->conversion_id && $this->options_obj->google->ads->conversion_label) {
//            return true;
//        } else {
//            return false;
//        }
//    }

    protected function is_google_analytics_active(): bool
    {
        if ($this->is_google_analytics_ua_active() || $this->is_google_analytics_4_active()) {
            return true;
        } else {
            return false;
        }
    }

    protected function is_google_analytics_ua_active(): bool
    {
        if ($this->options_obj->google->analytics->universal->property_id) {
            return true;
        } else {
            return false;
        }
    }

    protected function is_google_analytics_4_active(): bool
    {
        if ($this->options_obj->google->analytics->ga4->measurement_id) {
            return true;
        } else {
            return false;
        }
    }

    protected function is_google_analytics_4_mp_active(): bool
    {
        if ($this->options_obj->google->analytics->ga4->measurement_id && $this->options_obj->google->analytics->ga4->api_secret) {
            return true;
        } else {
            return false;
        }
    }

    protected function get_order_currency($order)
    {
        // use the right function to get the currency depending on the WooCommerce version
        return $this->woocommerce_3_and_above() ? $order->get_currency() : $order->get_order_currency();
    }

    protected function woocommerce_3_and_above(): bool
    {
        global $woocommerce;
        if (version_compare($woocommerce->version, 3.0, ">=")) {
            return true;
        } else {
            return false;
        }
    }

    protected function get_order_item_data($order_item): array
    {
        $product = $order_item->get_product();

        if (!is_object($product)) {

//            $this->log_problematic_product_id();
            wc_get_logger()->debug('get_order_item_data received an order item which is not a valid product: ' . $order_item->get_id(), ['source' => 'wooptpm']);
            return [];
        }

        $dyn_r_ids = $this->get_dyn_r_ids($product);

        if ($product->get_type() === 'variation') {
            $parent_product = wc_get_product($product->get_parent_id());
            $name           = $parent_product->get_name();
        } else {
            $name = $product->get_name();
        }

        return [
            'id'             => (string)$dyn_r_ids[$this->get_ga_id_type()],
            'name'           => (string)$name,
            'quantity'       => (int)$order_item['quantity'],
            'affiliation'    => (string)get_bloginfo('name'),
            //            'coupon' => '',
            //            'discount' => 0,
            'brand'          => (string)$this->get_brand_name($product->get_id()),
            // https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters#pr_ca
            'category'       => implode(',', $this->get_product_category($product->get_id())),
            'category_array' => $this->get_product_category($product->get_id()),
            'variant'        => (string)($product->get_type() === 'variation') ? $this->get_formatted_variant_text($product) : '',
            //            'tax'      => 0,
            'price'          => (float)$this->wooptpm_get_order_item_price($order_item, $product),
            //            'list_name' => ,
            //            'currency' => '',
        ];
    }

    protected function wooptpm_get_order_item_price($order_item, $product): float
    {
        if ((new Environment_Check())->is_woo_discount_rules_active()) {
            $item_value = $order_item->get_meta('_advanced_woo_discount_item_total_discount');
            if (is_array($item_value) && array_key_exists('discounted_price', $item_value) && $item_value['discounted_price'] != 0) {
                return (float)$item_value['discounted_price'];
            } elseif (is_array($item_value) && array_key_exists('initial_price', $item_value) && $item_value['initial_price'] != 0) {
                return (float)$item_value['initial_price'];
            } else {
                return (float)$product->get_price();
            }
        } else {
            return (float)$product->get_price();
        }
    }

    protected function add_categories_to_ga4_product_items($item_details_array, $categories)
    {
        $categories = array_unique($categories);

        if(count($categories) > 0){

            $max_categories = 5;

            $item_details_array['item_category'] = $categories[0];

            $max = count($categories) > $max_categories ? $max_categories : count($categories);

            for ($i = 1; $i < $max; $i++) {
                $item_details_array['item_category' . ($i + 1)] = $categories[$i];
            }
        }

        return $item_details_array;
    }
}