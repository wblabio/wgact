<?php

namespace WGACT\Classes\Pixels;

use stdClass;
use WC_Order;
use WGACT\Classes\Admin\Environment_Check;
use WC_Order_Refund;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Pixel_Manager extends Pixel_Manager_Base
{
    use Trait_Product;
    use Trait_Google;
    use Trait_Temp;

    protected $options;
    protected $options_obj;
    protected $cart;
    protected $facebook_active;
    protected $google_active;
    protected $transaction_deduper_timeout = 2000;
    protected $google_pixel_manager;
    protected $hotjar_pixel;
    protected $facebook_pixel_manager;
    protected $bing_pixel;
    protected $twitter_pixel;
    protected $pinterest_pixel;

    public function __construct()
    {
        /*
         * Initialize options
         */
        $this->options = get_option(WGACT_DB_OPTIONS_NAME);

        $this->options_obj = json_decode(json_encode($this->options));

        $this->options_obj->shop->currency = new stdClass();
        $this->options_obj->shop->currency = get_woocommerce_currency();

        /*
         * Set a few states
         */
        $this->facebook_active = !empty($this->options_obj->facebook->pixel_id);
        $this->google_active   = $this->google_active();

        /*
         * Compatibility modes
         */
        if ($this->options_obj->general->maximum_compatibility_mode) (new Environment_Check())->enable_maximum_compatibility_mode();

        if (
            $this->options_obj->general->maximum_compatibility_mode &&
            $this->options_obj->facebook->microdata
        ) {
            (new Environment_Check())->enable_maximum_compatibility_mode_yoast_seo();
        }
        /*
         * Inject pixel snippets in head
         */
//        add_action('wp_head', function () {
//            $this->inject_head_pixels();
//        });

        add_action('wp_head', function () {
            $this->inject_woopt_opening();
            $this->inject_wgact_order_deduplication_script();

            $this->inject_data_layer_init();
            $this->inject_data_layer_shop();
            $this->inject_data_layer_product();
        });


        /*
         * Initialize all pixels
         */
        if ($this->google_active) $this->google_pixel_manager = new Google_Pixel_Manager();
        if ($this->facebook_active) $this->facebook_pixel_manager = new Facebook_Pixel_Manager();
        if ($this->options_obj->bing->uet_tag_id) $this->bing_pixel = new Bing();
        if ($this->options_obj->hotjar->site_id) $this->hotjar_pixel = new Hotjar();
        if ($this->options_obj->twitter->pixel_id) $this->twitter_pixel = new Twitter();
        if ($this->options_obj->pinterest->pixel_id) $this->pinterest_pixel = new Pinterest();

        add_action('wp_head', function () {
            $this->inject_woopt_closing();
        });

        /*
         * Front-end script section
         */
        add_action('wp_enqueue_scripts', [$this, 'wooptpm_front_end_scripts']);

        add_action('wp_ajax_wooptpm_get_cart_items', [$this, 'ajax_wooptpm_get_cart_items__premium_only']);
        add_action('wp_ajax_nopriv_wooptpm_get_cart_items', [$this, 'ajax_wooptpm_get_cart_items__premium_only']);

        if (wga_fs()->is__premium_only()) {
            add_action('wp_ajax_wgact_purchase_pixels_fired', [$this, 'ajax_purchase_pixels_fired_handler__premium_only']);
            add_action('wp_ajax_nopriv_wgact_purchase_pixels_fired', [$this, 'ajax_purchase_pixels_fired_handler__premium_only']);
        }


        /*
         * Inject pixel snippets after <body> tag
         */
        if (did_action('wp_body_open')) {
            add_action('wp_body_open', function () {
                $this->inject_body_pixels();
            });
        }

        /*
         * Process short codes
         */
        new Shortcodes($this->options, $this->options_obj);
    }

    private function inject_data_layer_init()
    {
        ?>
        <script>
            window.wooptpmDataLayer = window.wooptpmDataLayer || [];
            window.wooptpmDataLayer['cart'] = window.wooptpmDataLayer['cart'] || {};
        </script>

        <?php
    }


    public function inject_woopt_opening()
    {
        echo PHP_EOL . '<!-- START woopt Pixel Manager -->' . PHP_EOL;
    }

    public function inject_woopt_closing()
    {
        echo PHP_EOL . '<!-- END woopt Pixel Manager -->' . PHP_EOL;
    }

    public function ajax_wooptpm_get_cart_items__premium_only()
    {
        global $woocommerce;

        $cart_items = $woocommerce->cart->get_cart();

        $data = [];

        foreach ($cart_items as $cart_item => $value) {

//            error_log('qty: ' . $value['quantity']);

//            error_log(print_r($value['data'], true));

            $product = wc_get_product($value['data']->get_id());

            $data['cart_item_keys'][$cart_item] = [
                'id'           => $product->get_id(),
                'is_variation' => false,
            ];

            $data['cart'][$product->get_id()] = [
                'id'           => (string)$product->get_id(),
                'name'         => (string)$product->get_name(),
                //                'list_name'     => '',
                'brand'        => (string)$this->get_brand_name($product->get_id()),
                //                'variant'       => '',
                //                'list_position' => '',
                'quantity'     => (int)$value['quantity'],
                'price'        => (int)$product->get_price(),
                'is_variation' => false,
            ];
//            error_log('id: ' . $product->get_id());
//            error_log('type: ' . $product->get_type());

            if ($product->is_type('variation')) {
//                error_log('is variation');
                $data['cart'][$product->get_id()]['parent_id']    = (string)$product->get_parent_id();
                $data['cart'][$product->get_id()]['is_variation'] = true;
                $data['cart'][$product->get_id()]['category']     = $this->get_product_category($product->get_parent_id());


                $data['cart_item_keys'][$cart_item]['parent_id']    = (string)$product->get_parent_id();
                $data['cart_item_keys'][$cart_item]['is_variation'] = true;
            } else {
                $data['cart'][$product->get_id()]['category'] = $this->get_product_category($product->get_id());
            }
        }

//        error_log(print_r($data, true));

        wp_send_json($data);
    }

    public function ajax_purchase_pixels_fired_handler__premium_only()
    {
        $order_id = $_POST['order_id'];
        update_post_meta($order_id, '_WGACT_conversion_pixel_fired', true);
        wp_die(); // this is required to terminate immediately and return a proper response
    }

    public function wooptpm_front_end_scripts()
    {
        wp_enqueue_script('front-end-scripts', plugin_dir_url(__DIR__) . '../js/public/wooptpm.js', [], WGACT_CURRENT_VERSION, false);
        if (wga_fs()->is__premium_only()) {
            wp_enqueue_script('front-end-scripts-premium-only', plugin_dir_url(__DIR__) . '../js/public/wooptpm__premium_only.js', [], WGACT_CURRENT_VERSION, false);
            wp_localize_script('front-end-scripts-premium-only', 'ajax_object', ['ajax_url' => admin_url('admin-ajax.php')]);
        }
    }

    public function inject_head_pixels()
    {
        global $woocommerce;

        if ((new Environment_Check())->is_autoptimize_active()) {
            $this->inject_noptimize_opening_tag();
        }

        echo PHP_EOL . '<!-- START woopt Pixel Manager -->' . PHP_EOL;

        $this->inject_wgact_order_deduplication_script();

//        $this->google_pixel_manager->inject_everywhere();


//        if ($this->google_active) $this->google_pixel_manager->inject_everywhere();
        if ($this->facebook_active) $this->facebook_pixel_manager->inject_everywhere();

        if (wga_fs()->is__premium_only()) {
            if ($this->options_obj->bing->uet_tag_id) $this->bing_pixel->inject_everywhere();
            if ($this->options_obj->twitter->pixel_id) $this->twitter_pixel->inject_everywhere();
            if ($this->options_obj->pinterest->pixel_id) $this->pinterest_pixel->inject_everywhere();
            if ($this->options_obj->hotjar->site_id) $this->hotjar_pixel->inject_everywhere();
        }

        if (is_product_category()) {

//            if ($this->google_active) $this->google_pixel_manager->inject_product_category();
            if (wga_fs()->is__premium_only()) {
                if ($this->options_obj->bing->uet_tag_id) $this->bing_pixel->inject_product_category();
                if ($this->options_obj->pinterest->pixel_id) $this->pinterest_pixel->inject_product_category();
            }

        } elseif (is_product_tag()) {
//            if ($this->google_active) $this->google_pixel_manager->inject_product_tag();
        } elseif (is_search()) {

//            if ($this->google_active) $this->google_pixel_manager->inject_search();
            if ($this->facebook_active) $this->facebook_pixel_manager->inject_search();
            if (wga_fs()->is__premium_only()) {
                if ($this->options_obj->bing->uet_tag_id) $this->bing_pixel->inject_search();
                if ($this->options_obj->twitter->pixel_id) $this->twitter_pixel->inject_search();
                if ($this->options_obj->pinterest->pixel_id) $this->pinterest_pixel->inject_search();
            }

        } elseif (is_product() && (!isset($_POST['add-to-cart']))) {

            $product    = wc_get_product();
            $product_id = $product->get_id();

            $product_attributes = [
                'brand' => $this->get_brand_name($product_id),
            ];

            if ($product->is_type('variable')) {
                // find out if attributes have been set in the URL
                // if not, continue
                // if yes get the variation id and variation SKU

                if ($this->query_string_contains_all_variation_attributes($product)) {
                    // get variation product
                    $product_id = $this->get_variation_from_query_string($product_id, $product);

                    // In case a variable product is misconfigured, wc_get_product($product_id) will not
                    // get a product but a bool. So we need to test it and only run it if
                    // we actually get a product. Basically we fall back to the parent product.
                    if (!is_bool(wc_get_product($product_id))) {
                        $product = wc_get_product($product_id);
                    }
                }
            }

//            if (is_bool($product)) {
////               error_log( 'WooCommerce detects the page ID ' . $product_id . ' as product, but when invoked by wc_get_product( ' . $product_id . ' ) it returns no product object' );
//                return;
//            }

            $product_id_compiled = $this->get_compiled_product_id($product_id, $product->get_sku(),'', $this->options);

//            if ($this->google_active) $this->google_pixel_manager->inject_product($product_id_compiled, $product, $product_attributes);
            if ($this->facebook_active) $this->facebook_pixel_manager->inject_product($product_id_compiled, $product, $product_attributes);
            if (wga_fs()->is__premium_only()) {
                if ($this->options_obj->bing->uet_tag_id) $this->bing_pixel->inject_product($product_id_compiled, $product, $product_attributes);
                if ($this->options_obj->twitter->pixel_id) $this->twitter_pixel->inject_product($product_id_compiled, $product, $product_attributes);
                if ($this->options_obj->pinterest->pixel_id) $this->pinterest_pixel->inject_product($product_id_compiled, $product, $product_attributes);
            }

        } elseif ($this->is_shop_top_page()) {
//            if ($this->google_active) $this->google_pixel_manager->inject_shop_top_page();
        } elseif (is_cart() && !empty($woocommerce->cart->get_cart())) {

            $cart       = $woocommerce->cart->get_cart();
            $cart_total = WC()->cart->get_cart_contents_total();

//            if ($this->google_active) $this->google_pixel_manager->inject_cart($cart, $cart_total);
            if ($this->facebook_active) $this->facebook_pixel_manager->inject_cart($cart, $cart_total);
            if (wga_fs()->is__premium_only()) {
                if ($this->options_obj->bing->uet_tag_id) $this->bing_pixel->inject_cart($cart, $cart_total);
                if ($this->options_obj->twitter->pixel_id) $this->twitter_pixel->inject_cart($cart, $cart_total);
                if ($this->options_obj->pinterest->pixel_id) $this->pinterest_pixel->inject_cart($cart, $cart_total);
            }

        } elseif (is_order_received_page()) {

            $this->is_nodedupe_parameter_set();

            // get order from URL and evaluate order total
            if (isset($_GET['key'])) {

                $order_key = $_GET['key'];
                $order     = new WC_Order(wc_get_order_id_by_order_key($order_key));

                $conversion_prevention = false;
                $conversion_prevention = apply_filters('wgact_conversion_prevention', $conversion_prevention, $order);

                if ($this->is_nodedupe_parameter_set() ||
                    (!$order->has_status('failed') &&
                        !current_user_can('edit_others_pages') &&
                        $conversion_prevention == false &&
                        (!$this->options['shop']['order_deduplication'] ||
                            get_post_meta($order->get_id(), '_WGACT_conversion_pixel_fired', true) != true))) {

                    $this->increase_conversion_count_for_ratings();

                    if (is_user_logged_in()) {
                        $user = get_current_user_id();
                    } else {
                        $user = $order->get_billing_email();
                    }
                    $is_new_customer = !$this->has_bought($user, $order);

                    $order_total = 0 == $this->options_obj->shop->order_total_logic ? $order->get_subtotal() - $order->get_total_discount() : $order->get_total();

                    // filter to adjust the order value
                    $order_total = apply_filters('wgact_conversion_value_filter', $order_total, $order);

                    $order_item_ids = $this->get_order_item_ids($order);

//                    if ($this->google_active) $this->google_pixel_manager->inject_order_received_page($order, $order_total, $order_item_ids, $is_new_customer);
                    if ($this->facebook_active) $this->facebook_pixel_manager->inject_order_received_page($order, $order_total, $order_item_ids);

                    if (wga_fs()->is__premium_only()) {
                        if ($this->options_obj->bing->uet_tag_id) $this->bing_pixel->inject_order_received_page($order, $order_total, $order_item_ids);
                        if ($this->options_obj->twitter->pixel_id) $this->twitter_pixel->inject_order_received_page($order, $order_total, $order_item_ids);
                        if ($this->options_obj->pinterest->pixel_id) $this->pinterest_pixel->inject_order_received_page($order, $order_total, $order_item_ids);
                    }

                    $this->inject_transaction_deduper_script($order->get_id());
                } else {
                    if (wga_fs()->is__premium_only()) {
                        $this->conversion_pixels_already_fired_html__premium_only();
                    }
                }
            }
        }

        echo PHP_EOL . '<!-- END woopt Pixel Manager -->' . PHP_EOL;

        if ((new Environment_Check())->is_autoptimize_active()) {
            $this->inject_noptimize_closing_tag();
        }
    }













    private function inject_wgact_order_deduplication_script()
    {
        ?>
        <script>
            let wgact_order_deduplication = <?php echo ($this->options['shop']['order_deduplication'] && !$this->is_nodedupe_parameter_set()) ? 'true' : 'false' ?>;
        </script>
        <?php
    }


    private function inject_body_pixels()
    {
//        $this->google_pixel_manager->inject_google_optimize_anti_flicker_snippet();
    }

    private function inject_noptimize_opening_tag()
    {
        echo PHP_EOL . '<!--noptimize-->';
    }

    private function inject_noptimize_closing_tag()
    {
        echo '<!--/noptimize-->' . PHP_EOL . PHP_EOL;
    }




//    protected function get_compiled_product_id($product_id, $product_sku): string
//    {
//        // depending on setting use product IDs or SKUs
//        if (0 == $this->options['google']['ads']['product_identifier']) {
//            return (string)$product_id;
//        } else if (1 == $this->options['google']['ads']['product_identifier']) {
//            return (string)'woocommerce_gpf_' . $product_id;
//        } else {
//            if ($product_sku) {
//                return (string)$product_sku;
//            } else {
//                return (string)$product_id;
//            }
//        }
//    }





    private function inject_data_layer_shop()
    {
        $data = [];

        if (is_product_category()) {
            $data['list_name'] = 'Product Category';
            $data['page_type'] = 'product_category';
        } elseif (is_product_tag()) {
            $data['list_name'] = 'Product Tag';
            $data['page_type'] = 'product_tag';
        } elseif (is_search()) {
            $data['list_name'] = 'Product Search';
            $data['page_type'] = 'search';
        } elseif (is_shop()) {
            $data['list_name'] = 'Shop';
            $data['page_type'] = 'product_shop';
        } elseif (is_product()) {
            $data['page_type'] = 'product';

            $product              = wc_get_product();
            $data['product_type'] = $product->get_type();
        } elseif (is_cart()) {
            $data['list_name'] = '';
            $data['page_type'] = 'cart';
        } else {
            $data['list_name'] = '';
        }

        $data['currency'] = get_woocommerce_currency();
        ?>

        <script>
            wooptpmDataLayer['shop'] = <?php echo json_encode($data) ?>;
        </script>
        <?php
    }

    private function inject_data_layer_product()
    {
        global $wp_query, $woocommerce;

        if (is_shop() || is_product_category() || is_product_tag() || is_search()) {

            $product_ids = [];
            $posts       = $wp_query->posts;
            foreach ($posts as $key => $post) {
                if ($post->post_type == 'product') {
                    array_push($product_ids, $post->ID);
                }
            }

            ?>

            <script>
                wooptpmDataLayer['visible_products'] = <?php echo json_encode($this->eec_get_visible_products($product_ids)) ?>;
            </script>
            <?php
        } elseif (is_cart()) {
            $visible_product_ids = [];
            $upsell_product_ids  = [];

            $items = $woocommerce->cart->get_cart();
            foreach ($items as $item => $values) {
                array_push($visible_product_ids, $values['data']->get_id());
                $product = wc_get_product($values['data']->get_id());

                // only continue if WC retrieves a valid product
                if (!is_bool($product)) {
                    $single_product_upsell_ids = $product->get_upsell_ids();
//                error_log(print_r($single_product_upsell_ids,true));

                    foreach ($single_product_upsell_ids as $item => $value) {
//                    error_log('item ' . $item);
//                    error_log('value' . $value);

                        if (!in_array($value, $upsell_product_ids, true)) {
                            array_push($upsell_product_ids, $value);
                        }
                    }
                }

            }

//            error_log(print_r($upsell_product_ids,true));

            ?>

            <script>
                wooptpmDataLayer['visible_products'] = <?php echo json_encode($this->eec_get_visible_products($visible_product_ids)) ?>;
                wooptpmDataLayer['upsell_products']  = <?php echo json_encode($this->eec_get_visible_products($upsell_product_ids)) ?>;
            </script>
            <?php
        } elseif (is_product()) {

            $visible_product_ids = [];

            $product = wc_get_product();
            array_push($visible_product_ids, $product->get_id());

            $related_products = wc_get_related_products($product->get_id());
            foreach ($related_products as $item => $value) {
                array_push($visible_product_ids, $value);
            }

            $upsell_product_ids = $product->get_upsell_ids();
            foreach ($upsell_product_ids as $item => $value) {
                array_push($visible_product_ids, $value);
            }
//            error_log(print_r($visible_product_ids, true));

            if ($product->get_type() === 'grouped') {
                $visible_product_ids = array_merge($visible_product_ids, $product->get_children());
            }

            ?>

            <script>
                wooptpmDataLayer['visible_products'] = <?php echo json_encode($this->eec_get_visible_products($visible_product_ids)) ?>;
            </script>
            <?php
        }
    }

    private function eec_get_visible_products($product_ids): array
    {
        $data = [];

        $position = 1;

        foreach ($product_ids as $key => $product_id) {

            $product = wc_get_product($product_id);

            // only continue if WC retrieves a valid product
            if (!is_bool($product)) {
                $data[$product->get_id()] = [
                    'id'        => (string)$product->get_id(),
                    'sku'       => (string)$product->get_sku(),
                    'name'      => (string)$product->get_name(),
                    'price'     => (int)$product->get_price(),
                    'brand'     => $this->get_brand_name($product->get_id()),
                    'category'  => (array)$this->get_product_category($product->get_id()),
                    // 'variant'  => '',
                    'quantity'  => (int)1,
                    'position'  => (int)$position,
                    'dyn_r_ids' => [
                        'post_id' => (string)$product->get_id(),
                        'sku'     => (string)$product->get_sku(),
                        'gpf'     => 'woocommerce_gpf_' . (string)$product->get_id(),
                    ]
                ];
                $position++;
            }
        }

        return $data;
    }
}