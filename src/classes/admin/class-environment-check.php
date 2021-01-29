<?php

namespace WGACT\Classes\Admin;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Environment_Check
{
    public function __construct()
    {
        add_action( 'admin_enqueue_scripts', [$this, 'environment_check_script'] );
        add_action( 'wp_ajax_environment_check_handler', [$this, 'ajax_environment_check_handler'] );
        add_action('admin_notices', [$this, 'run_checks']);
    }

    public function run_checks()
    {
        $this->check_wp_rocket_js_concatenation();
    }

    public function environment_check_script()
    {
        wp_enqueue_script(
            'environment-check', // Handle
            plugin_dir_url( __DIR__ ) . '../js/admin/environment-check.js',
            [ 'jquery' ],
            WGACT_CURRENT_VERSION,
            true
        );
    }

    public function ajax_environment_check_handler() {
        $set = $_POST['set'];

        if('disable_wp_rocket_javascript_concatenation' == $set){
            $wp_rocket_options = get_option('wp_rocket_settings');
            $wp_rocket_options['minify_concatenate_js'] = 0;
            update_option('wp_rocket_settings', $wp_rocket_options);
        }

        if('dismiss_wp_rocket_javascript_concatenation_error' == $set){
            $wgact_notifications = get_option('wgact_notifications');
            $wgact_notifications['dismiss_wp_rocket_javascript_concatenation_error'] = true;
            update_option('wgact_notifications', $wgact_notifications);
        }

        wp_die(); // this is required to terminate immediately and return a proper response
    }

    private function check_wp_rocket_js_concatenation()
    {
        $wgact_notifications = get_option('wgact_notifications');

        if (is_plugin_active('wp-rocket/wp-rocket.php') && false == $wgact_notifications['dismiss_wp_rocket_javascript_concatenation_error']) {

            $wp_rocket_settings = get_option('wp_rocket_settings');

            if ($wp_rocket_settings) {
                if (true == $wp_rocket_settings['minify_concatenate_js']) {
                    // display warning
                    (new Notifications())->wp_rocket_js_concatenation_error();
                }
            }
        }
    }
}