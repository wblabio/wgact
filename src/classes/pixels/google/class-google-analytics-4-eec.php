<?php


namespace WGACT\Classes\Pixels\Google;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google_Analytics_4_EEC extends Google_Analytics_4
{
    use Trait_Google;

    public function __construct()
    {
        parent::__construct();
    }

    protected function output_view_item_list_html($items, $list_id)
    {
        // handled on front-end
    }

    public function inject_cart($cart, $cart_total)
    {
        // handled on front-end
    }

    public function inject_product($product, $product_attributes)
    {
        // handled on front-end
    }
}