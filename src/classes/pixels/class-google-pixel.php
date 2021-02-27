<?php

namespace WGACT\Classes\Pixels;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google_Pixel extends Pixel
{
    use Trait_Google;

    protected $conversion_identifiers;

    public function __construct($options, $options_obj)
    {
        parent::__construct($options, $options_obj);

        $this->google_business_vertical = $this->get_google_business_vertical($this->options['google']['ads']['google_business_vertical']);

        $this->conversion_identifiers[$this->conversion_id] = $this->conversion_label;

        $this->conversion_identifiers = apply_filters('wgact_google_ads_conversion_identifiers', $this->conversion_identifiers);
    }

    protected function get_google_business_vertical($id): string
    {
        $verticals = [
            0 => 'retail',
            1 => 'education',
            2 => 'flights',
            3 => 'hotel_rental',
            4 => 'jobs',
            5 => 'local',
            6 => 'real_estate',
            7 => 'travel',
            8 => 'custom'
        ];

        return $verticals[$id];
    }

    protected function get_formatted_order_items($order, $channel = '')
    {
        $order_items       = $order->get_items();
        $order_items_array = [];

        $list_position = 1;

        foreach ((array)$order_items as $order_item) {

            $product_id = $this->get_variation_or_product_id($order_item->get_data(), $this->options_obj->general->variations_output);
            $product = wc_get_product($product_id);

            $item_details_array = [];

            $item_details_array['id']       = $this->get_compiled_product_id($product_id, $product->get_sku(), $channel);
            $item_details_array['quantity'] = (int)$order_item['quantity'];
            $item_details_array['price']    = (int)$product->get_price();
            if ($this->is_google_ads_active()) {
                $item_details_array['google_business_vertical'] = $this->google_business_vertical;
            }

            if ($this->is_google_analytics_active()) {
                $item_details_array['name'] = (string)$product->get_name();
//                $item_details_array['list_name'] = '';
                $item_details_array['brand']    = $this->get_brand_name($product_id);
                $item_details_array['category'] = $this->get_product_category($product_id);
//                $item_details_array['variant'] = '';
                $item_details_array['list_position'] = $list_position++;
            }

            array_push($order_items_array, $item_details_array);
        }

        // apply filter to the $order_items_array array
        $order_items_array = apply_filters('wgact_filter', $order_items_array, 'order_items_array');

        return $order_items_array;
    }

    protected function get_event_purchase_json($order, $order_total, $order_currency, $is_new_customer, $channel)
    {
        $gtag_data = [
            'send_to'        => [],
            'transaction_id' => $order->get_order_number(),
            'currency'       => $order_currency,
            'discount'       => $order->get_total_discount(),
            'items'          => $this->get_formatted_order_items($order, $channel),
        ];

        if ('ads' === $channel) {
            array_push($gtag_data['send_to'], $this->get_google_ads_conversion_ids(true));
            $gtag_data['value']            = $order_total;
            $gtag_data['aw_merchant_id']   = $this->aw_merchant_id;
            $gtag_data['aw_feed_country']  = $this->get_visitor_country();
            $gtag_data['aw_feed_language'] = $this->get_gmc_language();
            $gtag_data['new_customer']     = $is_new_customer;
        }

        if ('analytics' === $channel) {
            if ($this->options_obj->google->analytics->universal->property_id) array_push($gtag_data['send_to'], $this->options_obj->google->analytics->universal->property_id);
            if ($this->options_obj->google->analytics->ga4->measurement_id) array_push($gtag_data['send_to'], $this->options_obj->google->analytics->ga4->measurement_id);
            $gtag_data['affiliation'] = (string)get_bloginfo('name');
            $gtag_data['tax']         = (string)$order->get_total_tax();
            $gtag_data['shipping']    = (string)$order->get_total_shipping();
            $gtag_data['value']       = (float)$order->get_total();
        }

        return json_encode($gtag_data);
    }

    protected function get_gmc_language(): string
    {
        return strtoupper(substr(get_locale(), 0, 2));
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

    protected function get_google_ads_conversion_ids($purchase = false): array
    {
        $formatted_conversion_ids = [];
        if ($purchase) {
            foreach ($this->conversion_identifiers as $conversion_id => $conversion_label) {
                array_push($formatted_conversion_ids, 'AW-' . $conversion_id . '/' . $conversion_label);
            }
        } else {
            foreach ($this->conversion_identifiers as $conversion_id => $conversion_label) {
                array_push($formatted_conversion_ids, 'AW-' . $conversion_id);
            }
        }
        return $formatted_conversion_ids;
    }
}