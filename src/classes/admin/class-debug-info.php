<?php

namespace WGACT\Classes\Admin;

use WC_Order_Query;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Debug_info
{
    protected $environment_check;

    public function __construct()
    {
        $this->environment_check = new Environment_Check();
    }

    public function get_debug_info(): string
    {
        global $woocommerce, $wp_version, $current_user, $hook_suffix;

        $html = '### Debugging Information ###' . PHP_EOL . PHP_EOL;

        $html .= '## System Environment ##' . PHP_EOL . PHP_EOL;

        $html .= 'This plugin\'s version: ' . WGACT_CURRENT_VERSION . PHP_EOL;

        $html .= PHP_EOL;

        $html .= 'WordPress version: ' . $wp_version . PHP_EOL;
        $html .= 'WooCommerce version: ' . $woocommerce->version . PHP_EOL;
        $html .= 'PHP version: ' . phpversion() . PHP_EOL;

        $curl_available = $this->environment_check->is_curl_active() ? 'yes' : 'no';
        $html           .= 'curl available: ' . $curl_available . PHP_EOL;
        $html           .= 'wp_remote_get to Cloudflare: ' . $this->wp_remote_get_response('https://www.cloudflare.com/cdn-cgi/trace') . PHP_EOL;
        $html           .= 'wp_remote_get to Google Analytics API: ' . $this->wp_remote_get_response('https://www.google-analytics.com/debug/collect') . PHP_EOL;
        $html           .= 'wp_remote_get to Facebook Graph API: ' . $this->wp_remote_get_response('https://graph.facebook.com/facebook/picture?redirect=false') . PHP_EOL;
//        $html           .= 'wp_remote_post to Facebook Graph API: ' . $this->wp_remote_get_response('https://graph.facebook.com/') . PHP_EOL;

        $html .= PHP_EOL;

        $multisite_enabled = is_multisite() ? 'yes' : 'no';
        $html              .= 'Multisite enabled: ' . $multisite_enabled . PHP_EOL;

        $wp_debug = 'no';
        if (defined('WP_DEBUG') && true === WP_DEBUG) {
            $wp_debug = 'yes';
        }

        $html .= 'WordPress debug mode enabled: ' . $wp_debug . PHP_EOL;

//        wp_get_current_user();
        $html .= 'Logged in user login name: ' . $current_user->user_login . PHP_EOL;
        $html .= 'Logged in user display name: ' . $current_user->display_name . PHP_EOL;

        $html .= 'hook_suffix: ' . $hook_suffix . PHP_EOL;

        $html .= PHP_EOL;

        $html .= 'Hosting provider: ' . $this->environment_check->get_hosting_provider() . PHP_EOL;


        $html .= PHP_EOL . '## WooCommerce ##' . PHP_EOL . PHP_EOL;

        $html .= 'Default currency: ' . get_woocommerce_currency() . PHP_EOL;
        $html .= 'Shop URL: ' . get_home_url() . PHP_EOL;
        $html .= 'Cart URL: ' . wc_get_cart_url() . PHP_EOL;
        $html .= 'Checkout URL: ' . wc_get_checkout_url() . PHP_EOL;
        $html .= 'Purchase confirmation endpoint: ' . wc_get_endpoint_url('order-received') . PHP_EOL;

        $order_received_page_url = wc_get_checkout_url() . ltrim(wc_get_endpoint_url('order-received'), '/');
        $html                    .= 'is_order_received_page(): ' . $order_received_page_url . PHP_EOL . PHP_EOL;

        $last_order_url = $this->environment_check->get_last_order_url();
        $html           .= 'Last order URL: ' . $last_order_url . '&nodedupe' . PHP_EOL;

        $last_order_url_contains_order_received_page_url = strpos($this->environment_check->get_last_order_url(), $order_received_page_url) !== false ? 'yes' : 'no';
        $html                                            .= 'Order received page uses proper is_order_received() url: ' . $last_order_url_contains_order_received_page_url . PHP_EOL;

        $purchase_confirmation_page_redirect = $this->environment_check->does_url_redirect($last_order_url) ? 'yes' : 'no';
        $html                                .= $this->show_warning($this->environment_check->does_url_redirect($last_order_url)) . 'Purchase confirmation page redirect: ' . $purchase_confirmation_page_redirect . PHP_EOL;

        if ($this->environment_check->does_url_redirect($last_order_url)) {
            $html .= 'Redirect URL: ' . $this->environment_check->get_redirect_url($this->environment_check->get_last_order_url()) . PHP_EOL;
        }

//        $html                                .= 'wc_get_page_permalink(\'checkout\'): ' . wc_get_page_permalink('checkout') . PHP_EOL;

        $html .= PHP_EOL . '## WooCommerce Payment Gateways##' . PHP_EOL . PHP_EOL;
        $html .= 'Active payment gateways: ' . PHP_EOL;

//        $this->get_enabled_payment_gateways();

        foreach ($this->get_enabled_payment_gateways() as $key => $value) {
//            error_log(get_class($value));
//            error_log($value->method_title);

            $html .= "\t" . get_class($value) . '(' . $value->method_title . ')' . PHP_EOL;
        }

        $max_order_amount = 100;
        $html             .= PHP_EOL . "Purchase confirmation page reached per gateway (of last $max_order_amount orders):" . PHP_EOL;

        foreach ($this->get_gateway_analysis_array($max_order_amount) as $text) {
            $html .= "\t" . $text . PHP_EOL;
        }

//        $html .= PHP_EOL;

        $html .= PHP_EOL . '## Theme ##' . PHP_EOL . PHP_EOL;

        $is_child_theme = is_child_theme() ? 'yes' : 'no';
        $html           .= 'Is child theme: ' . $is_child_theme . PHP_EOL;
        $theme_support  = current_theme_supports('woocommerce') ? 'yes' : 'no';
        $html           .= 'WooCommerce support: ' . $theme_support . PHP_EOL;

        $html .= PHP_EOL;

        // using the double check prevents problems with some themes that have not implemented
        // the child state correctly
        // https://wordpress.org/support/topic/debug-error-33/
        $theme_description_prefix = (is_child_theme() && wp_get_theme()->parent()) ? 'Child theme ' : 'Theme ';

        $html .= $theme_description_prefix . 'Name: ' . wp_get_theme()->get('Name') . PHP_EOL;
        $html .= $theme_description_prefix . 'ThemeURI: ' . wp_get_theme()->get('ThemeURI') . PHP_EOL;
        $html .= $theme_description_prefix . 'Author: ' . wp_get_theme()->get('Author') . PHP_EOL;
        $html .= $theme_description_prefix . 'AuthorURI: ' . wp_get_theme()->get('AuthorURI') . PHP_EOL;
        $html .= $theme_description_prefix . 'Version: ' . wp_get_theme()->get('Version') . PHP_EOL;
        $html .= $theme_description_prefix . 'Template: ' . wp_get_theme()->get('Template') . PHP_EOL;
        $html .= $theme_description_prefix . 'Status: ' . wp_get_theme()->get('Status') . PHP_EOL;
        $html .= $theme_description_prefix . 'TextDomain: ' . wp_get_theme()->get('TextDomain') . PHP_EOL;
        $html .= $theme_description_prefix . 'DomainPath: ' . wp_get_theme()->get('DomainPath') . PHP_EOL;

        $html .= PHP_EOL;

        // using the double check prevents problems with some themes that have not implemented
        // the child state correctly
        if (is_child_theme() && wp_get_theme()->parent()) {
            $html .= 'Parent theme Name: ' . wp_get_theme()->parent()->get('Name') . PHP_EOL;
            $html .= 'Parent theme ThemeURI: ' . wp_get_theme()->parent()->get('ThemeURI') . PHP_EOL;
            $html .= 'Parent theme Author: ' . wp_get_theme()->parent()->get('Author') . PHP_EOL;
            $html .= 'Parent theme AuthorURI: ' . wp_get_theme()->parent()->get('AuthorURI') . PHP_EOL;
            $html .= 'Parent theme Version: ' . wp_get_theme()->parent()->get('Version') . PHP_EOL;
            $html .= 'Parent theme Template: ' . wp_get_theme()->parent()->get('Template') . PHP_EOL;
            $html .= 'Parent theme Status: ' . wp_get_theme()->parent()->get('Status') . PHP_EOL;
            $html .= 'Parent theme TextDomain: ' . wp_get_theme()->parent()->get('TextDomain') . PHP_EOL;
            $html .= 'Parent theme DomainPath: ' . wp_get_theme()->parent()->get('DomainPath') . PHP_EOL;
        }

        // TODO maybe add all active plugins

        $html .= PHP_EOL;

        $html .= PHP_EOL . '## freemius ##' . PHP_EOL . PHP_EOL;

        $html .= 'api.freemius.com : ' . $this->try_connect_to_server('api.freemius.com') . PHP_EOL;
        $html .= 'wp.freemius.com : ' . $this->try_connect_to_server('wp.freemius.com') . PHP_EOL;

//        $html .= PHP_EOL . '## misc ##' . PHP_EOL . PHP_EOL;

//        $html .= 'WP Rocket JavaScript concatenation: ' . $this->is_wp_rocket_js_concatenation();

        $html .= PHP_EOL . PHP_EOL . '### End of Information ###';

        return $html;
    }

    // possible way to use a proxy if necessary
    // https://freemius.com/help/documentation/wordpress-sdk/license-activation-issues/#isp_blockage
    // https://deliciousbrains.com/php-curl-how-wordpress-makes-http-requests/
    // possible proxy list
    // https://www.us-proxy.org/
    // Google and Facebook might block free proxy requests
    private function wp_remote_get_response($url)
    {
        $response = wp_remote_get($url, [
            'timeout'             => 2,
            'sslverify'           => false,
            'limit_response_size' => 5000,
        ]);

//        error_log(print_r($response, true));

        if (is_wp_error($response)) {
            return $this->show_warning(true) . $response->get_error_message();
        } else {
            $response_code = wp_remote_retrieve_response_code($response);

            if ($response_code === 200) {
                return $response_code;
            } else {
                return $this->show_warning(true) . $response_code;
            }
        }
    }

    private function show_warning($test = false): string
    {
        if ($test) {
            return '❗ ';
        } else {
            return '';
        }
    }

    private function is_wp_rocket_js_concatenation(): string
    {
        if (is_plugin_active('wp-rocket/wp-rocket.php')) {

            $wp_rocket_settings = get_option('wp_rocket_settings');

            if ($wp_rocket_settings) {
                if (true == $wp_rocket_settings['minify_concatenate_js']) {
                    return 'on';
                } else {
                    return 'off';
                }
            }
        } else {
            return 'off';
        }
    }

    private function try_connect_to_server($server): string
    {
        if ($socket = @ fsockopen($server, 80)) {
            @fclose($socket);
            return 'online';
        } else {
            return 'offline';
        }
    }

    private function get_enabled_payment_gateways(): array
    {
        $gateways         = WC()->payment_gateways->get_available_payment_gateways();
        $enabled_gateways = [];

        if ($gateways) {
            foreach ($gateways as $gateway) {

                if ($gateway->enabled == 'yes') $enabled_gateways[] = $gateway;
            }
        }

//        error_log(print_r($enabled_gateways, true)); // Should return an array of enabled gateways

        return $enabled_gateways;
    }

    private function get_last_orders($limit = 100)
    {
        // Get most recent order ids in date descending order.
        $query = new WC_Order_Query([
            'limit'   => $limit,
            'type'    => 'shop_order',
            'orderby' => 'date',
            'order'   => 'DESC',
            'return'  => 'ids',
        ]);

        return $query->get_orders();
    }

    private function list_gateways_of_orders($limit = 100): array
    {
        $last_orders = $this->get_last_orders($limit);

        if (empty($last_orders)) {
            return [];
        }

//        error_log(print_r(array_flip($last_orders), true));
//        error_log(min($last_orders));

        $earliest_relevant_order_id = $this->get_earliest_order_with_pixel_fired_tag($last_orders, $limit);

        if ($earliest_relevant_order_id === false) {
            return [];
        }

        // only keep orders up until the oldest one with _wooptpm_conversion_pixel_fired
        $last_orders = array_filter($last_orders, function ($x) use ($earliest_relevant_order_id) {
            return $x >= $earliest_relevant_order_id;
        });

//        error_log(print_r(array_flip($last_orders), true));

        $data = [];

        foreach ($last_orders as $order_id) {
            $order = wc_get_order($order_id);

//            error_log(print_r(get_post_meta($order_id, '_wooptpm_conversion_pixel_fired', true), true));
//            error_log('payment method: ' . $order->get_payment_method() . ', ' . $order->get_payment_method_title());

            if (!array_key_exists($order->get_payment_method(), $data)) {
                $data[$order->get_payment_method()]                 = [];
                $data[$order->get_payment_method()]['fired']        = 0;
                $data[$order->get_payment_method()]['not_fired']    = 0;
                $data[$order->get_payment_method()]['method_title'] = $order->get_payment_method_title();
            }

            $fired = get_post_meta($order_id, '_wooptpm_conversion_pixel_fired', true);

//            error_log('order_id: ' . $order_id . ', payment method: ' . $order->get_payment_method() . ', ' . $order->get_payment_method_title() . ', ' . $fired);

            if ($fired) {
                $data[$order->get_payment_method()]['fired'] += 1;
            } else {
                $data[$order->get_payment_method()]['not_fired'] += 1;
            }
//            error_log('payment method title: ' . $order->get_payment_method_title());
        }

//        error_log(print_r($data, true));

        return $data;
    }

    private function get_gateway_analysis_array($limit = 100): array
    {
        $data = [];

        foreach ($this->list_gateways_of_orders($limit) as $gateway => $value) {

            $fired     = $value['fired'];
            $not_fired = $value['not_fired'];
            $total     = $fired + $not_fired;

            if ($total > 0) {
                $percentage = number_format((float)($fired / $total), 2, '.', '');
                $text       = $gateway . ' (' . $value['method_title'] . '): ' . $fired . ' / ' . $total . ' => ' . $percentage * 100 . '% accuracy';
            } else {
                $text = $gateway . ' (' . $value['method_title'] . '): ' . $fired . ' / ' . $total;
            }

            $data[] = $text;
        }

        return $data;
    }

    public function get_earliest_order_with_pixel_fired_tag($order_ids, $limit)
    {
        $query = new WC_Order_Query([
            'limit'    => $limit,
            // 'orderby'  => 'date',
            // 'order'    => 'DESC',
            'type'     => 'shop_order',
            'return'   => 'ids',
            'post__in' => $order_ids,
            'meta_key' => '_wooptpm_conversion_pixel_fired'
        ]);

//        error_log(print_r($query->get_orders(), true));
//        error_log('min: ' . min($query->get_orders()));

        $result = $query->get_orders();

        if (!empty($result)) {
            return min($result);
        } else {
            return false;
        }
    }
}