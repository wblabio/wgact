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

//    protected function eec_appweb_get_product_details_array($product_id, $list_id, $position = null): array
//    {
//        $list = $this->get_list_name_by_current_page_type($list_id);
//
//        $product = wc_get_product($product_id);
//
//        $data = [
//            'item_id'        => (string)$product_id,
//            'item_name'      => (string)$product->get_name(),
//            'quantity'       => 1,
//            //            'affiliation' => '',
//            //            'coupon' => '',
//            //            'discount' => 0,
//            'index'          => (int)$position,
//            'item_brand'     => (string)$this->get_brand_name($product_id),
//            'item_category'  => (array)$this->get_product_category($product_id),
//            'item_list_name' => (string)$list,
//            'item_list_id'   => (string)$list_id,
//            // 'item_variant' => (string) $item['variant'],
//            'price'          => (float)$product->get_price(),
//            //            'currency' => '',
//        ];
//
//
//        if ($list) {
//            $data['item_list_name'] = $list . $this->get_list_name_suffix();
//            $data['item_list_id']   = $list_id . $this->get_list_id_suffix();
//        }
//
//        return $data;
//    }
}