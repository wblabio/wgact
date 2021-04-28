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

    public function __construct($options)
    {
        parent::__construct($options);

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