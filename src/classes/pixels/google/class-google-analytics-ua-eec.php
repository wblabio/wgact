<?php


namespace WGACT\Classes\Pixels\Google;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google_Analytics_UA_EEC extends Google_Analytics_UA
{
    use Trait_Google;

    public function __construct()
    {
        parent::__construct();
    }

    protected function output_view_item_list_html($items, $list_id){
        // handled on front-end
    }

    public function inject_product($product, $product_attributes)
    {
        $data = [
            'id'            => (string)$product->get_id(),
            'name'          => (string)$product->get_name(),
            //            'list_name'     => 'Search Results',  // should probably be empty
            'brand'         => (string)$product_attributes['brand'],
            'category'      => $this->get_product_category($product->get_id()),
            //            'variant'       => 'Black',
            'list_position' => 1,
            'quantity'      => 1,
            'price'         => (int)$product->get_price(),
        ];

        echo "
                wooptpmExists().then(function(){
                    gtag('event', 'view_item', {
                        'send_to': '" . $this->options_obj->google->analytics->universal->property_id . "',
                        'items': [" . json_encode($data) . "]
                    });
                });
        ";
    }

    public function inject_cart($cart, $cart_total)
    {
        // triggered by front-end script
    }

    protected function eec_appweb_get_product_details_array($product_id, $list_id, $position = null): array
    {
        $list = $this->get_list_name_by_current_page_type($list_id);

        $product = wc_get_product($product_id);

        $data = [
            'id'            => (string)$product_id,
            'name'          => (string)$product->get_name(),
            //            'list_name'     => (string)$list,
            'brand'         => (string)$this->get_brand_name($product_id),
            'category'      => $this->get_product_category($product_id),
            // 'variant' => (string) $item['variant'],
            'list_position' => (int)$position,
            'quantity'      => 1,
            'price'         => (float)$product->get_price(),
        ];

//        $data = $this->eec_add_product_categories_to_product_item($data, $product_id);

        if ($list) {
            $data['list_name'] = $list . $this->get_list_name_suffix();
        }
//		if ($list) $data['item_list_id']   = '';
//        if ($position) {
//            $data['index'] = $position;
//        }

        return $data;
    }
}