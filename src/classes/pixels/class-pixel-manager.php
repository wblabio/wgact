<?php

namespace WGACT\Classes\Pixels;

use stdClass;
use WC_Order;
use WGACT\Classes\Admin\Environment_Check;
use WGACT\Classes\Pixels\Bing\Bing_Pixel_Manager;
use WGACT\Classes\Pixels\Facebook\Facebook_Pixel_Manager;
use WGACT\Classes\Pixels\Facebook\Facebook_Pixel_Manager_Microdata;
use WGACT\Classes\Pixels\Google\Google_Pixel_Manager;
use WGACT\Classes\Pixels\Google\Trait_Google;
use WGACT\Classes\Pixels\Hotjar\Hotjar_Pixel;
use WGACT\Classes\Pixels\Pinterest\Pinterest_Pixel_Manager;
use WGACT\Classes\Pixels\Twitter\Twitter_Pixel_Manager;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Pixel_Manager extends Pixel_Manager_Base
{
    use Trait_Product;
    use Trait_Google;

    protected $options;
    protected $options_obj;
    protected $cart;
    protected $facebook_active;
    protected $google_active;
    protected $transaction_deduper_timeout = 2000;
    protected $hotjar_pixel;
    protected $dyn_r_ids;

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

            $this->inject_data_layer_init();
            $this->inject_data_layer_shop();
            $this->inject_data_layer_product();
        });

        /*
         * Initialize all pixels
         */
        if ($this->google_active) new Google_Pixel_Manager();
        if ($this->facebook_active) new Facebook_Pixel_Manager();
        if ($this->options_obj->hotjar->site_id) $this->hotjar_pixel = new Hotjar_Pixel();

        if (wga_fs()->is__premium_only()) {
            if ($this->options_obj->facebook->microdata) new Facebook_Pixel_Manager_Microdata();
            if ($this->options_obj->bing->uet_tag_id) new Bing_Pixel_Manager();
            if ($this->options_obj->twitter->pixel_id) new Twitter_Pixel_Manager();
            if ($this->options_obj->pinterest->pixel_id) new Pinterest_Pixel_Manager();
        }

        add_action('wp_head', function () {
            $this->inject_woopt_closing();
        });

        /*
         * Front-end script section
         */
        add_action('wp_enqueue_scripts', [$this, 'wooptpm_front_end_scripts']);

        add_action('wp_ajax_wooptpm_get_cart_items', [$this, 'ajax_wooptpm_get_cart_items']);
        add_action('wp_ajax_nopriv_wooptpm_get_cart_items', [$this, 'ajax_wooptpm_get_cart_items']);

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
            window.wooptpmDataLayer                    = window.wooptpmDataLayer || [];
            window.wooptpmDataLayer.cart               = window.wooptpmDataLayer.cart || {};
            window.wooptpmDataLayer.orderDeduplication = <?php echo ($this->options['shop']['order_deduplication'] && !$this->is_nodedupe_parameter_set()) ? 'true' : 'false' ?>;
        </script>

        <?php
    }


    public function inject_woopt_opening()
    {
        if ((new Environment_Check())->is_autoptimize_active()) {
            $this->inject_noptimize_opening_tag();
        }

        echo PHP_EOL . '<!-- START woopt Pixel Manager -->' . PHP_EOL;
    }

    public function inject_woopt_closing()
    {
        if ($this->options_obj->hotjar->site_id) $this->hotjar_pixel->inject_everywhere();

        if (is_order_received_page()) {
            if (isset($_GET['key'])) {

                $order_key = $_GET['key'];
                $order     = new WC_Order(wc_get_order_id_by_order_key($order_key));
                $this->inject_transaction_deduper_script($order->get_id());
            }
        }

        $this->increase_conversion_count_for_ratings();

        echo PHP_EOL . '<!-- END woopt Pixel Manager -->' . PHP_EOL;

        if ((new Environment_Check())->is_autoptimize_active()) {
            $this->inject_noptimize_closing_tag();
        }
    }

    private function increase_conversion_count_for_ratings()
    {
        if (isset($_GET['key'])) {

            $order_key = $_GET['key'];
            $order     = new WC_Order(wc_get_order_id_by_order_key($order_key));

            if ($this->can_order_confirmation_be_processed($order)) {
                $ratings                      = get_option(WGACT_DB_RATINGS);
                $ratings['conversions_count'] = $ratings['conversions_count'] + 1;
                update_option(WGACT_DB_RATINGS, $ratings);


            } else {
                $this->conversion_pixels_already_fired_html__premium_only();
            }
        }
    }

    public function ajax_wooptpm_get_cart_items()
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
        wp_localize_script('front-end-scripts', 'ajax_object', ['ajax_url' => admin_url('admin-ajax.php')]);

        if (wga_fs()->is__premium_only()) {
            wp_enqueue_script('front-end-scripts-premium-only', plugin_dir_url(__DIR__) . '../js/public/wooptpm__premium_only.js', [], WGACT_CURRENT_VERSION, false);
            wp_localize_script('front-end-scripts-premium-only', 'ajax_object', ['ajax_url' => admin_url('admin-ajax.php')]);
        }
    }

    public function inject_order_received_page($order, $order_total, $is_new_customer)
    {

    }

    private function inject_body_pixels()
    {
//        $this->google_pixel_manager->inject_google_optimize_anti_flicker_snippet();
    }

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
            wooptpmDataLayer.shop = <?php echo json_encode($data) ?>;
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
                if ($post->post_type == 'product' || $post->post_type == 'product_variation') {
                    array_push($product_ids, $post->ID);
                }
            }

            ?>

            <script>
                wooptpmDataLayer.visible_products = <?php echo json_encode($this->eec_get_visible_products($product_ids)) ?>;
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
                wooptpmDataLayer.visible_products = <?php echo json_encode($this->eec_get_visible_products($visible_product_ids)) ?>;
                wooptpmDataLayer.upsell_products  = <?php echo json_encode($this->eec_get_visible_products($upsell_product_ids)) ?>;
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
                wooptpmDataLayer.visible_products = <?php echo json_encode($this->eec_get_visible_products($visible_product_ids)) ?>;
            </script>
            <?php
        }
    }

    private function eec_get_visible_products($product_ids): array
    {
//        error_log(print_r($product_ids, true));
        $data = [];

        $position = 1;

        foreach ($product_ids as $key => $product_id) {

            $product = wc_get_product($product_id);

            // only continue if WC retrieves a valid product
            if (!is_bool($product)) {

                $this->dyn_r_ids = $this->get_dyn_r_ids($product);

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
                    'dyn_r_ids' => $this->dyn_r_ids,
                ];
                $position++;
            }
        }

        return $data;
    }


    protected function inject_transaction_deduper_script($order_id)
    {
        ?>

        <script>
            jQuery(function () {
                setTimeout(function () {
                    if (typeof wooptpm !== "undefined") {
                        wooptpm.writeOrderIdToStorage(<?php echo $order_id ?>);
                    }
                }, <?php echo $this->transaction_deduper_timeout ?>);
            });
        </script>
        <?php
    }

    private function inject_noptimize_opening_tag()
    {
        echo PHP_EOL . '<!--noptimize-->';
    }

    private function inject_noptimize_closing_tag()
    {
        echo '<!--/noptimize-->' . PHP_EOL . PHP_EOL;
    }
}