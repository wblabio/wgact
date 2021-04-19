<?php

namespace WGACT\Classes\Pixels\Google;

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
}