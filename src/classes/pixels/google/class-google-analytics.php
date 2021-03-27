<?php

namespace WGACT\Classes\Pixels\Google;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google_Analytics extends Google_Pixel
{
    public function __construct()
    {
        parent::__construct();
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
}