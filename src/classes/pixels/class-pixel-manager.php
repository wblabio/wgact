<?php

namespace WGACT\Classes\Pixels;

use stdClass;
use WC_Order;
use WC_Product_Variation;
use WGACT\Classes\Admin\Environment_Check;
use WGACT\Classes\Pixels\Bing\Bing_Pixel_Manager;
use WGACT\Classes\Pixels\Facebook\Facebook_Pixel_Manager;
use WGACT\Classes\Pixels\Facebook\Facebook_Pixel_Manager_Microdata;
use WGACT\Classes\Pixels\Google\Google_Analytics_4_Refund;
use WGACT\Classes\Pixels\Google\Google_Analytics_Refund;
use WGACT\Classes\Pixels\Google\Google_Analytics_UA_Refund;
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
    use Trait_Shop;

    protected $options;
    protected $options_obj;
    protected $cart;
    protected $facebook_active;
    protected $google_active;
    protected $transaction_deduper_timeout = 2000;
    protected $hotjar_pixel;
    protected $dyn_r_ids;
    protected $position = 1;

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
         * Inject pixel snippets in head
         */
//        add_action('wp_head', function () {
//            $this->inject_head_pixels();
//        });

        add_action('wp_head', function () {
            $this->inject_woopt_opening();

            $this->inject_data_layer_init();
            $this->inject_data_layer_shop();
            $this->inject_data_layer_general();
//            $this->inject_data_layer_product();
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
         * Inject pixel snippets into wp_footer
         */
        add_action('wp_footer', [$this, 'woopt_wp_footer']);


        /*
         * Process short codes
         */
        new Shortcodes($this->options, $this->options_obj);

        add_action('woocommerce_after_shop_loop_item', [$this, 'action_woocommerce_after_shop_loop_item'], 10, 1);
        add_filter('woocommerce_blocks_product_grid_item_html', [$this, 'wc_add_date_to_gutenberg_block'], 10, 3);
        add_filter('woocommerce_after_add_to_cart_form', [$this, 'woocommerce_after_add_to_cart_form']);

    }

    // on product page
    public function woocommerce_after_add_to_cart_form()
    {
        global $product;

        echo $this->get_product_data_layer_script($product, false);

        if ($product->is_type('grouped')) {

            foreach ($product->get_children() as $product_id) {
                $product = wc_get_product($product_id);
                echo $this->get_product_data_layer_script($product, false);
            }
        }

        if ($product->is_type('variable')) {
            foreach ($product->get_available_variations() as $key => $variation) {

                $variable_product = wc_get_product($variation['variation_id']);

                if (!is_bool($variable_product)) {
                    echo $this->get_product_data_layer_script($variable_product, false);
                }
            }
        }
    }

    // every product that's generated by the shop loop like shop page or a shortcode
    public function action_woocommerce_after_shop_loop_item()
    {
        global $product;

        echo $this->get_product_data_layer_script($product);
    }

    // product views generated by a gutenberg block instead of a shortcode
    function wc_add_date_to_gutenberg_block($html, $data, $product): string
    {
        return $html . $this->get_product_data_layer_script($product);
    }

    private function get_product_data_layer_script($product, $set_position = true): string
    {
        global $woocommerce_wpml;

        $this->dyn_r_ids = $this->get_dyn_r_ids($product);

        if ((new Environment_Check())->is_wpml_woocommerce_multi_currency_active()) {
            $price = $woocommerce_wpml->multi_currency->prices->get_product_price_in_currency($product->get_id(), get_woocommerce_currency());
        } else {
            $price = $product->get_price();
        }

        $data = [
            'id'          => (string)$product->get_id(),
            'sku'         => (string)$product->get_sku(),
            'name'        => (string)$product->get_name(),
            'price'       => (int)$price,
            'brand'       => $this->get_brand_name($product->get_id()),
            'category'    => (array)$this->get_product_category($product->get_id()),
            'quantity'    => (int)1,
            'dyn_r_ids'   => $this->dyn_r_ids,
            'isVariation' => false,
        ];

        if ($product->get_type() == 'variation') {

            $parent_product      = wc_get_product($product->get_parent_id());
            $data['name']        = $parent_product->get_name();
            $data['isVariation'] = true;
            $data['parentId']    = $parent_product->get_id();
            $data['variant']     = $this->get_formatted_variant_text($product);
        }

        $html = "
            <input type='hidden' class='wooptpmProductId' data-id='" . $product->get_id() . "'>
            <script>
                window.wooptpmDataLayer.products = window.wooptpmDataLayer.products || {};
                window.wooptpmDataLayer.products[" . $product->get_id() . "] = " . json_encode($data) . ";";

        if ($set_position === true) {

            $html .= "
                window.wooptpmDataLayer.products[{$product->get_id()}]['position'] = window.wooptpmDataLayer.position++;";
        }

        $html .= "</script>";

        return $html;
    }

    public function woopt_wp_footer()
    {
        if (wga_fs()->is__premium_only() && $this->options_obj->google->analytics->eec) {
            (new Google_Analytics_Refund())->process_refund_to_frontend__premium_only();
        }
    }

    private function inject_data_layer_init()
    {
        $data = [
            'cart'                => [],
            'cart_item_keys'      => [],
            'pixels'              => [],
            'orderDeduplication'  => ($this->options['shop']['order_deduplication'] && !$this->is_nodedupe_parameter_set()) ? true : false,
            'position'            => (int)1,
            'viewItemListTrigger' => (array)$this->view_item_list_trigger_settings(),
            'version'             => (string)WGACT_CURRENT_VERSION,
        ];

        ?>

        <script>

            function wooptpmExists() {
                return new Promise(function (resolve, reject) {
                    (function waitForWooptpm() {
                        if (window.wooptpm) return resolve();
                        setTimeout(waitForWooptpm, 30);
                    })();
                });
            }

            window.wooptpmDataLayer = window.wooptpmDataLayer || {};
            window.wooptpmDataLayer = <?php echo json_encode($data, JSON_FORCE_OBJECT) ?>;

        </script>

        <?php
    }

    public function view_item_list_trigger_settings(): array
    {
        $settings = [
            'testMode'        => false,
            'backgroundColor' => 'green',
            'opacity'         => 0.5,
            'repeat'          => true,
            'timeout'         => 1000,
            'threshold'       => 0.8,
        ];

        return apply_filters('wooptpm_view_item_list_trigger_settings', $settings);
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
            if ($this->get_order_from_order_received_page()) {

                $order = new WC_Order($this->get_order_from_order_received_page());
                $this->inject_transaction_deduper_script($order->get_order_number());

                $this->increase_conversion_count_for_ratings($order);
            }
        }

        echo PHP_EOL . '<!-- END woopt Pixel Manager -->' . PHP_EOL;

        if ((new Environment_Check())->is_autoptimize_active()) {
            $this->inject_noptimize_closing_tag();
        }
    }

    private function increase_conversion_count_for_ratings($order)
    {
        if ($this->can_order_confirmation_be_processed($order)) {

            $ratings                      = get_option(WGACT_DB_RATINGS);
            $ratings['conversions_count'] = $ratings['conversions_count'] + 1;
            update_option(WGACT_DB_RATINGS, $ratings);
        } else {
            $this->conversion_pixels_already_fired_html();
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
                'id'          => (string)$product->get_id(),
                'isVariation' => false,
            ];

            $data['cart'][$product->get_id()] = [
                'id'          => (string)$product->get_id(),
                'dyn_r_ids'   => (array)$this->get_dyn_r_ids($product),
                'name'        => (string)$product->get_name(),
                //                'list_name'     => '',
                'brand'       => (string)$this->get_brand_name($product->get_id()),
                //                'variant'       => '',
                //                'list_position' => '',
                'quantity'    => (int)$value['quantity'],
                'price'       => (float)$product->get_price(),
                'isVariation' => false,
            ];

            if ($product->get_type() == 'variation') {

                $parent_product                                  = wc_get_product($product->get_parent_id());
                $data['cart'][$product->get_id()]['name']        = (string)$parent_product->get_name();
                $data['cart'][$product->get_id()]['isVariation'] = true;
                $data['cart'][$product->get_id()]['parentId']    = (string)$parent_product->get_id();
                $data['cart'][$product->get_id()]['category']    = (array)$this->get_product_category($product->get_parent_id());

                $variant_text_array = [];

                $attributes = $product->get_attributes();
                if ($attributes) {
                    foreach ($attributes as $key => $value) {

                        $key_name             = str_replace('pa_', '', $key);
                        $variant_text_array[] = ucfirst($key_name) . ': ' . strtolower($value);
                    }
                }

                $data['cart'][$product->get_id()]['variant'] = (string)implode(' | ', $variant_text_array);

                $data['cart_item_keys'][$cart_item]['parentId']    = (string)$product->get_parent_id();
                $data['cart_item_keys'][$cart_item]['isVariation'] = true;

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
        wp_enqueue_script('wooptpm-front-end-scripts', plugin_dir_url(__DIR__) . '../js/public/wooptpm.js', [], WGACT_CURRENT_VERSION, false);
        wp_localize_script('wooptpm-front-end-scripts', 'ajax_object', ['ajax_url' => admin_url('admin-ajax.php')]);

        if (wga_fs()->is__premium_only()) {
            wp_enqueue_script('wooptpm-front-end-scripts-premium-only', plugin_dir_url(__DIR__) . '../js/public/wooptpm__premium_only.js', [], WGACT_CURRENT_VERSION, false);
            wp_localize_script('wooptpm-front-end-scripts-premium-only', 'ajax_object', ['ajax_url' => admin_url('admin-ajax.php')]);
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
            $data['list_name'] = 'Product Category' . $this->get_list_name_suffix();
            $data['list_id']   = 'product_category' . $this->get_list_id_suffix();
            $data['page_type'] = 'product_category';
        } elseif (is_product_tag()) {
            $data['list_name'] = 'Product Tag' . $this->get_list_name_suffix();
            $data['list_id']   = 'product_tag' . $this->get_list_id_suffix();
            $data['page_type'] = 'product_tag';
        } elseif (is_search()) {
            $data['list_name'] = 'Product Search';
            $data['list_id']   = 'search';
            $data['page_type'] = 'search';
        } elseif (is_shop()) {
            $data['list_name'] = 'Shop';
            $data['list_id']   = 'product_shop';
            $data['page_type'] = 'product_shop';
        } elseif (is_product()) {
            $data['list_name'] = 'Product';
            $data['list_id']   = 'product';
            $data['page_type'] = 'product';

            $product              = wc_get_product();
            $data['product_type'] = $product->get_type();
        } elseif (is_cart()) {
            $data['list_name'] = 'Cart';
            $data['list_id']   = 'cart';
            $data['page_type'] = 'cart';
        } else if (is_front_page()) {
            $data['list_name'] = 'Front Page';
            $data['list_id']   = 'front_page';
            $data['page_type'] = 'front_page';
        } else {
            $data['list_name'] = '';
            $data['list_id']   = '';
            $data['page_type'] = '';
        }

        $data['currency'] = get_woocommerce_currency();
        ?>

        <script>
            wooptpmDataLayer.shop = <?php echo json_encode($data) ?>;
        </script>
        <?php
    }

    private function inject_data_layer_general()
    {
        $data = [
            'variationsOutput' => $this->options_obj->general->variations_output ? true : false,
        ];

        ?>

        <script>
            wooptpmDataLayer.general = <?php echo json_encode($data) ?>;
        </script>
        <?php
    }

    private function inject_data_layer_product()
    {
        global $wp_query, $woocommerce;

        if (is_shop() || is_product_category() || is_product_tag() || is_search()) {
//        if (!is_cart() || !is_order_received_page()) {


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
                    wooptpmExists().then(function () {
                        wooptpm.writeOrderIdToStorage(<?php echo $order_id ?>);
                    });
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