<?php

namespace WGACT\Classes\Pixels\Google;

use WGACT\Classes\Admin\Environment_Check;
use WGACT\Classes\Pixels\Trait_Shop;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google_Analytics extends Google
{
    use Trait_Shop;

    public function __construct()
    {
        parent::__construct();

        $this->pixel_name = 'google_analytics';
    }

    protected function get_list_name_by_current_page_type($list_id): string
    {
        $list_names = [
            'add_payment_method_page' => 'Add Payment Method page',
            'cart'                    => 'Cart',
            'checkout'                => 'Checkout Page',
            'checkout_pay_page'       => 'Checkout Pay Page',
            'front_page'              => 'Front Page',
            'order_received_page'     => 'Order Received Page',
            'product'                 => 'Product',
            'product_category'        => 'Product Category',
            'product_detail'          => 'Product Detail',
            'product_tag'             => 'Product Tag',
            'product_taxonomy'        => 'Product Taxonomy',
            'search'                  => 'Search Page',
            'shop'                    => 'Shop Page',
        ];

        return $list_names[$list_id];
    }

    protected function wooptpm_get_order_item_price($order_item, $product): float
    {
        if ((new Environment_Check())->is_woo_discount_rules_active()) {
            $item_value = $order_item->get_meta('_advanced_woo_discount_item_total_discount');
            if (is_array($item_value) && array_key_exists('discounted_price', $item_value) && $item_value['discounted_price'] != 0) {
                return $item_value['discounted_price'];
            } elseif (is_array($item_value) && array_key_exists('initial_price', $item_value) && $item_value['initial_price'] != 0) {
                return $item_value['initial_price'];
            } else {
                return $product->get_price();
            }
        } else {
            return $product->get_price();
        }
    }

    protected function get_order_item_data($order_item): array
    {
        $product = $order_item->get_product();

        $dyn_r_ids = $this->get_dyn_r_ids($product);

        return [
            'id'       => (string)$dyn_r_ids[$this->get_ga_id_type()],
            'name'     => (string)$product->get_name(),
            'quantity' => (int)$order_item['quantity'],
//            'affiliation' => '',
//            'coupon' => '',
//            'discount' => 0,
            'brand'    => (string)$this->get_brand_name($product->get_id()),
            'category' => (array)$this->get_product_category($product->get_id()),
            //                    'variant' => ,
//            'tax'      => 0,
            'price'    => (float)$this->wooptpm_get_order_item_price($order_item, $product),
            //                    'list_name' => ,
//            'currency' => '',
        ];
    }

//    public function inject_product_list_object($list_id)
//    {
//        global $wp_query;
//
//        $items = [];
//
//        $position = 1;
//
//        $posts = $wp_query->posts;
//
//        foreach ($posts as $key => $post) {
//
//            if ($post->post_type == 'product' || $post->post_type == 'product_variation') {
//
//                array_push($items, $this->eec_appweb_get_product_details_array($post->ID, $list_id, $position));
//
//                $position++;
//            }
//        }
//
//        $this->output_view_item_list_html($items, $list_id);
//    }
}