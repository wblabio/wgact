<?php


namespace WGACT\Classes\Pixels\Google;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google_Analytics_UA extends Google_Analytics
{
    public function __construct()
    {
        parent::__construct();
    }

    public function inject_order_received_page($order, $order_total, $is_new_customer)
    {
        $order_currency = $this->get_order_currency($order);

        echo "
                 wooptpmExists().then(function(){
                    if  (!wooptpm.isOrderIdStored(" . $order->get_order_number() . ")) {
                        gtag('event', 'purchase', " . $this->get_event_purchase_json($order, $order_total, $order_currency, $is_new_customer) . ")
                    }
                }).catch(() => {
                    console.log('couldn\'t run gtag for GA UA');
                });
        ";
    }

    protected function get_event_purchase_json($order, $order_total, $order_currency, $is_new_customer, $channel = null)
    {
        $gtag_data = [
            'send_to'        => [],
            'transaction_id' => (string)$order->get_order_number(),
            'affiliation'    => (string)get_bloginfo('name'),
            'currency'       => (string)$order_currency,
            'value'          => (float)$order->get_total(),
            'discount'       => (float)$order->get_total_discount(),
            'tax'            => (float)$order->get_total_tax(),
            'shipping'       => (float)$order->get_total_shipping(),
            'items'          => (array)$this->get_formatted_order_items($order),
        ];

        array_push($gtag_data['send_to'], $this->options_obj->google->analytics->universal->property_id);

        return json_encode($gtag_data);
    }

    protected function get_formatted_order_items($order, $channel = null)
    {
        $order_items       = $order->get_items();
        $order_items_array = [];

        $list_position = 1;

        foreach ((array)$order_items as $order_item) {

//            $product_id = $this->get_variation_or_product_id($order_item->get_data(), $this->options_obj->general->variations_output);

            $order_item_data = $this->get_order_item_data($order_item);

            $item_details_array = [
                'id'            => $order_item_data['id'],
                'quantity'      => $order_item_data['quantity'],
                'price'         => $order_item_data['price'],
                'name'          => $order_item_data['name'],
                //                    'list_name' => ,
                'brand'         => $order_item_data['brand'],
                'category'      => $order_item_data['category'],
                //                    'variant' => ,
                'list_position' => (int)$list_position++,
            ];

            array_push($order_items_array, $item_details_array);
        }

        // apply filter to the $order_items_array array
        $order_items_array = apply_filters('wgact_filter', $order_items_array, 'order_items_array');

        return $order_items_array;
    }
}