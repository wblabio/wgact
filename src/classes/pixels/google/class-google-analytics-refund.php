<?php

namespace WGACT\Classes\Pixels\Google;

use WC_Order;
use WC_Order_Refund;
use WC_Product;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google_Analytics_Refund extends Google_Analytics
{
    use Trait_Google;

    public function __construct($options)
    {
        parent::__construct($options);
    }

    /**
     * Processes all prepared refunds in post_meta and outputs them on the frontend into the dataLayer.
     * We only process this on the frontend since the output on is_order_received_page has a higher chance to get
     * processed properly through GTM.
     */
    public function process_refund_to_frontend__premium_only()
    {
        global $wpdb;

        // eec order refund logic
        // the following condition is to limit running the following script and potentially overload the server
        if (is_order_received_page()) {

//            error_log('processing refunds');

            $sql = "SELECT meta_id, post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'wooptpm_refund_processed' AND `meta_value` = false";

            $results = $wpdb->get_results($sql);

            foreach ($results as $result) {

                $refund = new WC_Order_Refund($result->post_id);
                $order  = new WC_Order($refund->get_parent_id());

                $refund_items = $refund->get_items();

                $dataLayer_refund_items_ga_ua = [];
                $dataLayer_refund_items_ga_4  = [];

                foreach ($refund_items as $refund_item) {

                    $product = new WC_Product($refund_item->get_product_id());

                    $dyn_r_ids = $this->get_dyn_r_ids($product);

                    $dataLayer_refund_items_ga_ua[] = [
                        'id'       => (string)$dyn_r_ids[$this->get_ga_id_type()],
                        'name'     => (string)$refund_item->get_name(),
                        'brand'    => (string)$this->get_brand_name($refund_item->get_product_id()),
                        'category' => (array)$this->get_product_category($refund_item->get_product_id()),
                        //                        'coupon'        => '',
                        //                        'list_name' => '',
                        //                        'list_position' => 0,
                        'price'    => (float)$product->get_price(),
                        'quantity' => -1 * (int)$refund_item->get_quantity(),
                        //                        'variant'  => '',
                    ];

                    $dataLayer_refund_items_ga_4[] = [
                        'item_id'       => (string)$dyn_r_ids[$this->get_ga_id_type()],
                        'item_name'     => (string)$refund_item->get_name(),
                        'quantity'      => -1 * (int)$refund_item->get_quantity(),
                        //                        'affiliation'   => '',
                        //                        'coupon'        => '',
                        //                        'discount'      => 0,
                        'item_brand'    => (string)$this->get_brand_name($refund_item->get_product_id()),
                        'item_category' => (array)$this->get_product_category($refund_item->get_product_id()),
                        //                        'item_variant'  => '',
                        //                        'tax'           => 0,
                        'price'         => (float)$product->get_price(),
                        //                        'currency'      => '',
                    ];
                }

//                (new Google_Analytics_Refund_UA())->output_refund_to_frontend($order, $refund, $dataLayer_refund_items_ga_ua);
                (new Google_Analytics_Refund_4($this->options))->output_refund_to_frontend($order, $refund, $dataLayer_refund_items_ga_4);

                update_post_meta($result->post_id, 'wooptpm_refund_processed', true);
            }
        }
    }
}