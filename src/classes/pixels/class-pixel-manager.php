<?php

namespace WGACT\Classes\Pixels;

use stdClass;
use WC_Order;
use WGACT\Classes\Admin\Environment_Check;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Pixel_Manager
{
    use Trait_Product;
    use Trait_Google;

    protected $options;
    protected $options_obj;
    protected $cart;
    protected $facebook_active;
    protected $google_active;
    protected $transaction_deduper_timeout = 2000;

    public function __construct($options)
    {
        $this->options = $options;

//        error_log(print_r($options, true));

        $this->options_obj = json_decode(json_encode($this->options));

        $this->options_obj->shop->currency = new stdClass();
        $this->options_obj->shop->currency = get_woocommerce_currency();

        $this->facebook_active = !empty($this->options_obj->facebook->pixel_id);
        $this->google_active   = $this->google_active();

        if($this->options_obj->general->maximum_compatibility_mode)(new Environment_Check())->enable_maximum_compatibility_mode();

        add_action('wp_enqueue_scripts', [$this, 'wgact_front_end_scripts']);

        if (wga_fs()->is__premium_only()) {
            add_action('wp_ajax_wgact_purchase_pixels_fired', [$this, 'ajax_purchase_pixels_fired_handler__premium_only']);
            add_action('wp_ajax_nopriv_wgact_purchase_pixels_fired', [$this, 'ajax_purchase_pixels_fired_handler__premium_only']);
        }

        add_action('wp_head', function () {
            $this->inject_head_pixels();
        });

        if (did_action('wp_body_open')) {
            add_action('wp_body_open', function () {
                $this->inject_body_pixels();
            });
        }
    }

    public function ajax_purchase_pixels_fired_handler__premium_only()
    {
        $order_id = $_POST['order_id'];
        update_post_meta($order_id, '_WGACT_conversion_pixel_fired', true);
        wp_die(); // this is required to terminate immediately and return a proper response
    }

    public function wgact_front_end_scripts()
    {
        wp_enqueue_script('front-end-scripts', plugin_dir_url(__DIR__) . '../js/public/wgact.js', [], WGACT_CURRENT_VERSION, false);
        if (wga_fs()->is__premium_only()) {
            wp_enqueue_script('front-end-scripts-premium-only', plugin_dir_url(__DIR__) . '../js/public/wgact__premium_only.js', [], WGACT_CURRENT_VERSION, false);
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

        if ($this->google_active) (new Google_Pixel_Manager($this->options, $this->options_obj))->inject_everywhere();
        if ($this->facebook_active) (new Facebook_Pixel_Manager($this->options, $this->options_obj))->inject_everywhere();

        if (wga_fs()->is__premium_only()) {
            if ($this->options_obj->bing->uet_tag_id) (new Bing($this->options, $this->options_obj))->inject_everywhere();
            if ($this->options_obj->twitter->pixel_id) (new Twitter($this->options, $this->options_obj))->inject_everywhere();
            if ($this->options_obj->pinterest->pixel_id) (new Pinterest($this->options, $this->options_obj))->inject_everywhere();
            if ($this->options_obj->hotjar->site_id) (new Hotjar($this->options, $this->options_obj))->inject_everywhere();
        }

        if (is_product_category()) {

            if ($this->google_active) (new Google_Pixel_Manager($this->options, $this->options_obj))->inject_product_category();
            if (wga_fs()->is__premium_only()) {
                if ($this->options_obj->bing->uet_tag_id) (new Bing($this->options, $this->options_obj))->inject_product_category();
                if ($this->options_obj->pinterest->pixel_id) (new Pinterest($this->options, $this->options_obj))->inject_product_category();
            }

        } elseif (is_product_tag()) {
            if ($this->google_active) (new Google_Pixel_Manager($this->options, $this->options_obj))->inject_product_tag();
        } elseif (is_search()) {

            if ($this->google_active) (new Google_Pixel_Manager($this->options, $this->options_obj))->inject_search();
            if ($this->facebook_active) (new Facebook_Pixel_Manager($this->options, $this->options_obj))->inject_search();
            if (wga_fs()->is__premium_only()) {
                if ($this->options_obj->bing->uet_tag_id) (new Bing($this->options, $this->options_obj))->inject_search();
                if ($this->options_obj->twitter->pixel_id) (new Twitter($this->options, $this->options_obj))->inject_search();
                if ($this->options_obj->pinterest->pixel_id) (new Pinterest($this->options, $this->options_obj))->inject_search();
            }

        } elseif (is_product() && (!isset($_POST['add-to-cart']))) {

            $product_id = get_the_ID();
            $product    = wc_get_product($product_id);

            if (is_bool($product)) {
//               error_log( 'WooCommerce detects the page ID ' . $product_id . ' as product, but when invoked by wc_get_product( ' . $product_id . ' ) it returns no product object' );
                return;
            }

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
                    $product = wc_get_product($product_id);
                }
            }


            $product_id_compiled = $this->get_compiled_product_id($product_id, $product->get_sku());

            if ($this->google_active) (new Google_Pixel_Manager($this->options, $this->options_obj))->inject_product($product_id_compiled, $product, $product_attributes);
            if ($this->facebook_active) (new Facebook_Pixel_Manager($this->options, $this->options_obj))->inject_product($product_id_compiled, $product, $product_attributes);
            if (wga_fs()->is__premium_only()) {
                if ($this->options_obj->bing->uet_tag_id) (new Bing($this->options, $this->options_obj))->inject_product($product_id_compiled, $product, $product_attributes);
                if ($this->options_obj->twitter->pixel_id) (new Twitter($this->options, $this->options_obj))->inject_product($product_id_compiled, $product, $product_attributes);
                if ($this->options_obj->pinterest->pixel_id) (new Pinterest($this->options, $this->options_obj))->inject_product($product_id_compiled, $product, $product_attributes);
            }

        } elseif ($this->is_shop_top_page()) {
            if ($this->google_active) (new Google_Pixel_Manager($this->options, $this->options_obj))->inject_shop_top_page();
        } elseif (is_cart() && !empty($woocommerce->cart->get_cart())) {

            $cart       = $woocommerce->cart->get_cart();
            $cart_total = WC()->cart->get_cart_contents_total();

            if ($this->google_active) (new Google_Pixel_Manager($this->options, $this->options_obj))->inject_cart($cart, $cart_total);
            if ($this->facebook_active) (new Facebook_Pixel_Manager($this->options, $this->options_obj))->inject_cart($cart, $cart_total);
            if (wga_fs()->is__premium_only()) {
                if ($this->options_obj->bing->uet_tag_id) (new Bing($this->options, $this->options_obj))->inject_cart($cart, $cart_total);
                if ($this->options_obj->twitter->pixel_id) (new Twitter($this->options, $this->options_obj))->inject_cart($cart, $cart_total);
                if ($this->options_obj->pinterest->pixel_id) (new Pinterest($this->options, $this->options_obj))->inject_cart($cart, $cart_total);
            }

        } elseif (is_order_received_page()) {

            // get order from URL and evaluate order total
            if (isset($_GET['key'])) {

                $order_key = $_GET['key'];
                $order     = new WC_Order(wc_get_order_id_by_order_key($order_key));

                $conversion_prevention = false;
                $conversion_prevention = apply_filters('wgact_conversion_prevention', $conversion_prevention, $order);

                if (!$order->has_status('failed') &&
                    !current_user_can('edit_others_pages') &&
                    $conversion_prevention == false &&
                    (!$this->options['shop']['order_deduplication'] ||
                        get_post_meta($order->get_id(), '_WGACT_conversion_pixel_fired', true) != true)) {

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

                    if ($this->google_active) (new Google_Pixel_Manager($this->options, $this->options_obj))->inject_order_received_page($order, $order_total, $order_item_ids, $is_new_customer);
                    if ($this->facebook_active) (new Facebook_Pixel_Manager($this->options, $this->options_obj))->inject_order_received_page($order, $order_total, $order_item_ids);

                    if (wga_fs()->is__premium_only()) {
                        if ($this->options_obj->bing->uet_tag_id) (new Bing($this->options, $this->options_obj))->inject_order_received_page($order, $order_total, $order_item_ids);
                        if ($this->options_obj->twitter->pixel_id) (new Twitter($this->options, $this->options_obj))->inject_order_received_page($order, $order_total, $order_item_ids);
                        if ($this->options_obj->pinterest->pixel_id) (new Pinterest($this->options, $this->options_obj))->inject_order_received_page($order, $order_total, $order_item_ids);
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

    private function get_variation_from_query_string($product_id, $product): int
    {
        parse_str($_SERVER['QUERY_STRING'], $query_string_attributes);

        $search_variation_attributes = [];

        foreach (array_keys($product->get_attributes()) as $variation_attribute => $value) {
            $search_variation_attributes['attribute_' . $value] = $query_string_attributes['attribute_' . $value];
        }

        return $this->find_matching_product_variation_id($product_id, $search_variation_attributes);
    }

    private function find_matching_product_variation_id($product_id, $attributes): int
    {
        return (new \WC_Product_Data_Store_CPT())->find_matching_product_variation(
            new \WC_Product($product_id),
            $attributes
        );
    }


    private function query_string_contains_all_variation_attributes($product): bool
    {
        if (!empty($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING'], $query_string_attributes);

            foreach (array_keys($product->get_attributes()) as $variation_attribute => $value) {
                if (!array_key_exists('attribute_' . $value, $query_string_attributes)) {
                    return false;
                }
            }
            return true;
        } else {
            return false;
        }
    }

    private function is_shop_top_page(): bool
    {
        if (
            !is_product() &&
            !is_product_category() &&
            !is_order_received_page() &&
            !is_cart() &&
            !is_search() &&
            is_shop()
        ) {
            return true;
        } else {
            return false;
        }
    }

    private function increase_conversion_count_for_ratings()
    {
        $ratings                      = get_option(WGACT_DB_RATINGS);
        $ratings['conversions_count'] = $ratings['conversions_count'] + 1;
        update_option(WGACT_DB_RATINGS, $ratings);
    }

    private function conversion_pixels_already_fired_html__premium_only()
    {
        ?>

        <!-- The conversion pixels have not been inserted. Possible reasons: -->
        <!--    You are logged into WooCommerce as admin or shop manager. -->
        <!--    The order payment has failed. -->
        <!--    The pixels have already been fired. To prevent double counting the pixels are only fired once. -->
        <?php
    }

    private function inject_transaction_deduper_script($order_id)
    {
        ?>
        <script>
            jQuery(function () {
                setTimeout(function () {
                    if (typeof wgact !== "undefined") {
                        wgact.writeOrderIdToStorage(<?php echo $order_id ?>);
                    }
                }, <?php echo $this->transaction_deduper_timeout ?>);
            });

        </script>
        <?php
    }

    private function inject_wgact_order_deduplication_script()
    {
        ?>
        <script>
            let wgact_order_deduplication = <?php echo $this->options['shop']['order_deduplication'] ? 'true' : 'false' ?>;
        </script>
        <?php
    }


    private function inject_body_pixels()
    {
//        (new Google_Pixel_Manager())->inject_google_optimize_anti_flicker_snippet();
    }

    private function inject_noptimize_opening_tag()
    {
        echo PHP_EOL . '<!--noptimize-->';
    }

    private function inject_noptimize_closing_tag()
    {
        echo '<!--/noptimize-->' . PHP_EOL . PHP_EOL;
    }

    protected function get_order_item_ids($order): array
    {
        $order_items       = $order->get_items();
        $order_items_array = [];

        foreach ((array)$order_items as $order_item) {

            $product_id = $this->get_variation_or_product_id($order_item->get_data(), $this->options_obj->general->variations_output);

            $product    = wc_get_product($product_id);

            $product_id_compiled = $this->get_compiled_product_id($product_id, $product->get_sku());

            array_push($order_items_array, $product_id_compiled);
        }

        return $order_items_array;
    }


    protected function get_compiled_product_id($product_id, $product_sku): string
    {
        // depending on setting use product IDs or SKUs
        if (0 == $this->options['google']['ads']['product_identifier']) {
            return (string)$product_id;
        } else if (1 == $this->options['google']['ads']['product_identifier']) {
            return (string)'woocommerce_gpf_' . $product_id;
        } else {
            if ($product_sku) {
                return (string)$product_sku;
            } else {
                return (string)$product_id;
            }
        }
    }

    // https://stackoverflow.com/a/46216073/4688612
    private function has_bought($value = 0, $order): bool
    {
        global $wpdb;

        // Based on user ID (registered users)
        if (is_numeric($value)) {
            $meta_key   = '_customer_user';
            $meta_value = $value == 0 ? (int)get_current_user_id() : (int)$value;
        } // Based on billing email (Guest users)
        else {
            $meta_key   = '_billing_email';
            $meta_value = sanitize_email($value);
        }

        $paid_order_statuses = array_map('esc_sql', wc_get_is_paid_statuses());

        $count = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(p.ID) FROM {$wpdb->prefix}posts AS p
        INNER JOIN {$wpdb->prefix}postmeta AS pm ON p.ID = pm.post_id
        WHERE p.post_status IN ( 'wc-" . implode("','wc-", $paid_order_statuses) . "' )
        AND p.post_type LIKE 'shop_order'
        AND p.ID <> {$order->get_id()}
        AND pm.meta_key = '%s'
        AND pm.meta_value = %s
        LIMIT 1
    ", $meta_key, $meta_value));

        // Return a boolean value based on orders count
        return $count > 0 ? true : false;
    }
}