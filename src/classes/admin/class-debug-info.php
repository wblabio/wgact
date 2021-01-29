<?php

namespace WGACT\Classes\Admin;

use WC_Order;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Debug_info {

    public function get_debug_info(): string
    {
        global $woocommerce, $wp_version, $current_user;

        $html = '### Debugging Information ###' . PHP_EOL . PHP_EOL;

        $html .= '## System Environment ##' . PHP_EOL . PHP_EOL;

        $html .= 'This plugin\'s version: ' . WGACT_CURRENT_VERSION . PHP_EOL;

        $html .= PHP_EOL;

        $html .= 'WordPress version: ' . $wp_version . PHP_EOL;
        $html .= 'WooCommerce version: ' . $woocommerce->version . PHP_EOL;
        $html .= 'PHP version: ' . phpversion() . PHP_EOL;

        $html .= PHP_EOL;

        $multisite_enabled = is_multisite() ? 'yes' : 'no';
        $html              .= 'Multisite enabled: ' . $multisite_enabled . PHP_EOL;

        $wp_debug = 'no';
        if (defined('WP_DEBUG') && true === WP_DEBUG) {
            $wp_debug = 'yes';
        }

        $html .= 'WordPress debug mode enabled: ' . $wp_debug . PHP_EOL;

        wp_get_current_user();
        $html .= 'Logged in user login name: ' . $current_user->user_login . PHP_EOL;
        $html .= 'Logged in user display name: ' . $current_user->display_name . PHP_EOL;

        $html .= PHP_EOL . '## WooCommerce ##' . PHP_EOL . PHP_EOL;

        $html .= 'Default currency: ' . get_woocommerce_currency() . PHP_EOL;
        $html .= 'Shop URL: ' . get_home_url() . PHP_EOL;
        $html .= 'Cart URL: ' . wc_get_cart_url() . PHP_EOL;
        $html .= 'Checkout URL: ' . wc_get_checkout_url() . PHP_EOL;

        $last_order_id = $this->get_last_order_id();
//		echo('last order: ' . $last_order_id . PHP_EOL);
        $last_order = new WC_Order(wc_get_order($last_order_id));
        $html       .= 'Last order URL: ' . $last_order->get_checkout_order_received_url() . PHP_EOL;


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

        $html .= PHP_EOL . '## misc ##' . PHP_EOL . PHP_EOL;

        $html .= 'WP Rocket JavaScript concatenation: ' . $this->is_wp_rocket_js_concatenation();

        $html .= PHP_EOL . PHP_EOL . '### End of Information ###';

        return $html;
    }

    private function is_wp_rocket_js_concatenation()
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

    private function get_last_order_id()
    {
        global $wpdb;
        $statuses = array_keys(wc_get_order_statuses());
        $statuses = implode("','", $statuses);

        // Getting last Order ID (max value)
        $results = $wpdb->get_col("
            SELECT MAX(ID) FROM {$wpdb->prefix}posts
            WHERE post_type LIKE 'shop_order'
            AND post_status IN ('$statuses')
        ");

        return reset($results);
    }
}