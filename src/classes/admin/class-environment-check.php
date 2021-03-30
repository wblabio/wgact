<?php

namespace WGACT\Classes\Admin;

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
        }
    }

    public function run_checks()
    {
//        $this->check_wp_rocket_js_concatenation();
        $this->check_litespeed_js_inline_after_dom();
    }

    public function environment_check_script()
    {
        wp_enqueue_script(
            'environment-check', // Handle
            plugin_dir_url(__DIR__) . '../js/admin/environment-check.js',
            ['jquery'],
            WGACT_CURRENT_VERSION,
            true
        );
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

    public function is_wp_rocket_active(): bool
    {
        return is_plugin_active('wp-rocket/wp-rocket.php');
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

    public function disable_yoast_seo_facebook_social($option)
    {
        $option['opengraph'] = false;
        return $option;
    }

    public function disable_litespeed_js_inline_after_dom($option): int
    {
        $option = 0;
        return $option;
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
        if ($this->is_wp_rocket_active()) $this->exclude_inline_scripts_from_wp_rocket();
    }

    public function exclude_inline_scripts_from_wp_rocket()
    {
//        error_log('test');
        $options        = get_option('wp_rocket_settings');
        $update_options = false;

        $js_to_exclude = [
            'optimize.js',
            'googleoptimize.com/optimize.js',
            'jQuery',
            'wooptpm',
            'wooptpmDataLayer',
            'window.wooptpmDataLayer',
//            'wooptpm__premiums_only.js',
            'wooptpm.js',
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
        ];

        foreach ($js_to_exclude as $string) {

            if (!in_array($string, $options['exclude_inline_js'])) {

                array_push($options['exclude_inline_js'], $string);
                $update_options = true;
            }
        }

        foreach ($js_to_exclude as $string) {

            if (!in_array($string, $options['exclude_js'])) {

                array_push($options['exclude_js'], $string);
                $update_options = true;
            }
        }

        // remove scripts from delay_js_scripts
        foreach ($js_to_exclude as $string) {

            if (in_array($string, $options['delay_js_scripts'])) {
                unset($options['delay_js_scripts'][array_search($string, $options['delay_js_scripts'])]);
                $update_options = true;
            }
        }

        // exclude_defer_js
        foreach ($js_to_exclude as $string) {

            if (!in_array($string, $options['exclude_defer_js'])) {

                array_push($options['exclude_defer_js'], $string);
                $update_options = true;
            }
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
}