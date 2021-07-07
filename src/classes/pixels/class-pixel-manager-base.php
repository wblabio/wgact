<?php

namespace WGACT\Classes\Pixels;


use stdClass;
use WC_Order;
use WC_Product;
use WC_Product_Data_Store_CPT;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Pixel_Manager_Base
{
    use Trait_Product;

    protected $transaction_deduper_timeout = 2000;
    protected $options;
    protected $options_obj;

    public function __construct($options)
    {
        /*
         * Initialize options
         */
//        $this->options = get_option(WGACT_DB_OPTIONS_NAME);
        $this->options = $options;

        $this->options_obj = json_decode(json_encode($this->options));

        $this->options_obj->shop->currency = new stdClass();
        $this->options_obj->shop->currency = get_woocommerce_currency();

        /*
         * Inject pixel snippets in head
         */
        add_action('wp_head', function () {
            $this->inject_head_pixels();
        });
    }

    public function inject_head_pixels()
    {
        global $woocommerce;

        $this->inject_opening_script_tag();

        $this->inject_everywhere();

        if (is_product_category()) {
            $this->inject_product_category();
        } elseif (is_product_tag()) {
            $this->inject_product_tag();
        } elseif (is_search()) {
            $this->inject_search();
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
                    if (is_object(wc_get_product($product_id))) {
                        $product = wc_get_product($product_id);
                    }
                }
            }

            if (is_object($product)) {

                $product_attributes['product_id_compiled'] = $this->get_compiled_product_id($product_id, $product->get_sku(), $this->options, '');
                $product_attributes['dyn_r_ids']           = $this->get_dyn_r_ids($product);
                $this->inject_product($product, $product_attributes);
            } else {

                $this->log_problematic_product_id($product_id);
            }

        } elseif ($this->is_shop_top_page()) {
            $this->inject_shop_top_page();
        } elseif (is_cart() && !empty($woocommerce->cart->get_cart())) {

            $cart       = $woocommerce->cart->get_cart();
            $cart_total = WC()->cart->get_cart_contents_total();

            $this->inject_cart($cart, $cart_total);
        } elseif (is_order_received_page()) {

//            $this->is_nodedupe_parameter_set();

            // get order from URL and evaluate order total
            if ($this->get_order_from_order_received_page()) {

                $order = $this->get_order_from_order_received_page();

                if ($this->can_order_confirmation_be_processed($order)) {

                    if (is_user_logged_in()) {
                        $user = get_current_user_id();
                    } else {
                        $user = $order->get_billing_email();
                    }
                    $is_new_customer = !$this->has_bought($order, $user);

                    $order_total = 0 == $this->options_obj->shop->order_total_logic ? $order->get_subtotal() - $order->get_total_discount() : $order->get_total();

                    // filter to adjust the order value
                    $order_total = apply_filters_deprecated('wgact_conversion_value_filter', [$order_total, $order], '1.10.2', 'wooptpm_conversion_value_filter');
                    $order_total = apply_filters('wooptpm_conversion_value_filter', $order_total, $order);

                    $this->inject_order_received_page($order, $order_total, $is_new_customer);
                }
            }
        }

        $this->inject_closing_script_tag();
    }

    // https://stackoverflow.com/a/49616130/4688612
    protected function get_order_from_order_received_page()
    {
        global $wp;

        $order_id = absint($wp->query_vars['order-received']);

        $order = new WC_Order($order_id);

        if (empty($order_id) || $order_id == 0) {
            return false;
        } else {
            return $order;
        }
    }

    protected function can_order_confirmation_be_processed($order): bool
    {
        $conversion_prevention = false;
        $conversion_prevention = apply_filters_deprecated('wgact_conversion_prevention', [$conversion_prevention, $order], '1.10.2', 'wooptpm_conversion_prevention');
        $conversion_prevention = apply_filters('wooptpm_conversion_prevention', $conversion_prevention, $order);

//        error_log('conversion_prevention: ' . $conversion_prevention);
//        error_log('$this->is_nodedupe_parameter_set(): ' . $this->is_nodedupe_parameter_set());
//        error_log('$order->has_status(\'failed\'): ' . $order->has_status('failed'));
//        error_log('current_user_can(\'edit_others_pages\'): ' . current_user_can('edit_others_pages'));
//        error_log("this->options['shop']['order_deduplication']: " . $this->options['shop']['order_deduplication']);
//        error_log('order id: ' . $order->get_id());
//        error_log('order number: ' . $order->get_order_number());
//        error_log('get_post_meta($order->get_order_number(), \'_wooptpm_conversion_pixel_fired\', true): ' . get_post_meta($order->get_order_number(), '_wooptpm_conversion_pixel_fired', true));


        if (
            $this->is_nodedupe_parameter_set() ||
            (!$order->has_status('failed') &&
                !current_user_can('edit_others_pages') &&
                $conversion_prevention == false &&
                (
                    !$this->options['shop']['order_deduplication'] ||
                    get_post_meta($order->get_id(), '_wooptpm_conversion_pixel_fired', true) != true
                )
            )
        ) {
//            error_log('fire pixels: true' . PHP_EOL);
            return true;
        } else {
//            error_log('fire pixels: false' . PHP_EOL);
            return false;
        }
    }

    public function inject_everywhere()
    {

    }

    public function inject_product_category()
    {

    }

    public function inject_product_tag()
    {

    }

    public function inject_search()
    {

    }

    public function inject_product($product, $product_attributes)
    {

    }

    public function inject_shop_top_page()
    {

    }

    public function inject_cart($cart, $cart_total)
    {

    }

    public function inject_order_received_page($order, $order_total, $is_new_customer)
    {

    }

    protected function is_nodedupe_parameter_set(): bool
    {
        if (isset($_GET["nodedupe"])) {
            return true;
        } else {
            return false;
        }
    }

    // https://stackoverflow.com/a/46216073/4688612
    protected function has_bought($order, $value = 0): bool
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


    protected function query_string_contains_all_variation_attributes($product): bool
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

    protected function get_variation_from_query_string($product_id, $product): int
    {
        parse_str($_SERVER['QUERY_STRING'], $query_string_attributes);

        $search_variation_attributes = [];

        foreach (array_keys($product->get_attributes()) as $variation_attribute => $value) {
            $search_variation_attributes['attribute_' . $value] = $query_string_attributes['attribute_' . $value];
        }

        return $this->find_matching_product_variation_id($product_id, $search_variation_attributes);
    }

    protected function find_matching_product_variation_id($product_id, $attributes): int
    {
        return (new WC_Product_Data_Store_CPT())->find_matching_product_variation(
            new WC_Product($product_id),
            $attributes
        );
    }

    protected function conversion_pixels_already_fired_html()
    {
        ?>

        <!-- The conversion pixels have not been inserted. Possible reasons:
                You are logged into WooCommerce as admin or shop manager.
                The order payment has failed.
                The pixels have already been fired. To prevent double counting the pixels are only fired once.
                If you want to test the order you have two options:
                    - Turn off order deduplication in the advanced settings
                    - Add the '&nodedupe' parameter to the order confirmation URL like this: https://example.test/checkout/order-received/123/?key=wc_order_123abc&nodedupe
                More info on testing: https://docs.woopt.com/wgact/#/test-order
         -->
        <?php
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

    protected function inject_opening_script_tag()
    {
        echo '   <script>';
    }

    protected function inject_closing_script_tag()
    {
        echo '   </script>';
    }
}