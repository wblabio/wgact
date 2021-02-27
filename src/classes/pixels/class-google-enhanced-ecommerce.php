<?php


namespace WGACT\Classes\Pixels;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google_Enhanced_Ecommerce extends Google_Pixel
{
    use Trait_Google;

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

        <script>
            gtag('event', 'view_item_list', {"items": <?php echo json_encode($items) ?>});
        </script>
        <?php
    }

    public function inject_product($product_id, $product, $product_attributes)
    {
        $data = [
            'id'            => $product_id,
            'name'          => (string)$product->get_name(),
            //            'list_name'     => 'Search Results',  // should probably be empty
            'brand'         => (string)$this->get_brand_name($product_id),
            'category'      => $this->get_product_category($product_id),
            //            'variant'       => 'Black',
            'list_position' => 1,
            'quantity'      => 1,
            'price'         => (int)$product->get_price(),
        ];

        ?>

        <script>
            gtag('event', 'view_item', {
                "items": [<?php echo json_encode($data) ?>]
            });
        </script>
        <?php
    }

    public function inject_cart($cart, $cart_total)
    {
        // no function yet
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