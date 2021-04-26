<?php
/**
 * Plugin Name:  WooCommerce Conversion Tracking
 * Description:  Google Ads conversion value tracking for WooCommerce.
 * Author:       woopt
 * Plugin URI:   https://wordpress.org/plugins/woocommerce-google-adwords-conversion-tracking-tag/
 * Author URI:   https://woopt.com
 * Version:      1.9.6
 * License:      GPLv2 or later
 * Text Domain:  woocommerce-google-adwords-conversion-tracking-tag
 * WC requires at least: 2.6
 * WC tested up to: 5.2
 *
 * @fs_premium_only /classes/pixels/bing/, /classes/pixels/twitter/, /classes/pixels/pinterest/, /classes/pixels/facebook/class-facebook-microdata.php, /classes/pixels/google/class-google-analytics-4-eec-pixel.php, /classes/pixels/google/class-google-analytics-ua-eec-pixel.php, /classes/pixels/google/class-google-analytics-ua-refund-pixel.php, /classes/http/
 *t
 **/

// TODO export settings function
// TODO add option checkbox on uninstall and ask if user wants to delete options from db
// TODO ask inverse cookie approval. Only of cookies have been allowed, fire the pixels.
// TODO remove google_business_vertical cleanup


use WGACT\Classes\Admin\Admin;
use WGACT\Classes\Admin\Ask_For_Rating;
use WGACT\Classes\Admin\Environment_Check;
use WGACT\Classes\Db_Upgrade;
use WGACT\Classes\Pixels\Cookie_Consent_Management;
use WGACT\Classes\Pixels\Pixel_Manager;


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (function_exists('wga_fs')) {
    wga_fs()->set_basename(true, __FILE__);
} else {
    // DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE `function_exists` CALL ABOVE TO PROPERLY WORK.

    if (!function_exists('wga_fs')) {
        // Create a helper function for easy SDK access.
        function wga_fs()
        {
            global $wga_fs;

            if (!isset($wga_fs)) {
                // Include Freemius SDK.
                require_once dirname(__FILE__) . '/freemius/start.php';

                $wga_fs = fs_dynamic_init([
                    'navigation'          => 'tabs',
                    'id'                  => '7498',
                    'slug'                => 'woocommerce-google-adwords-conversion-tracking-tag',
                    'premium_slug'        => 'woopt-pixel-manager-pro',
                    'type'                => 'plugin',
                    'public_key'          => 'pk_d4182c5e1dc92c6032e59abbfdb91',
                    'is_premium'          => true,
                    'premium_suffix'      => 'Pro',
                    // If your plugin is a serviceware, set this option to false.
                    'has_premium_version' => true,
                    'has_addons'          => false,
                    'has_paid_plans'      => true,
                    'trial'               => [
                        'days'               => 14,
                        'is_require_payment' => true,
                    ],
                    'menu'                => [
                        'slug'           => 'wgact',
                        'override_exact' => true,
                        'contact'        => false,
                        'support'        => false,
                        'parent'         => [
                            'slug' => 'woocommerce',
                        ],
                    ],
                    // Set the SDK to work in a sandbox mode (for development & testing).
                    // IMPORTANT: MAKE SURE TO REMOVE SECRET KEY BEFORE DEPLOYMENT.
                    'secret_key'          => 'sk_2t^o~F$B}(lo>en:M2TlvgTV@6ltK',
                ]);
            }

            return $wga_fs;
        }

        // Init Freemius.
        wga_fs();
        // Signal that SDK was initiated.
        do_action('wga_fs_loaded');

        function wga_fs_settings_url()
        {
            return admin_url('admin.php?page=wgact&section=main&subsection=google-ads');
        }

        wga_fs()->add_filter('connect_url', 'wga_fs_settings_url');
        wga_fs()->add_filter('after_skip_url', 'wga_fs_settings_url');
        wga_fs()->add_filter('after_connect_url', 'wga_fs_settings_url');
        wga_fs()->add_filter('after_pending_connect_url', 'wga_fs_settings_url');
    }

    // ... Your plugin's main file logic ...


    define('WGACT_PLUGIN_PREFIX', 'wgact_');
    define('WGACT_DB_VERSION', '3');
    define('WGACT_DB_OPTIONS_NAME', 'wgact_plugin_options');
    define('WGACT_DB_RATINGS', 'wgact_ratings');


    class WGACT
    {
        protected $options;

        public function __construct()
        {

//		$options = get_option(WGACT_DB_OPTIONS_NAME);
//		error_log(print_r($options, true));
//		$options['gads']['google_business_vertical'] = 'retail';
//		$options['google_business_vertical'] = 'XX';

//		$options['woopt'] = [];
//		$options['woopt']['existing'] = true;
//		$options['db_version'] = '2';
//		unset($options['google']['consent_mode']['activation']);
//		update_option(WGACT_DB_OPTIONS_NAME, $options);

//        $options = get_option('wgact_options_backup');
//        error_log(print_r($options, true));


            // check if WooCommerce is running
            // currently this is the most reliable test for single and multisite setups
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
            if (is_plugin_active('woocommerce/woocommerce.php')) {

                // autoloader
                require_once 'lib/autoload.php';

                $plugin_data    = get_file_data(__FILE__, ['Version' => 'Version'], false);
                $plugin_version = $plugin_data['Version'];
                define('WGACT_CURRENT_VERSION', $plugin_version);

                // running the DB updater
                if (get_option(WGACT_DB_OPTIONS_NAME)) {
                    (new Db_Upgrade)->run_options_db_upgrade();
                }

                // load the options
                $this->wgact_options_init();

                new \WGACT\Classes\Admin\Launch_Deal();

                if (isset($this->options['google']['gads']['dynamic_remarketing']) && $this->options['google']['gads']['dynamic_remarketing']) {
                    // make sure to disable the WGDR plugin in case we use dynamic remarketing in this plugin
                    add_filter('wgdr_third_party_cookie_prevention', '__return_true');
                }

                // run environment workflows
                add_action('admin_notices', [$this, 'run_admin_compatibility_checks']);
                (new Environment_Check())->permanent_compatibility_mode();
                $this->run_compatibility_modes();

                $this->init();
            }
        }

        private function run_compatibility_modes()
        {
            /*
            * Compatibility modes
            */
            if ($this->options['general']['maximum_compatibility_mode']) (new Environment_Check())->enable_maximum_compatibility_mode();

            if (
                $this->options['general']['maximum_compatibility_mode'] &&
                $this->options['facebook']['microdata']
            ) {
                (new Environment_Check())->enable_maximum_compatibility_mode_yoast_seo();
            }
        }

        // startup all functions
        public function init()
        {
            // display admin views
            new Admin();

            // ask visitor for rating
            new Ask_For_Rating();

            new Environment_Check();

            // add a settings link on the plugins page
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'wgact_settings_link']);

            // inject pixels into front end
            // in order to time it correctly so that the prevention filter works we need to use the after_setup_theme action
            // 	https://stackoverflow.com/a/19279650
            add_action('after_setup_theme', [$this, 'inject_pixels']);
        }

        public function inject_pixels()
        {
            // check if cookie prevention has been activated

            // load the cookie consent management functions
            $cookie_consent = new Cookie_Consent_Management();
            $cookie_consent->setPluginPrefix(WGACT_PLUGIN_PREFIX);

            if ($cookie_consent->is_cookie_prevention_active() == false) {

                // inject pixels
                new Pixel_Manager();
            }
        }

        public function run_admin_compatibility_checks()
        {
            (new Environment_Check())->run_checks();
        }

        // initialise the options
        private function wgact_options_init()
        {
            // set options equal to defaults
//            global $wgact_plugin_options;
            $this->options = get_option(WGACT_DB_OPTIONS_NAME);

            if (false === $this->options) { // if no options have been set yet, initiate default options

                // launch deal prep
                $wooptpm_launch_deal = [
                    'eligible'   => false,
                    'dismissed'  => false,
                    'later'      => false,
                    'later_date' => ''
                ];

                update_option('wooptpm_launch_deal', $wooptpm_launch_deal);

//            error_log('options empty, loading default');
                $this->options = $this->wgact_get_default_options();
                update_option(WGACT_DB_OPTIONS_NAME, $this->options);

//            $options = get_option(WGACT_DB_OPTIONS_NAME);
//		    error_log(print_r($options, true));

            } else {  // Check if each single option has been set. If not, set them. That is necessary when new options are introduced.

                if (get_option('wooptpm_launch_deal') === false) {
                    // launch deal prep
                    $wooptpm_launch_deal = [
                        'eligible'   => true,
                        'dismissed'  => false,
                        'later'      => false,
                        'later_date' => ''
                    ];

                    update_option('wooptpm_launch_deal', $wooptpm_launch_deal);
                }

                // cleanup the db of this setting
                // remove by end of 2021 latest
                if (array_key_exists('google_business_vertical', $this->options)) {
                    unset($this->options['google_business_vertical']);
                }

                // cleanup the db of this setting
                // remove by end of 2021 latest
                // accidentally had this dummy id left in the default options in 1.7.13
                if ($this->options['facebook']['pixel_id'] === '767038516805171') {
                    $this->options['facebook']['pixel_id'] = '';
                }

                // add new default options to the options db array
                $this->options = $this->update_with_defaults($this->options, $this->wgact_get_default_options());
                update_option(WGACT_DB_OPTIONS_NAME, $this->options);
            }
        }

        protected function update_with_defaults($array_input, $array_default)
        {
            foreach ($array_default as $key => $value) {
                if (array_key_exists($key, $array_input)) {
                    if (is_array($value)) {
                        $array_input[$key] = $this->update_with_defaults($array_input[$key], $value);
                    }
                } else {
                    $array_input[$key] = $value;
                }
            }

            return $array_input;
        }

        // get the default options
        private function wgact_get_default_options(): array
        {
            // default options settings
            return [
                'google'     => [
                    'ads'          => [
                        'conversion_id'            => '',
                        'conversion_label'         => '',
                        'aw_merchant_id'           => '',
                        'product_identifier'       => 0,
                        'google_business_vertical' => 0,
                        'dynamic_remarketing'      => 0,
                        'phone_conversion_number'  => '',
                        'phone_conversion_label'   => '',
                    ],
                    'analytics'    => [
                        'universal'        => [
                            'property_id' => '',
                        ],
                        'ga4'              => [
                            'measurement_id' => '',
                            'api_secret'     => '',
                        ],
                        'eec'              => 0,
                        'link_attribution' => 0,
                    ],
                    'optimize'     => [
                        'container_id' => '',
                    ],
                    'gtag'         => [
                        'deactivation' => 0,
                    ],
                    'consent_mode' => [
                        'active'  => 0,
                        'regions' => [],
                    ],
                    'user_id'      => 0,
                ],
                'facebook'   => [
                    'pixel_id'   => '',
                    'capi_token' => '',
                    'microdata'  => 0,
                ],
                'bing'       => [
                    'uet_tag_id' => ''
                ],
                'twitter'    => [
                    'pixel_id' => ''
                ],
                'pinterest'  => [
                    'pixel_id' => ''
                ],
                'hotjar'     => [
                    'site_id' => ''
                ],
                'shop'       => [
                    'order_total_logic'   => 0,
                    'cookie_consent_mgmt' => [
                        'cookiebot' => [
                            'active' => 0
                        ],
                    ],
                    'order_deduplication' => 1
                ],
                'general'    => [
                    'variations_output'          => 1,
                    'maximum_compatibility_mode' => 0,
                    'pro_version_demo'           => 0,
                ],
                'db_version' => WGACT_DB_VERSION,
            ];
        }

        // adds a link on the plugins page for the wgdr settings
        // ! Can't be required. Must be in the main plugin file.
        public function wgact_settings_link($links)
        {
            $links[] = '<a href="' . admin_url('admin.php?page=wgact') . '">Settings</a>';

            return $links;
        }
    }

    new WGACT();
}