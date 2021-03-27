<?php


namespace WGACT\Classes\Pixels\Google;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google_Analytics_UA_EEC_Pixel extends Google_Analytics
{
    use Trait_Google;

    public function __construct()
    {
        parent::__construct();
    }

    public function inject_product_list_object($list_id)
    {
//        error_log('list_id:' . $list_id);
        global $wp_query;

        $items = [];

        $position = 1;

        $posts = $wp_query->posts;

        foreach ($posts as $key => $post) {

            if ($post->post_type == 'product') {

                array_push($items, $this->eec_appweb_get_product_details_array($post->ID, $list_id, $position));

                $position++;
            }
        }

        ?>

                gtag('event', 'view_item_list', {
                    "send_to": '<?php echo $this->options_obj->google->analytics->universal->property_id ?>',
                    "items": <?php echo json_encode($items) . PHP_EOL ?>
                });
        <?php
    }

    public function inject_product($product, $product_attributes)
    {
        $data = [
            'id'            => $product->get_id(),
            'name'          => (string)$product->get_name(),
            //            'list_name'     => 'Search Results',  // should probably be empty
            'brand'         => (string)$product_attributes['brand'],
            'category'      => $this->get_product_category($product->get_id()),
            //            'variant'       => 'Black',
            'list_position' => 1,
            'quantity'      => 1,
            'price'         => (int)$product->get_price(),
        ];

        ?>

                gtag('event', 'view_item', {
                    "send_to": '<?php echo $this->options_obj->google->analytics->universal->property_id ?>',
                    "items": [<?php echo json_encode($data) ?>]
                });
        <?php
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
            $data['list_name'] = $list . $this->get_list_suffix();
        }
//		if ($list) $data['item_list_id']   = '';
//        if ($position) {
//            $data['index'] = $position;
//        }

        return $data;
    }

    protected function get_list_suffix(): string
    {
        $list_suffix = '';

        if (is_product_category()) {

            $category    = get_queried_object();
            $list_suffix = ' | ' . $category->name;
            $list_suffix = $this->add_parent_category_name($category, $list_suffix);
        } else if (is_product_tag()) {
            $tag         = get_queried_object();
            $list_suffix = ' | ' . $tag->name;
        }

        return $list_suffix;
    }

    protected function add_parent_category_name($category, $list_suffix)
    {
        if ($category->parent > 0) {

            $parent_category = get_term_by('id', $category->parent, 'product_cat');
            $list_suffix     = ' | ' . $parent_category->name . $list_suffix;
            $list_suffix     = $this->add_parent_category_name($parent_category, $list_suffix);
        }

        return $list_suffix;
    }


    public function inject_order_received_page($order, $order_total, $order_item_ids, $is_new_customer)
    {
        $order_currency = $this->get_order_currency($order);

        ?>

                if  ((typeof wooptpm !== "undefined") && !wooptpm.isOrderIdStored(<?php echo $order->get_id() ?>)) {
                  gtag('event', 'purchase', <?php echo $this->get_event_purchase_json($order, $order_total, $order_currency, $is_new_customer, 'ga_ua') ?>);
                }
        <?php
    }
}