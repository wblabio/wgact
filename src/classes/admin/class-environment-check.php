<?php

namespace WGACT\Classes\Admin;

use WC_Order;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Environment_Check
{
    public $notifications;

    public function __construct()
    {
        if (is_admin()) {

            $this->notifications = new Notifications();
            add_action('admin_enqueue_scripts', [$this, 'environment_check_script']);
            add_action('wp_ajax_environment_check_handler', [$this, 'ajax_environment_check_handler']);

            // get all active payment gateways
//            add_action('plugins_loaded', [$this, 'get_active_payment_gateways_after_plugins_loaded']);

            //check for active off-site payment gateways
//            $this->check_active_off_site_payment_gateways();
        }
    }

    public function run_incompatible_plugins_checks()
    {
        $saved_notifications = get_option(WOOPTPM_NOTIFICATIONS);

        foreach ($this->get_incompatible_plugins_list() as $plugin) {

            if (is_array($saved_notifications) && !array_key_exists($plugin['slug'], $saved_notifications) && is_plugin_active($plugin['file_location'])) {

                (new Notifications())->plugin_is_incompatible(
                    $plugin['name'],
                    $plugin['version'],
                    $plugin['slug'],
                    $plugin['link'],
                    $plugin['wooptpm_doc_link']
                );
            }
        }
    }

    public function get_incompatible_plugins_list(): array
    {
        return [
            'wc-custom-thank-you' => [
                'name'             => 'WC Custom Thank You',
                'slug'             => 'wc-custom-thank-you',
                'file_location'    => 'wc-custom-thank-you/woocommerce-custom-thankyou.php',
                'link'             => 'https://wordpress.org/plugins/wc-custom-thank-you/',
                'wooptpm_doc_link' => 'https://docs.woopt.com/wgact/#/troubleshooting?id=wc-custom-thank-you',
                'version'          => '1.2.1',
            ]
        ];
    }

    public function flush_cache_on_plugin_changes()
    {
        // flush cache after saving the plugin options
        add_action('update_option_wgact_plugin_options', [$this, 'flush_cache_of_all_cache_plugins'], 10, 3);

        // flush cache after install
        // we don't need that because after first install the user needs to set new options anyway where the cache flush happens too
//        add_filter('upgrader_post_install', [$this, 'flush_cache_of_all_cache_plugins'], 10, 3);

        // flush cache after plugin update
        add_action('upgrader_process_complete', [$this, 'flush_cache_of_all_cache_plugins'], 10, 2);
    }

    public function flush_cache_of_all_cache_plugins()
    {
//        error_log('flush cache of all cache plugins');
        if ($this->is_wp_rocket_active()) $this->flush_wp_rocket_cache();          // works
        if ($this->is_litespeed_active()) $this->flush_litespeed_cache();          // works
        if ($this->is_autoptimize_active()) $this->flush_autoptimize_cache();      // works
        if ($this->is_hummingbird_active()) $this->flush_hummingbird_cache();      // works
        if ($this->is_nitropack_active()) $this->flush_nitropack_cache();          // works
        if ($this->is_sg_optimizer_active()) $this->flush_sg_optimizer_cache();    // works
        if ($this->is_w3_total_cache_active()) $this->flush_w3_total_cache();      // works
        if ($this->is_wp_optimize_active()) $this->flush_wp_optimize_cache();      // works
        if ($this->is_wp_super_cache_active()) $this->flush_wp_super_cache();      // works
        if ($this->is_wp_fastest_cache_active()) $this->flush_wp_fastest_cache();  // works
        if ($this->is_cloudflare_active()) $this->flush_cloudflare_cache();        // works

        if ($this->is_hosting_wp_engine()) $this->flush_wp_engine_cache();         // works
//        if ($this->is_hosting_pagely()) $this->flush_pagely_cache();               // TODO test
//        if ($this->is_hosting_kinsta()) $this->flush_kinsta_cache();               // TODO test
//
//        if ($this->is_nginx_helper_active()) $this->flush_nginx_cache();           // TODO test

        // TODO add generic varnish purge
    }

    function flush_kinsta_cache()
    {
        try {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, 'https://localhost/kinsta-clear-cache-all');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $response = curl_exec($ch);

            if (!$response) {
                die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
            }

//            echo 'HTTP Status Code: ' . curl_getinfo($ch, CURLINFO_HTTP_CODE) . PHP_EOL;
//            echo 'Response Body: ' . $response . PHP_EOL;

            curl_close($ch);

        } catch (\Exception $e) {
            error_log($e);
        }
    }

    public function is_nginx_helper_active(): bool
    {
        return defined('NGINX_HELPER_BASEPATH');
    }

    function flush_nginx_cache(): bool
    {
        global $nginx_purger;
        if ($nginx_purger) {
            $nginx_purger->purge_all();
        }
        return true;
    }

    public function flush_cloudflare_cache()
    {
        try {
            if (class_exists('\CF\WordPress\Hooks')) {
                (new \CF\WordPress\Hooks())->purgeCacheEverything();
            }
        } catch (\Exception $e) {
            error_log($e);
        }
    }

    public function flush_wp_engine_cache()
    {
        try {
            if (class_exists('WpeCommon')) {
                \WpeCommon::purge_varnish_cache_all();
            }
        } catch (\Exception $e) {
            error_log($e);
        }
    }

    function flush_pagely_cache()
    {
        try {
            if (class_exists("PagelyCachePurge")) { // We need to have this check for clients that switch hosts
                $pagely = new \PagelyCachePurge();
                $pagely->purgeAll();
            }
        } catch (\Exception $e) {
            error_log($e);
        }
    }

    public function flush_wp_fastest_cache()
    {
        if (function_exists('wpfc_clear_all_cache')) {
            wpfc_clear_all_cache(true);;
        }
    }

    public function flush_wp_super_cache()
    {
        if (function_exists('wp_cache_clean_cache')) {
            global $file_prefix;
            wp_cache_clean_cache($file_prefix, true);
        }
    }

    public function flush_wp_optimize_cache()
    {
        if (function_exists('wpo_cache_flush')) {
            wpo_cache_flush();
        }
    }

    public function flush_w3_total_cache()
    {
        if (function_exists('w3tc_flush_all')) {
            w3tc_flush_all();
        }
    }

    public function flush_sg_optimizer_cache()
    {
        if (function_exists('sg_cachepress_purge_everything')) {
            sg_cachepress_purge_everything();
        }
    }

    public function flush_nitropack_cache()
    {
        try {
            if (class_exists('\NitroPack\SDK\Api\Cache')) {
                $siteId     = get_option('nitropack-siteId');
                $siteSecret = get_option('nitropack-siteSecret');
                (new \NitroPack\SDK\Api\Cache($siteId, $siteSecret))->purge();
            }

        } catch (\Exception $e) {
            error_log($e);
        }

//        do_action('nitropack_integration_purge_all');
    }

    public function flush_hummingbird_cache()
    {
        do_action('wphb_clear_page_cache');
    }

    public function flush_autoptimize_cache()
    {
        if (class_exists('autoptimizeCache')) {
            // we need the backslash because autoptimizeCache is in the global namespace
            // and otherwise our plugin would search in its own namespace and throw an error
            \autoptimizeCache::clearall();
        }
    }

    public function flush_litespeed_cache()
    {
        do_action('litespeed_purge_all');
    }

    protected function flush_wp_rocket_cache()
    {
        // flush WP Rocket cache
        if (function_exists('rocket_clean_domain')) {
            rocket_clean_domain();
        }

        // Preload cache.
        if (function_exists('run_rocket_sitemap_preload')) {
            run_rocket_sitemap_preload();
        }
    }

    public function check_active_off_site_payment_gateways()
    {
        $wgact_notifications = get_option(WOOPTPM_NOTIFICATIONS);

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
        wp_enqueue_script('wooptpm-environment-check', WOOPTPM_PLUGIN_DIR_PATH . 'js/admin/environment-check.js', ['jquery'], WGACT_CURRENT_VERSION, true);
    }

    public function ajax_environment_check_handler()
    {
        if (isset($_POST['set'])) {

            $set = $_POST['set'];

            if ('disable_wp_rocket_javascript_concatenation' == $set) {
                $wp_rocket_options                          = get_option('wp_rocket_settings');
                $wp_rocket_options['minify_concatenate_js'] = 0;
                update_option('wp_rocket_settings', $wp_rocket_options);
            }

            if ('dismiss_wp_rocket_javascript_concatenation_error' == $set) {
                $wooptpm_notifications                                                     = get_option(WOOPTPM_NOTIFICATIONS);
                $wooptpm_notifications['dismiss_wp_rocket_javascript_concatenation_error'] = true;
                update_option(WOOPTPM_NOTIFICATIONS, $wooptpm_notifications);
            }

            if ('disable_litespeed_inline_js_dom_ready' == $set) {
                $litespeed_inline_js_dom_ready_option = 0;
                update_option('litespeed.conf.optm-js_inline_defer', $litespeed_inline_js_dom_ready_option);
            }

            if ('dismiss_litespeed_inline_js_dom_ready' == $set) {
                $wooptpm_notifications                                                = get_option(WOOPTPM_NOTIFICATIONS);
                $wooptpm_notifications['dismiss_litespeed_inline_js_dom_ready_error'] = true;
                update_option(WOOPTPM_NOTIFICATIONS, $wooptpm_notifications);
            }

            if ('dismiss_paypal_standard_warning' == $set) {
                $wooptpm_notifications                                    = get_option(WOOPTPM_NOTIFICATIONS);
                $wooptpm_notifications['dismiss_paypal_standard_warning'] = true;
                update_option(WOOPTPM_NOTIFICATIONS, $wooptpm_notifications);
            }
        } else if (isset($_POST['disable_warning'])) {
            $wooptpm_notifications                            = get_option(WOOPTPM_NOTIFICATIONS);
            $wooptpm_notifications[$_POST['disable_warning']] = true;
            update_option(WOOPTPM_NOTIFICATIONS, $wooptpm_notifications);
//            error_log('warning disabled for: ' . $_POST['disable_warning']);
        }

        wp_die(); // this is required to terminate immediately and return a proper response
    }

    private function check_wp_rocket_js_concatenation()
    {
        $wgact_notifications = get_option(WOOPTPM_NOTIFICATIONS);

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
        $wgact_notifications = get_option(WOOPTPM_NOTIFICATIONS);

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

    public function is_wc_custom_thank_you_active(): bool
    {
        return is_plugin_active('wc-custom-thank-you/woocommerce-custom-thankyou.php');
    }

    public function is_wp_rocket_active(): bool
    {
        return is_plugin_active('wp-rocket/wp-rocket.php');
    }

    public function is_sg_optimizer_active(): bool
    {
        return is_plugin_active('sg-cachepress/sg-cachepress.php');
    }

    public function is_w3_total_cache_active(): bool
    {
        return is_plugin_active('w3-total-cache/w3-total-cache.php');
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

    public function is_hummingbird_active(): bool
    {
        // TODO find out if there is a pro version with different folder and file name

        return is_plugin_active('hummingbird-performance/wp-hummingbird.php');
    }

    public function is_nitropack_active(): bool
    {
        // TODO find out if there is a pro version with different folder and file name

        return is_plugin_active('nitropack/main.php');
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

    public function is_wp_super_cache_active(): bool
    {
        // TODO find out if there is a pro version with different folder and file name

        return is_plugin_active('wp-super-cache/wp-cache.php');
    }

    public function is_wp_fastest_cache_active(): bool
    {
        // TODO find out if there is a pro version with different folder and file name

        return is_plugin_active('wp-fastest-cache/wpFastestCache.php');
    }

    public function is_cloudflare_active(): bool
    {
        return is_plugin_active('cloudflare/cloudflare.php');
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

    public function is_hosting_flywheel()
    {
        return defined("FLYWHEEL_PLUGIN_DIR");
    }

    public function is_hosting_cloudways()
    {
        return array_key_exists("cw_allowed_ip", $_SERVER) || preg_match("~/home/.*?cloudways.*~", __FILE__);
    }

    public function is_hosting_wp_engine(): bool
    {
        return !!getenv('IS_WPE');
    }

    public function is_hosting_godaddy_wpaas(): bool
    {
        return class_exists('\WPaaS\Plugin');
    }

    public function is_hosting_siteground(): bool
    {
        $configFilePath = $this->get_wpconfig_path();
        if (!$configFilePath) return false;
        return strpos(file_get_contents($configFilePath), 'Added by SiteGround WordPress management system') !== false;
    }

    public function is_hosting_gridpane(): bool
    {
        $configFilePath = $this->get_wpconfig_path();
        if (!$configFilePath) return false;
        return strpos(file_get_contents($configFilePath), 'GridPane Cache Settings') !== false;
    }

    public function is_hosting_kinsta(): bool
    {
        return defined("KINSTAMU_VERSION");
    }

    public function is_hosting_closte(): bool
    {
        return defined("CLOSTE_APP_ID");
    }

    public function is_hosting_pagely(): bool
    {
        return class_exists('\PagelyCachePurge');
    }

    public function get_hosting_provider(): string
    {
        if ($this->is_hosting_flywheel()) {
            return "Flywheel";
        } else if ($this->is_hosting_cloudways()) {
            return "Cloudways";
        } else if ($this->is_hosting_wp_engine()) {
            return "WP Engine";
        } else if ($this->is_hosting_siteground()) {
            return "SiteGround";
        } else if ($this->is_hosting_godaddy_wpaas()) {
            return "GoDaddy WPaas";
        } else if ($this->is_hosting_gridpane()) {
            return "GridPane";
        } else if ($this->is_hosting_kinsta()) {
            return "Kinsta";
        } else if ($this->is_hosting_closte()) {
            return "Closte";
        } else if ($this->is_hosting_pagely()) {
            return "Pagely";
        } else {
            return "unknown";
        }
    }

    // https://github.com/wp-cli/wp-cli/blob/c3bd5bd76abf024f9d492579539646e0d263a05a/php/utils.php#L257
    public function get_wpconfig_path()
    {
        static $path;

        if (null === $path) {
            $path = false;

            if (getenv('WP_CONFIG_PATH') && file_exists(getenv('WP_CONFIG_PATH'))) {
                $path = getenv('WP_CONFIG_PATH');
            } elseif (file_exists(ABSPATH . 'wp-config.php')) {
                $path = ABSPATH . 'wp-config.php';
            } elseif (file_exists(dirname(ABSPATH) . '/wp-config.php') && !file_exists(dirname(ABSPATH) . '/wp-settings.php')) {
                $path = dirname(ABSPATH) . '/wp-config.php';
            }

            if ($path) {
                $path = realpath($path);
            }
        }

        return $path;
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
//            add_filter('option_litespeed.conf.optm-js_inline_defer', [$this, 'disable_litespeed_js_inline_after_dom']);

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
        if (is_array($exclusions)) $exclusions = array_merge($exclusions, $this->get_wooptpm_script_identifiers());

        return $exclusions;
    }

    public function exclude_inline_scripts_from_wp_rocket_using_options()
    {
        $options = get_option('wp_rocket_settings');

        // if no options array could be retrieved.
        if (!is_array($options)) return;

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
        $last_order = wc_get_order($last_order_id);

        return $last_order->get_checkout_order_received_url();
    }
}