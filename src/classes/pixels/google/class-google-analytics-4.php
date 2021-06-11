<?php


namespace WGACT\Classes\Pixels\Google;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google_Analytics_4 extends Google_Analytics
{
    use Trait_Google;

    public function __construct($options)
    {
        parent::__construct($options);
    }

    public function inject_order_received_page($order, $order_total, $is_new_customer)
    {
        $order_currency = $this->get_order_currency($order);

        echo "       
                wooptpmExists().then(function(){
                    if  (!wooptpm.isOrderIdStored('" . $order->get_id() . "')) {
                        gtag('event', 'purchase', " . $this->get_event_purchase_json($order, $order_total, $order_currency, $is_new_customer) . ");
                    }
                });
        ";
    }

    protected function get_event_purchase_json($order, $order_total, $order_currency, $is_new_customer, $channel = null)
    {
        $gtag_data = [
            'send_to'        => [],
            'transaction_id' => (string)$order->get_order_number(),
            'affiliation'    => (string)get_bloginfo('name'),
            'currency'       => (string)$this->get_order_currency($order),
            'value'          => (float)$order->get_total(),
            'discount'       => (float)$order->get_total_discount(),
            'tax'            => (float)$order->get_total_tax(),
            'shipping'       => (float)$order->get_total_shipping(),
            //            'coupon'           => (array)$order->get_used_coupons(),
            'items'          => (array)$this->get_formatted_order_items($order),
        ];

        array_push($gtag_data['send_to'], $this->options_obj->google->analytics->ga4->measurement_id);

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
                'item_id'       => $order_item_data['id'],
                'item_name'     => $order_item_data['name'],
                'quantity'      => $order_item_data['quantity'],
//                'affiliation'   => $order_item_data['affiliation'],
                //                'coupon' => '',
                //                'discount' => 0,
                'item_brand'    => $order_item_data['brand'],
                'item_category' => $order_item_data['category'],
                'item_variant'  => $order_item_data['variant'],
                //                'tax' => 0,
                'price'         => $order_item_data['price'],
                //                    'list_name' => ,
                'currency'      => get_woocommerce_currency(),
            ];

            array_push($order_items_array, $item_details_array);
        }

        // apply filter to the $order_items_array array
        $order_items_array = apply_filters_deprecated('wgact_filter', [$order_items_array], '1.10.2', '', 'This filter has been deprecated without replacement.');

        return $order_items_array;
    }
}