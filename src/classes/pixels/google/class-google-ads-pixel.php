<?php

namespace WGACT\Classes\Pixels\Google;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google_Ads_Pixel extends Google_Pixel
{
    use Trait_Google;

    protected $google_business_vertical;

    public function __construct()
    {
        parent::__construct();

        add_action('wp_enqueue_scripts', [$this, 'wooptpm_google_ads_front_end_scripts']);

//        $this->pixel_name = 'google_ads';
    }

    public function wooptpm_google_ads_front_end_scripts()
    {
        wp_enqueue_script('google-ads', plugin_dir_url(__DIR__) . '../../js/public/google_ads.js', [], WGACT_CURRENT_VERSION, false);
    }

    public function inject_product_list($list_name)
    {
        global $wp_query;

        ?>

                gtag('event', '<?php echo $list_name ?>', {
                    'send_to': <?php echo json_encode($this->get_google_ads_conversion_ids()) ?>,
                    'items'  : <?php echo json_encode($this->get_products_from_wp_query($wp_query)) . PHP_EOL ?>
                });
        <?php
    }

    public function inject_product($product, $product_attributes)
    {
        $product_details = $this->get_gads_formatted_product_details_from_product_id($product->get_id());

        ?>

                gtag('event', 'view_item', {
                    'send_to': <?php echo json_encode($this->get_google_ads_conversion_ids()) ?>,
                    'value'  : <?php echo $product_details['price'] ?>,
                    'items'  : [<?php echo(json_encode($product_details)) ?>]
                });
        <?php
    }

    public function inject_cart($cart, $cart_total)
    {
//       triggered by front-end scripts
    }

    public function inject_order_received_page($order, $order_total, $is_new_customer)
    {
        $order_currency = $this->get_order_currency($order);

        ?>

        <?php

        // if cart data beta is off and conversion id has been set
        if ($this->add_cart_data == false && $this->options_obj->google->ads->conversion_id) {

            // no deduper needed here
            // Google does this server side
            if ($this->options_obj->google->ads->conversion_label): ?>

                gtag('event', 'conversion', {
                    'send_to'       : <?php echo json_encode($this->get_google_ads_conversion_ids(true)) ?>,
                    'value'         : <?php echo $order_total; ?>,
                    'currency'      : '<?php echo $order_currency; ?>',
                    'transaction_id': '<?php echo $order->get_order_number(); ?>',
                });
            <?php
            endif; ?>

            <?php
            if ($this->is_dynamic_remarketing_active()) echo $this->get_dyn_remarketing_purchase_script($order, $order_total) ?>

            <?php
        }

        if ($this->add_cart_data == true && $this->conversion_id && $this->conversion_label) {
            ?>

                gtag('event', 'purchase', <?php echo $this->get_event_purchase_json($order, $order_total, $order_currency, $is_new_customer, 'ads') ?>);
            <?php
        }
    }


    // get products from wp_query
    protected function get_products_from_wp_query($wp_query): array
    {
        $items = [];

        $posts = $wp_query->posts;

        foreach ($posts as $key => $post) {

            if ($post->post_type == 'product' || $post->post_type == 'product_variation') {

                $item_details = [];

                $product = wc_get_product($post->ID);

                // only continue if WC retrieves a valid product
                if (!is_bool($product)) {

                    $dyn_r_ids = $this->get_dyn_r_ids($product);

                    $item_details['id']                       = $dyn_r_ids[$this->get_dyn_r_id_type()];
                    $item_details['google_business_vertical'] = $this->google_business_vertical;

                    array_push($items, $item_details);
                }
            }
        }

        return $items;
    }

    protected function get_gads_formatted_product_details_from_product_id($product_id): array
    {
        $product = wc_get_product($product_id);

        $dyn_r_ids = $this->get_dyn_r_ids($product);

        $product_details['id']       = $dyn_r_ids[$this->get_dyn_r_id_type()];
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

            $dyn_r_ids = $this->get_dyn_r_ids($product);

            $item_details['id']                       = $dyn_r_ids[$this->get_dyn_r_id_type()];
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

                if ((typeof wooptpm !== "undefined") && !wooptpm.isOrderIdStored(<?php echo $order->get_id() ?>)) {
                    gtag('event', 'purchase', {
                        'send_to': <?php echo json_encode($this->get_google_ads_conversion_ids()) ?>,
                        'value'  : <?php echo $order_total; ?>,
                        'items'  : <?php echo (json_encode($this->get_formatted_order_items($order, 'ads'))) . PHP_EOL ?>
                    });
                }
        <?php
    }
}