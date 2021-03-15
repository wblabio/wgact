<?php

namespace WGACT\Classes\Pixels;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google_Ads extends Google_Pixel
{
    protected $google_business_vertical;

    public function __construct($options, $options_obj)
    {
        parent::__construct($options, $options_obj);
    }

    public function inject_product_category()
    {
        global $wp_query;

        if ($this->is_dynamic_remarketing_active()) {

            ?>

            <script type="text/javascript">
                gtag('event', 'view_item_list', {
                    'send_to': <?php echo json_encode($this->get_google_ads_conversion_ids()) ?>,
                    'items'  : <?php echo json_encode($this->get_products_from_wp_query($wp_query)) . PHP_EOL ?>
                });
            </script>
            <?php
        }
    }

    public function inject_search()
    {
        global $wp_query;

        if ($this->is_dynamic_remarketing_active()) {

            ?>

            <script type="text/javascript">
                gtag('event', 'view_search_results',
                    {
                        'send_to': <?php echo json_encode($this->get_google_ads_conversion_ids()) ?>,
                        'items'  : <?php echo json_encode($this->get_products_from_wp_query($wp_query)) . PHP_EOL ?>
                    });
            </script>
            <?php
        }
    }

    public function inject_product($product_id_compiled, $product, $product_attributes)
    {
//global $wp_query;
//        error_log(print_r($related_products, true));
//        error_log(print_r(wc_get_related_products($product->get_id()), true));
//                error_log(print_r($wp_query, true));


        if ($this->is_dynamic_remarketing_active()) {

            $product_details = $this->get_gads_formatted_product_details_from_product_id($product->get_id());
            ?>

            <script type="text/javascript">
                gtag('event', 'view_item', {
                    'send_to': <?php echo json_encode($this->get_google_ads_conversion_ids()) ?>,
                    'value'  : <?php echo $product_details['price'] ?>,
                    'items'  : [<?php echo(json_encode($product_details)) ?>]
                });
            </script>
            <?php
        }
    }

    public function inject_cart($cart, $cart_total)
    {
        if ($this->is_dynamic_remarketing_active()) {

            ?>

            <script type="text/javascript">
                gtag('event', 'add_to_cart', {
                    'send_to': <?php echo json_encode($this->get_google_ads_conversion_ids()) ?>,
                    'value'  : <?php echo $cart_total ?>,
                    'items'  : <?php echo (json_encode($this->get_gads_formatted_cart_items($cart))) . PHP_EOL ?>
                });
            </script>
            <?php
        }
    }

    private function is_dynamic_remarketing_active(): bool
    {
        if ($this->dynamic_remarketing && $this->options_obj->google->ads->conversion_id) {
            return true;
        } else {
            return false;
        }
    }

    public function inject_order_received_page($order, $order_total, $order_item_ids, $is_new_customer)
    {
        $order_currency = $this->get_order_currency($order);

        ?>

        <?php

        // if cart data beta is off and conversion id has been set
        if ($this->add_cart_data == false && $this->options_obj->google->ads->conversion_id) {

            // no deduper needed here
            // Google does this server side
            if ($this->options_obj->google->ads->conversion_label): ?>

                <script>
                    gtag('event', 'conversion', {
                        'send_to'       : <?php echo json_encode($this->get_google_ads_conversion_ids(true))?>,
                        'value'         : <?php echo $order_total; ?>,
                        'currency'      : '<?php echo $order_currency; ?>',
                        'transaction_id': '<?php echo $order->get_order_number(); ?>',
                    });
                </script>
            <?php
            endif; ?>

            <?php
            if ($this->is_dynamic_remarketing_active()) echo $this->get_dyn_remarketing_purchase_script($order, $order_total) ?>

            <?php
        }

        if ($this->add_cart_data == true && $this->conversion_id && $this->conversion_label ) {
            ?>

            <script>
                gtag('event', 'purchase', <?php echo $this->get_event_purchase_json($order, $order_total, $order_currency, $is_new_customer, 'ads') ?>);
            </script>
            <?php
        }

        ?>

        <?php
    }


    // get products from wp_query
    protected function get_products_from_wp_query($wp_query): array
    {
        $items = [];

        $posts = $wp_query->posts;

        foreach ($posts as $key => $post) {

            if ($post->post_type == 'product') {

                $item_details = [];

                $product                                  = wc_get_product($post->ID);
                $item_details['id']                       = $this->get_compiled_product_id($post->ID, $product->get_sku());
                $item_details['google_business_vertical'] = $this->google_business_vertical;

                array_push($items, $item_details);
            }
        }

        return $items;
    }

    protected function get_gads_formatted_product_details_from_product_id($product_id): array
    {
        $product = wc_get_product($product_id);

        $product_details['id']       = $this->get_compiled_product_id($product_id, $product->get_sku());
        $product_details['category'] = $this->get_product_category($product_id);
        // $product_details['list_position'] = 1;
        $product_details['quantity']                 = 1;
        $product_details['price']                    = (float)$product->get_price();
        $product_details['google_business_vertical'] = $this->google_business_vertical;

        return $product_details;
    }

    // get an array with all cart product ids
    protected function get_gads_formatted_cart_items($cart)
    {
//         error_log(print_r($cart, true));
        // initiate product identifier array
        $cart_items   = [];
        $item_details = [];

        // go through the array and get all product identifiers
        foreach ((array)$cart as $cart_item) {
//            error_log(print_r($cart_item,true));
            $product_id = $this->get_variation_or_product_id($cart_item, $this->options_obj->general->variations_output);
//            error_log('id: ' . $product_id);
            $product = wc_get_product($product_id);

            $item_details['id']                       = $this->get_compiled_product_id($product_id, $product->get_sku());
            $item_details['quantity']                 = (int)$cart_item['quantity'];
            $item_details['price']                    = (int)$product->get_price();
            $item_details['google_business_vertical'] = $this->google_business_vertical;

            array_push($cart_items, $item_details);
        }

        // apply filter to the $cartprods_items array
        $cart_items = apply_filters('wgact_filter', $cart_items, 'cart_items');

        return $cart_items;
    }

    protected function get_dyn_remarketing_purchase_script($order, $order_total)
    {
        ?>

        <script>
            if ((typeof wgact !== "undefined") && !wgact.isOrderIdStored(<?php echo $order->get_id() ?>)) {
                gtag('event', 'purchase', {
                    'send_to': <?php echo json_encode($this->get_google_ads_conversion_ids()) ?>,
                    'value'  : <?php echo $order_total; ?>,
                    'items'  : <?php echo (json_encode($this->get_formatted_order_items($order))) . PHP_EOL ?>
                });
            }
        </script>
        <?php
    }
}