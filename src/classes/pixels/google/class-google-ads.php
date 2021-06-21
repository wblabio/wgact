<?php

namespace WGACT\Classes\Pixels\Google;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google_Ads extends Google
{
    use Trait_Google;

    protected $google_business_vertical;

    public function __construct($options)
    {
        parent::__construct($options);

        add_action('wp_enqueue_scripts', [$this, 'wooptpm_google_ads_front_end_scripts']);

        $this->pixel_name = 'google_ads';
    }

    public function wooptpm_google_ads_front_end_scripts()
    {
//        wp_enqueue_script('wooptpm-google-ads', plugin_dir_url(__DIR__) . '../../js/public/google-ads.js', ['jquery', 'wooptpm'], WGACT_CURRENT_VERSION, true);
        wp_enqueue_script('wooptpm-google-ads', WGACT_PLUGIN_DIR_PATH . 'js/public/google-ads.js', ['jquery', 'wooptpm'], WGACT_CURRENT_VERSION, true);
    }

    public function inject_product_list($list_name)
    {
        // handled on front-end
    }

    public function inject_product($product, $product_attributes)
    {
        // handled on front-end
    }

    public function inject_cart($cart, $cart_total)
    {
        // handled on front-end
    }

    public function inject_order_received_page($order, $order_total, $is_new_customer)
    {
        // If Google Ads Enhanced Conversions is active
        if($this->options_obj->google->ads->enhanced_conversions){

        $customer_data = [];

            if ($order->get_billing_email()) $customer_data['email'] = (string)$order->get_billing_email();
            if ($order->get_billing_phone()) $customer_data['phone_number'] = (string)$order->get_billing_phone();
            if ($order->get_billing_first_name()) $customer_data['first_name'] = (string)$order->get_billing_first_name();
            if ($order->get_billing_last_name()) $customer_data['last_name'] = (string)$order->get_billing_last_name();
            if ($order->get_billing_address_1()) $customer_data['home_address']['street'] = (string)$order->get_billing_address_1();
            if ($order->get_billing_city()) $customer_data['home_address']['city'] = (string)$order->get_billing_city();
            if ($order->get_billing_state()) $customer_data['home_address']['region'] = (string)$order->get_billing_state();
            if ($order->get_billing_postcode()) $customer_data['home_address']['postal_code'] = (string)$order->get_billing_postcode();
            if ($order->get_billing_country()) $customer_data['home_address']['country'] = (string)$order->get_billing_country();

            ?>

            let enhanced_conversion_data = <?php echo json_encode($customer_data) ?>;
            <?php
        }

        $order_currency = $this->get_order_currency($order);

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
            if ($this->is_dynamic_remarketing_active())  $this->get_dyn_remarketing_purchase_script($order, $order_total) ?>

            <?php
        }

        if ($this->add_cart_data == true && $this->conversion_id && $this->conversion_label) {
            ?>

            gtag('event', 'purchase', <?php echo $this->get_event_purchase_json($order, $order_total, $order_currency, $is_new_customer, 'ads') ?>);

            <?php
            if ($this->is_dynamic_remarketing_active())  $this->get_dyn_remarketing_purchase_script($order, $order_total);
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
                if (is_object($product)) {

                    $dyn_r_ids = $this->get_dyn_r_ids($product);

                    $item_details['id']                       = $dyn_r_ids[$this->get_dyn_r_id_type()];
                    $item_details['google_business_vertical'] = $this->google_business_vertical;

                    array_push($items, $item_details);
                } else {

                    $this->log_problematic_product_id($post->ID);
                }
            }
        }

        return $items;
    }

    protected function get_gads_formatted_product_details_from_product_id($product_id): array
    {
        $product = wc_get_product($product_id);

        if (is_object($product)) {

            $dyn_r_ids = $this->get_dyn_r_ids($product);

            $product_details['id']       = $dyn_r_ids[$this->get_dyn_r_id_type()];
            $product_details['category'] = $this->get_product_category($product_id);
            // $product_details['list_position'] = 1;
            $product_details['quantity']                 = 1;
            $product_details['price']                    = (float)$product->get_price();
            $product_details['google_business_vertical'] = $this->google_business_vertical;

            return $product_details;
        } else {

            $this->log_problematic_product_id($product_id);
        }
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

            if (is_object($product)) {

                $dyn_r_ids = $this->get_dyn_r_ids($product);

                $item_details['id']                       = $dyn_r_ids[$this->get_dyn_r_id_type()];
                $item_details['quantity']                 = (int)$cart_item['quantity'];
                $item_details['price']                    = (int)$product->get_price();
                $item_details['google_business_vertical'] = $this->google_business_vertical;

                array_push($cart_items, $item_details);
            } else {

                $this->log_problematic_product_id($product_id);
            }
        }

        // apply filter to the $cartprods_items array
        $cart_items = apply_filters_deprecated('wgact_filter', [$cart_items], '1.10.2', '', 'This filter has been deprecated without replacement.');

        return $cart_items;
    }

    protected function get_dyn_remarketing_purchase_script($order, $order_total)
    {
        echo "
            wooptpmExists().then(function(){
                if (!wooptpm.isOrderIdStored('" . $order->get_id() . "')) {
                    gtag('event', 'purchase', {
                        'send_to': " . json_encode($this->get_google_ads_conversion_ids()) . ",
                        'value'  : " . $order_total . ",
                        'items'  : " . (json_encode($this->get_formatted_order_items($order, 'ads'))) . "
                    });
                }
            });
        ";
    }
}