<?php

namespace WGACT\Classes\Pixels;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google extends Pixel
{
    protected $google_business_vertical;

    public function __construct($options, $options_obj)
    {
        parent::__construct($options, $options_obj);

        $this->google_business_vertical = $this->get_google_business_vertical($this->options['google']['ads']['google_business_vertical']);
    }

    public function inject_everywhere()
    {
        if ($this->options_obj->google->optimize->container_id) {
            ?>
            <script async
                    src="https://www.googleoptimize.com/optimize.js?id=<?php _e($this->options_obj->google->optimize->container_id) ?>"></script>
            <?php
        }

        if (!$this->options_obj->google->gtag->deactivation) {
            ?>

            <!-- Global site tag (gtag.js) - Google Ads: <?php _e($this->conversion_id) ?> -->
            <script async
                    src="https://www.googletagmanager.com/gtag/js?id=<?php _e($this->get_gtag_id()) ?>"></script>
            <script>
                window.dataLayer = window.dataLayer || [];

                function gtag() {
                    dataLayer.push(arguments);
                }

                gtag('js', new Date());

            </script>

            <?php
        }

        ?>

        <script>
            <?php $this->options_obj->google->ads->conversion_id ? _e($this->gtag_config($this->options_obj->google->ads->conversion_id, 'ads')) : _e(PHP_EOL); ?>
            <?php $this->options_obj->google->analytics->universal->property_id ? _e($this->gtag_config($this->options_obj->google->analytics->universal->property_id, 'analytics')) : _e(PHP_EOL); ?>
            <?php $this->options_obj->google->analytics->ga4->measurement_id ? _e($this->gtag_config($this->options_obj->google->analytics->ga4->measurement_id, 'analytics')) : _e(PHP_EOL); ?>
        </script>
        <?php
    }


    private function get_gtag_id(): string
    {
        if ($this->options_obj->google->analytics->universal->property_id) {
            return $this->options_obj->google->analytics->universal->property_id;
        } elseif ($this->options_obj->google->analytics->ga4->measurement_id) {
            return $this->options_obj->google->analytics->ga4->measurement_id;
        } elseif ($this->options_obj->google->ads->conversion_id) {
            return 'AW-' . $this->options_obj->google->ads->conversion_id;
        }
    }

    public function inject_google_optimize_anti_flicker_snippet()
    {
        ?>

        <script>(function (a, s, y, n, c, h, i, d, e) {
                s.className += ' ' + y;
                h.start                  = 1 * new Date;
                h.end                    = i = function () {
                    s.className = s.className.replace(RegExp(' ?' + y), '')
                };
                (a[n] = a[n] || []).hide = h;
                setTimeout(function () {
                    i();
                    h.end = null
                }, c);
                h.timeout = c;
            })(window, document.documentElement, 'async-hide', 'dataLayer', 4000,
                {'<?php _e($this->options_obj->google->optimize->container_id) ?>': true});</script>
        <?php
    }


    public function inject_product_category()
    {
        global $wp_query;

        if ($this->is_dynamic_remarketing_active()) {

            ?>

            <script type="text/javascript">
                gtag('event', 'view_item_list', {
                    'send_to': 'AW-<?php _e($this->conversion_id) ?>',
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
                        'send_to': 'AW-<?php _e($this->conversion_id) ?>',
                        'items'  : <?php echo json_encode($this->get_products_from_wp_query($wp_query)) . PHP_EOL ?>
                    });
            </script>
            <?php
        }
    }

    public function inject_product($product_id, $product)
    {
        if ($this->is_dynamic_remarketing_active()) {

            $product_details = $this->get_gads_formatted_product_details_from_product_id($product_id);
            ?>

            <script type="text/javascript">
                gtag('event', 'view_item', {
                    'send_to': 'AW-<?php _e($this->conversion_id) ?>',
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
                    'send_to': 'AW-<?php _e($this->conversion_id) ?>',
                    'value'  : <?php echo $cart_total ?>,
                    'items'  : <?php echo (json_encode($this->get_gads_formatted_cart_items($cart))) . PHP_EOL ?>
                });
            </script>
            <?php
        }
    }

    private function is_dynamic_remarketing_active(): bool
    {
        if($this->dynamic_remarketing && $this->options_obj->google->ads->conversion_id){
            return true;
        } else {
            return false;
        }
    }

    public function inject_order_received_page($order, $order_total)
    {
        // use the right function to get the currency depending on the WooCommerce version
        $order_currency = $this->woocommerce_3_and_above() ? $order->get_currency() : $order->get_order_currency();

        // filter to adjust the order value
        $order_total_filtered = apply_filters('wgact_conversion_value_filter', $order_total, $order);

        $ratings                      = get_option(WGACT_DB_RATINGS);
        $ratings['conversions_count'] = $ratings['conversions_count'] + 1;
        update_option(WGACT_DB_RATINGS, $ratings);

        ?>

        <!-- Google Code for Sales Conversion Page -->
        <?php

        // Only run conversion script if the payment has not failed. (has_status('completed') is too restrictive)
        // Also don't run the pixel if an admin or shop manager is logged in.
        if (!$order->has_status('failed') && !current_user_can('edit_others_pages') && ($this->add_cart_data == 0) && $this->options_obj->google->ads->conversion_id) {
//           if ( ! $order->has_status( 'failed' ) ) {
            ?>

            <?php if ($this->options_obj->google->ads->conversion_label ): ?>

            <script>
                gtag('event', 'conversion', {
                    'send_to'       : 'AW-<?php _e($this->conversion_id) ?>/<?php _e($this->conversion_label) ?>',
                    'value'         : <?php echo $order_total_filtered; ?>,
                    'currency'      : '<?php echo $order_currency; ?>',
                    'transaction_id': '<?php echo $order->get_order_number(); ?>',
                });
            </script>
            <?php endif; ?>

            <?php echo $this->get_dyn_remarketing_purchase_script($order, $order_total) ?>

            <?php

        }

        if (!$order->has_status('failed') && !current_user_can('edit_others_pages') && ($this->add_cart_data == 1 || $this->is_google_analytics_active())) {
            ?>

            <script>
                <?php if ($this->add_cart_data && $this->conversion_id && $this->conversion_label ): ?>
                gtag('event', 'purchase', <?php echo $this->get_event_purchase_json($order, $order_total_filtered, $order_currency, 'ads') ?>);
                <?php endif; ?>

                <?php if ($this->is_google_analytics_active() ): ?>
                gtag('event', 'purchase', <?php echo $this->get_event_purchase_json($order, $order_total_filtered, $order_currency, 'analytics') ?>);
                <?php endif; ?>

            </script>
            <?php


        }

        if ($order->has_status('failed') || current_user_can('edit_others_pages')) {

            ?>

            <!-- The pixels have not been inserted. Possible reasons: -->
            <!--    You are logged into WooCommerce as admin or shop manager. -->
            <!--    The order payment has failed. -->
            <!--    The pixel has already been fired. To prevent double counting the pixel is not being fired again. -->

            <?php
        } // end if order status

        ?>

        <!-- END Google Code for Sales Conversion Page -->
        <?php
    }

    private function get_event_purchase_json($order, $order_total_filtered, $order_currency, $channel)
    {
        $gtag_data = [
            'send_to' => [],
            'transaction_id' => $order->get_order_number(),
            'currency' => $order_currency,
            'discount' => $order->get_total_discount(),
            'items' => $this->get_gads_formatted_order_items($order),
        ];

        if ('ads' === $channel) {
            array_push($gtag_data['send_to'], 'AW-' . $this->conversion_id . '/' . $this->conversion_label);
            $gtag_data['value']            = $order_total_filtered;
            $gtag_data['aw_merchant_id']   = $this->aw_merchant_id;
            $gtag_data['aw_feed_country']  = $this->get_visitor_country();
            $gtag_data['aw_feed_language'] = $this->get_gmc_language();
        }

        if ('analytics' === $channel) {
            if ($this->options_obj->google->analytics->universal->property_id) array_push($gtag_data['send_to'], $this->options_obj->google->analytics->universal->property_id);
            if ($this->options_obj->google->analytics->ga4->measurement_id) array_push($gtag_data['send_to'], $this->options_obj->google->analytics->ga4->measurement_id);
            $gtag_data['affiliation'] = (string)get_bloginfo('name');
            $gtag_data['tax']         = (string)$order->get_total_tax();
            $gtag_data['shipping']    = (string)$order->get_total_shipping();
            $gtag_data['value']       = (float) $order->get_total();
        }

        return json_encode($gtag_data);
    }

    private function is_google_analytics_active(): bool
    {
        if ($this->options_obj->google->analytics->universal->property_id) {
            return true;
        } elseif ($this->options_obj->google->analytics->ga4->measurement_id) {
            return true;
        } else {
            return false;
        }
    }


    protected function woocommerce_3_and_above(): bool
    {
        global $woocommerce;
        if (version_compare($woocommerce->version, 3.0, ">=")) {
            return true;
        } else {
            return false;
        }
    }


    protected function get_gmc_language(): string
    {
        return strtoupper(substr(get_locale(), 0, 2));
    }

    protected function get_gads_formatted_order_items($order)
    {
        $order_items       = $order->get_items();
        $order_items_array = [];

        foreach ((array)$order_items as $item) {

            $product = wc_get_product($item['product_id']);

            $item_details_array = [];

            $item_details_array['id']                       = $this->get_compiled_product_id($item['product_id'], $product->get_sku());
            $item_details_array['quantity']                 = (int)$item['quantity'];
            $item_details_array['price']                    = (int)$product->get_price();
            $item_details_array['google_business_vertical'] = $this->google_business_vertical;

            if ($this->is_google_analytics_active()) {
                $item_details_array['name'] = (string)$product->get_name();
//                $item_details_array['list_name'] = '';
//                $item_details_array['brand'] = '';
                $item_details_array['category'] = $this->get_product_category($item['product_id']);
//                $item_details_array['variant'] = '';
//                $item_details_array['list_position'] = 1;
            }

            array_push($order_items_array, $item_details_array);
        }

        // apply filter to the $order_items_array array
        $order_items_array = apply_filters('wgact_filter', $order_items_array, 'order_items_array');

        return $order_items_array;
    }

    protected function get_compiled_product_id($product_id, $product_sku): string
    {
        // depending on setting use product IDs or SKUs
        if (0 == $this->product_identifier) {
            return (string)$product_id;
        } else if (1 == $this->product_identifier) {
            return (string)'woocommerce_gpf_' . $product_id;
        } else {
            return (string)$product_sku;
        }
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

    protected function gtag_config($id, $channel = ''): string
    {
        if('ads' === $channel){
            return "gtag('config', 'AW-" . $id . "');" . PHP_EOL;
        } elseif ( 'analytics') {
            return "gtag('config', '" . $id . "', { 'anonymize_ip': true });" . PHP_EOL;
        }
    }

    protected function get_gads_formatted_product_details_from_product_id($product_id): array
    {
        $product = wc_get_product($product_id);

        $product_details['id']       = $this->get_compiled_product_id(get_the_ID(), $product->get_sku());
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
        // error_log(print_r($cart, true));
        // initiate product identifier array
        $cart_items   = [];
        $item_details = [];

        // go through the array and get all product identifiers
        foreach ((array)$cart as $item) {

            $product = wc_get_product($item['product_id']);

            $item_details['id']                       = $this->get_compiled_product_id($item['product_id'], $product->get_sku());
            $item_details['quantity']                 = (int)$item['quantity'];
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
        if ($this->is_dynamic_remarketing_active()) {

            ?>

            <script>
                gtag('event', 'purchase', {
                    'send_to': 'AW-<?php _e($this->conversion_id) ?>',
                    'value'  : <?php echo $order_total; ?>,
                    'items'  : <?php echo (json_encode($this->get_gads_formatted_order_items($order))) . PHP_EOL ?>
                });
            </script>
            <?php
        }
    }

    protected function get_google_business_vertical($id): string
    {
        $verticals = [
            0 => 'retail',
            1 => 'education',
            2 => 'flights',
            3 => 'hotel_rental',
            4 => 'jobs',
            5 => 'local',
            6 => 'real_estate',
            7 => 'travel',
            8 => 'custom'
        ];

        return $verticals[$id];
    }
}