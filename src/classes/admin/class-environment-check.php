<?php

namespace WGACT\Classes\Admin;

use WC_Order;
use WC_Tracker;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Environment_Check
{
    public function __construct()
    {
        if (is_admin()) {
            add_action('admin_enqueue_scripts', [$this, 'environment_check_script']);
            add_action('wp_ajax_environment_check_handler', [$this, 'ajax_environment_check_handler']);

            // get all active payment gateways
//            add_action('plugins_loaded', [$this, 'get_active_payment_gateways_after_plugins_loaded']);

            //check for active off-site payment gateways
//            $this->check_active_off_site_payment_gateways();
        }
    }

    public function check_active_off_site_payment_gateways()
    {
        $wgact_notifications = get_option('wgact_notifications');

        if (!is_array($wgact_notifications) || !array_key_exists('dismiss_paypal_standard_warning', $wgact_notifications) || $wgact_notifications['dismiss_paypal_standard_warning'] !== true) {

            if ($this->is_paypal_standard_active()) {
                // run off-site payment gateway warning
                (new Notifications())->paypal_standard_active_warning();
            }
        }
    }

    public function get_active_payment_gateways_after_plugins_loaded()
    {
        error_log(print_r($this->get_active_payment_gateways(), true));
    }

    private static function get_active_payment_gateways(): array
    {
        $active_gateways = [];
        $gateways        = WC()->payment_gateways->payment_gateways();
        foreach ($gateways as $id => $gateway) {
            if (isset($gateway->enabled) && 'yes' === $gateway->enabled) {
                $active_gateways[$id] = [
                    'title'    => $gateway->title,
                    'supports' => $gateway->supports,
                ];
            }
        }

        return $active_gateways;
    }

    public function run_checks()
    {
//        $this->check_wp_rocket_js_concatenation();
//        $this->check_litespeed_js_inline_after_dom();
    }

    public function environment_check_script()
    {
//        wp_enqueue_script('wooptpm-environment-check', plugin_dir_url(__DIR__) . '../js/admin/environment-check.js', ['jquery'], WGACT_CURRENT_VERSION, true);
        wp_enqueue_script('wooptpm-environment-check', WGACT_PLUGIN_DIR_PATH . 'js/admin/environment-check.js', ['jquery'], WGACT_CURRENT_VERSION, true);
    }

    public function ajax_environment_check_handler()
    {
        $set = $_POST['set'];

        if ('disable_wp_rocket_javascript_concatenation' == $set) {
            $wp_rocket_options                          = get_option('wp_rocket_settings');
            $wp_rocket_options['minify_concatenate_js'] = 0;
            update_option('wp_rocket_settings', $wp_rocket_options);
        }

        if ('dismiss_wp_rocket_javascript_concatenation_error' == $set) {
            $wgact_notifications                                                     = get_option('wgact_notifications');
            $wgact_notifications['dismiss_wp_rocket_javascript_concatenation_error'] = true;
            update_option('wgact_notifications', $wgact_notifications);
        }

        if ('disable_litespeed_inline_js_dom_ready' == $set) {
            $litespeed_inline_js_dom_ready_option = 0;
            update_option('litespeed.conf.optm-js_inline_defer', $litespeed_inline_js_dom_ready_option);
        }

        if ('dismiss_litespeed_inline_js_dom_ready' == $set) {
            $wgact_notifications                                                = get_option('wgact_notifications');
            $wgact_notifications['dismiss_litespeed_inline_js_dom_ready_error'] = true;
            update_option('wgact_notifications', $wgact_notifications);
        }

        if ('dismiss_paypal_standard_warning' == $set) {
            $wgact_notifications                                    = get_option('wgact_notifications');
            $wgact_notifications['dismiss_paypal_standard_warning'] = true;
            update_option('wgact_notifications', $wgact_notifications);
        }

        wp_die(); // this is required to terminate immediately and return a proper response
    }

    private function check_wp_rocket_js_concatenation()
    {
        $wgact_notifications = get_option('wgact_notifications');

        if ($this->is_wp_rocket_active() && (!is_array($wgact_notifications) || false == $wgact_notifications['dismiss_wp_rocket_javascript_concatenation_error'])) {

            $wp_rocket_settings = get_option('wp_rocket_settings');

            if ($wp_rocket_settings) {
                if (true == $wp_rocket_settings['minify_concatenate_js']) {
                    // display warning
                    (new Notifications())->wp_rocket_js_concatenation_error();
                }
            }
        }
    }

    private function check_litespeed_js_inline_after_dom()
    {
        $wgact_notifications = get_option('wgact_notifications');

        if ($this->is_litespeed_active() && (!is_array($wgact_notifications) || false == $wgact_notifications['dismiss_litespeed_inline_js_dom_ready_error'])) {

            $litespeed_js_inline_defer_settings = get_option('litespeed.conf.optm-js_inline_defer');

            if ($litespeed_js_inline_defer_settings) {
                if (1 == $litespeed_js_inline_defer_settings) {
                    // display warning
                    (new Notifications())->litespeed_js_defer_error();
                }
            }
        }
    }

    public function is_paypal_standard_active(): bool
    {
        $woocommerce_paypal_settings = get_option('woocommerce_paypal_settings');

        if (!is_bool($woocommerce_paypal_settings) && array_key_exists('enabled', $woocommerce_paypal_settings) && $woocommerce_paypal_settings['enabled'] === 'yes') {
            return true;
        } else {
            return false;
        }
    }

    public function is_wp_rocket_active(): bool
    {
        return is_plugin_active('wp-rocket/wp-rocket.php');
    }

    public function is_sg_optimizer_active(): bool
    {
        return is_plugin_active('sg-cachepress/sg-cachepress.php');
    }

    public function is_litespeed_active(): bool
    {
        // TODO find out if there is a pro version with different folder and file name

        return is_plugin_active('litespeed-cache/litespeed-cache.php');
    }

    public function is_autoptimize_active(): bool
    {
        // TODO find out if there is a pro version with different folder and file name

        return is_plugin_active('autoptimize/autoptimize.php');
    }

    public function is_yoast_seo_active(): bool
    {
        // TODO find out if there is a pro version with different folder and file name

        return is_plugin_active('wordpress-seo/wp-seo.php');
    }

    public function is_borlabs_cookie_active(): bool
    {
        // TODO find out if there is a pro version with different folder and file name

        return is_plugin_active('borlabs-cookie/borlabs-cookie.php');
    }

    public function is_wpml_woocommerce_multi_currency_active(): bool
    {
        global $woocommerce_wpml;

        if (is_plugin_active('woocommerce-multilingual/wpml-woocommerce.php') && is_object($woocommerce_wpml->multi_currency)) {
            return true;
        } else {
            return false;
        }
    }

    public function is_woo_discount_rules_active(): bool
    {
        if (is_plugin_active('woo-discount-rules/woo-discount-rules.php') || is_plugin_active('woo-discount-rules-pro/woo-discount-rules-pro.php')) {
            return true;
        } else {
            return false;
        }
    }

    public function is_wp_optimize_active(): bool
    {
        return is_plugin_active('wp-optimize/wp-optimize.php');
    }

    public function is_woocommerce_brands_active(): bool
    {
        return is_plugin_active('woocommerce-brands/woocommerce-brands.php');
    }

    public function is_yith_wc_brands_active(): bool
    {
        return is_plugin_active('yith-woocommerce-brands-add-on-premium/init.php');
    }

    public function disable_yoast_seo_facebook_social($option)
    {
        $option['opengraph'] = false;
        return $option;
    }

    public function disable_litespeed_js_inline_after_dom($option): int
    {
        return 0;
    }

    public function disable_wp_rocket_js_optimizations($option)
    {
        $option['minify_concatenate_js'] = 0;
//        $option['defer_all_js']          = 0;
//        $option['delay_js']              = 0;
        return $option;
    }

    public function permanent_compatibility_mode()
    {
        if ($this->is_wp_rocket_active()) $this->exclude_inline_scripts_from_wp_rocket_using_options();

        // for testing you need to clear the WP Rocket cache, only then the filters run
        if ($this->is_wp_rocket_active()) {
            add_filter('rocket_delay_js_exclusions', [$this, 'add_wp_rocket_exclusions']);
            add_filter('rocket_defer_inline_exclusions', [$this, 'add_wp_rocket_exclusions']);
            add_filter('rocket_exclude_defer_js', [$this, 'add_wp_rocket_exclusions']);
            add_filter('rocket_exclude_js', [$this, 'add_wp_rocket_exclusions']);
            add_filter('rocket_minify_excluded_external_js', [$this, 'add_wp_rocket_exclusions']);
            add_filter('rocket_excluded_inline_js_content', [$this, 'add_wp_rocket_exclusions']);
        }

        if ($this->is_sg_optimizer_active()) {
            add_filter('sgo_javascript_combine_excluded_inline_content', [$this, 'sg_optimizer_js_exclude_combine_inline_content']);
            add_filter('sgo_js_minify_exclude', [$this, 'sg_optimizer_js_minify_exclude']);
            add_filter('sgo_javascript_combine_exclude_move_after', [$this, 'sgo_javascript_combine_exclude_move_after']);
        }

        if ($this->is_litespeed_active()) {
            add_filter('litespeed_optm_js_defer_exc', [$this, 'litespeed_cache_js_defer_exc']);
            add_filter('litespeed_optimize_js_excludes', [$this, 'litespeed_optimize_js_excludes']);
            add_filter('litespeed_optm_cssjs', [$this, 'litespeed_optm_cssjs']);

//             litespeed_optm_cssjs
//             litespeed_optm_html_head
        }

        if ($this->is_autoptimize_active()) {
            add_filter('autoptimize_filter_js_consider_minified', [$this, 'autoptimize_filter_js_consider_minified']);
            add_filter('autoptimize_filter_js_dontmove', [$this, 'autoptimize_filter_js_dontmove']);
        }

        if ($this->is_wp_optimize_active()) {
            // add_filter('wpo_minify_inline_js', '__return_false');
            add_filter('wp-optimize-minify-default-exclusions', [$this, 'wp_optimize_minify_default_exclusions']);
        }
    }

    public function wp_optimize_minify_default_exclusions($default_exclusions): array
    {
        // $default_exclusions[] = 'something/else.js';
        // $default_exclusions[] = 'something/else.css';
        return array_merge($default_exclusions, $this->get_wooptpm_script_identifiers());
    }

    // https://github.com/futtta/autoptimize/blob/37b13d4e19269bb2f50df123257de51afa37244f/classes/autoptimizeScripts.php#L387
    public function autoptimize_filter_js_consider_minified(): array
    {
        $exclude_js[] = 'wooptpm.js';
//        $exclude_js[] = 'jquery.js';
//        $exclude_js[] = 'jquery.min.js';
        return $exclude_js;
    }

    // https://github.com/futtta/autoptimize/blob/37b13d4e19269bb2f50df123257de51afa37244f/classes/autoptimizeScripts.php#L285
    public function autoptimize_filter_js_dontmove($dontmove)
    {
        $dontmove[] = 'wooptpm.js';
        $dontmove[] = 'jquery.js';
        $dontmove[] = 'jquery.min.js';
        return $dontmove;
    }

    public function litespeed_optm_cssjs($excludes)
    {
        return $excludes;
    }

    public function litespeed_optimize_js_excludes($excludes): array
    {
        if (is_array($excludes)) {
            $excludes = array_merge($excludes, $this->get_wooptpm_script_identifiers());
        }

        return $excludes;
    }

    public function litespeed_cache_js_defer_exc($excludes): array
    {
        if (is_array($excludes)) {
            $excludes = array_merge($excludes, $this->get_wooptpm_script_identifiers());
        }
        return $excludes;
    }

    public function sg_optimizer_js_exclude_combine_inline_content($exclude_list): array
    {
        if (is_array($exclude_list)) {
            $exclude_list = array_merge($exclude_list, $this->get_wooptpm_script_identifiers());
        }

//        foreach ($this->get_wooptpm_script_identifiers() as $exclusion) {
//            $exclude_list[] = $exclusion;
//        }

        return $exclude_list;
    }

    public function sg_optimizer_js_minify_exclude($exclude_list)
    {
        $exclude_list[] = 'wooptpm-front-end-scripts';
        $exclude_list[] = 'wooptpm-front-end-scripts-premium-only';
        $exclude_list[] = 'wooptpm';
        $exclude_list[] = 'wooptpm-premium-only';
        $exclude_list[] = 'wooptpm-facebook';
        $exclude_list[] = 'wooptpm-script-blocker-warning';
        $exclude_list[] = 'wooptpm-admin-helpers';
        $exclude_list[] = 'wooptpm-admin-tabs';
        $exclude_list[] = 'wooptpm-selectWoo';
        $exclude_list[] = 'wooptpm-google-ads';
        $exclude_list[] = 'wooptpm-ga-ua-eec';
        $exclude_list[] = 'wooptpm-ga4-eec';
        $exclude_list[] = 'jquery';
        $exclude_list[] = 'jquery-core';
        $exclude_list[] = 'jquery-migrate';

        return $exclude_list;
    }

    public function sgo_javascript_combine_exclude_move_after($exclude_list): array
    {
        if (is_array($exclude_list)) {
            $exclude_list = array_merge($exclude_list, $this->get_wooptpm_script_identifiers());
        }

        return $exclude_list;
    }

    public function add_wp_rocket_exclusions($exclusions): array
    {
        $exclusions = array_merge($exclusions, $this->get_wooptpm_script_identifiers());
        return array_merge($exclusions, $this->get_wooptpm_script_identifiers());
    }

    public function exclude_inline_scripts_from_wp_rocket_using_options()
    {
        $options        = get_option('wp_rocket_settings');
        $update_options = false;

        $js_to_exclude = $this->get_wooptpm_script_identifiers();

        foreach ($js_to_exclude as $string) {

            // add exclusions for inline js
//            if (array_key_exists('exclude_inline_js', $options) && is_array($options['exclude_inline_js']) && !in_array($string, $options['exclude_inline_js'])) {
//
//                array_push($options['exclude_inline_js'], $string);
//                $update_options = true;
//            }

            // add exclusions for js
//            if (array_key_exists('exclude_js', $options) && is_array($options['exclude_js']) && !in_array($string, $options['exclude_js'])) {
//
//                array_push($options['exclude_js'], $string);
//                $update_options = true;
//            }

            // remove scripts from delay_js_scripts
            if (array_key_exists('delay_js_scripts', $options) && is_array($options['delay_js_scripts']) && in_array($string, $options['delay_js_scripts'])) {

                unset($options['delay_js_scripts'][array_search($string, $options['delay_js_scripts'])]);
                $update_options = true;
            }

            // exclude_defer_js
//            if (array_key_exists('exclude_defer_js', $options) && is_array($options['exclude_defer_js']) && !in_array($string, $options['exclude_defer_js'])) {
//
//                array_push($options['exclude_defer_js'], $string);
//                $update_options = true;
//            }

            // exclude_delay_js
//            if (array_key_exists('delay_js_exclusions', $options) && is_array($options['delay_js_exclusions']) && !in_array($string, $options['delay_js_exclusions'])) {
//
//                array_push($options['delay_js_exclusions'], $string);
//                $update_options = true;
//            }
        }

        if ($update_options === true) {
            update_option('wp_rocket_settings', $options);
        }
    }


    public function enable_maximum_compatibility_mode()
    {
        if ($this->is_litespeed_active()) add_filter('option_litespeed.conf.optm-js_inline_defer', [$this, 'disable_litespeed_js_inline_after_dom']);

        // disabling WP Rocket js optimizations not necessary here, since we add permanent script specific exclusions
//        if ($this->is_wp_rocket_active()) add_filter('option_wp_rocket_settings', [$this, 'disable_wp_rocket_js_optimizations']);
    }

    public function enable_maximum_compatibility_mode_yoast_seo()
    {
        if ($this->is_yoast_seo_active()) add_filter('option_wpseo_social', [$this, 'disable_yoast_seo_facebook_social']);
    }

    private function get_wooptpm_script_identifiers(): array
    {
        return [
            'optimize.js',
            'googleoptimize.com/optimize.js',
            'jQuery',
            'jQuery.min.js',
            'jquery.js',
            'jquery.min.js',
            'wooptpm',
            'wooptpmDataLayer',
            'window.wooptpmDataLayer',
            'wooptpm.js',
            'wooptpm__premiums_only.js',
            //            'facebook.js',
            //            'facebook__premium_only.js',
            //            'google-ads.js',
            //            'google-ga-4-eec__premium_only.js',
            //            'google-ga-us-eec__premium_only.js',
            //            'google__premium_only.js',
            'window.dataLayer',
            //            '/gtag/js',
            'gtag',
            //            '/gtag/js',
            //            'gtag(',
            'gtm.js',
            //            '/gtm-',
            //            'GTM-',
            //            'fbq(',
            'fbq',
            'fbevents.js',
            //            'twq(',
            'twq',
            //            'e.twq',
            'static.ads-twitter.com/uwt.js',
            'platform.twitter.com/widgets.js',
            'uetq',
            'ttq',
            'events.js',
            'snaptr',
            'scevent.min.js',
        ];
    }

    public function is_curl_active(): bool
    {
        return function_exists('curl_version');
    }

    public function does_url_redirect($url): bool
    {
        $context = stream_context_create([
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ],
        ]);

        $headers = get_headers($url, 1, $context);
        if (!empty($headers['Location'])) {
            return true;
        } else {
            return false;
        }
    }

    public function get_redirect_url($url): string
    {
        $headers = get_headers($url, 1);

        if (!empty($headers['Location'])) {
            if (is_array($headers['Location'])) {
                return end($headers['Location']);
            } else {
                return $headers['Location'];
            }
        } else {
            return '';
        }
    }

    public function get_last_order_id()
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

    public function get_last_order_url(): string
    {
        $last_order_id = $this->get_last_order_id();
        //		echo('last order: ' . $last_order_id . PHP_EOL);
        $last_order = new WC_Order(wc_get_order($last_order_id));

        return $last_order->get_checkout_order_received_url();
    }
}