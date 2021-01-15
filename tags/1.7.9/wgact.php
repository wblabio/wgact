<?php
/**
 * Plugin Name:  WooCommerce Google Ads Conversion Tracking
 * Description:  Google Ads dynamic conversion value tracking for WooCommerce.
 * Author:       Wolf+Bär Agency
 * Plugin URI:   https://wordpress.org/plugins/woocommerce-google-adwords-conversion-tracking-tag/
 * Author URI:   https://wolfundbaer.ch
 * Version:      1.7.9
 * License:      GPLv2 or later
 * Text Domain:  woocommerce-google-adwords-conversion-tracking-tag
 * WC requires at least: 2.6
 * WC tested up to: 4.8
 **/

// TODO give users choice to use content or footer based code insertion
// TODO make this class a singleton
// TODO don't run if minimum versions of PHP, WordPress and WooCommerce are not met, and issue a warning notification
// TODO use namespaces
// TODO export settings function
// TODO add option checkbox on uninstall and ask if user wants to delete options from db
// TODO ask inverse cookie approval. Only of cookies have been allowed, fire the pixels.
// TODO force init gtag if it was deactivated but not initialized in the correct sequence on the website
// TODO remove google_business_vertical cleanup


if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! function_exists( 'wga_fs' ) ) {
    // Create a helper function for easy SDK access.
    function wga_fs() {
        global $wga_fs;

        if ( ! isset( $wga_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';

            $wga_fs = fs_dynamic_init( [
                'id'             => '7498',
                'slug'           => 'woocommerce-google-adwords-conversion-tracking-tag',
                'premium_slug'   => 'wgact-premium',
                'type'           => 'plugin',
                'public_key'     => 'pk_d4182c5e1dc92c6032e59abbfdb91',
                'is_premium'     => false,
                'has_addons'     => false,
                'has_paid_plans' => false,
                'menu'           => [
                    'slug'           => 'wgact',
                    'override_exact' => true,
                    'account'        => false,
                    'contact'        => false,
                    'support'        => false,
                    'parent'         => [
                        'slug' => 'woocommerce',
                    ],
                ],
            ] );
        }

        return $wga_fs;
    }

    // Init Freemius.
    wga_fs();
    // Signal that SDK was initiated.
    do_action( 'wga_fs_loaded' );

    function wga_fs_settings_url() {
        return admin_url( 'admin.php?page=wgact&section=main&subsection=google-ads' );
    }

    wga_fs()->add_filter( 'connect_url', 'wga_fs_settings_url' );
    wga_fs()->add_filter( 'after_skip_url', 'wga_fs_settings_url' );
    wga_fs()->add_filter( 'after_connect_url', 'wga_fs_settings_url' );
    wga_fs()->add_filter( 'after_pending_connect_url', 'wga_fs_settings_url' );
}

define( 'WGACT_PLUGIN_PREFIX', 'wgact_' );
define( 'WGACT_DB_VERSION', '2' );
define( 'WGACT_DB_OPTIONS_NAME', 'wgact_plugin_options' );
define( 'WGACT_DB_RATINGS', 'wgact_ratings' );


class WGACT {

    protected $options;

    public function __construct() {

//		$options = get_option(WGACT_DB_OPTIONS_NAME);
//		$options['gads']['google_business_vertical'] = 'retail';
//		$options['google_business_vertical'] = 'XX';

//		$options['woopt'] = [];
//		$options['woopt']['existing'] = true;
//		$options['db_version'] = '2';
//		unset($options['2']);
//		update_option(WGACT_DB_OPTIONS_NAME, $options);


        // check if WooCommerce is running
        // currently this is the most reliable test for single and multisite setups
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        if(	is_plugin_active( 'woocommerce/woocommerce.php' ) ){

            if ( is_admin() ) {
                $plugin_data    = get_file_data( __FILE__, [ 'Version' => 'Version' ], false );
                $plugin_version = $plugin_data['Version'];
                define( 'WGACT_CURRENT_VERSION', $plugin_version );
            }

            // preparing the DB check and upgrade routine
            require_once plugin_dir_path( __FILE__ ) . 'includes/class-db-upgrade.php';

            // running the DB updater
            if ( get_option( WGACT_DB_OPTIONS_NAME ) ) {

                ( new WgactDbUpgrade )->run_options_db_upgrade();
            }

            // load the options
            $this->wgact_options_init();
//            error_log('dr test');
            if(isset($this->options['gads']['dynamic_remarketing']) && $this->options['gads']['dynamic_remarketing']){
                add_filter('wgdr_third_party_cookie_prevention', '__return_true');
//                error_log('dr test');
            }

            $this->init();
        }
    }

    // startup all functions
    public function init() {

        require_once plugin_dir_path( __FILE__ ) . 'includes/class-ask-for-rating.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-pixel-gtag.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-pixel-google-ads.php';
        require_once plugin_dir_path( __FILE__ ) . 'admin/class-admin.php';

        // display admin views
        ( new WgactAdmin() )->init();

        // ask visitor for rating
        ( new WgactAskForRating )->init();

        // add a settings link on the plugins page
        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'wgact_settings_link' ] );

        // inject pixels into front end
        // in order to time it correctly so that the prevention filter works we need to use the after_setup_theme action
        // 	https://stackoverflow.com/a/19279650
        add_action( 'after_setup_theme', [ $this, 'inject_pixels' ] );
    }

    public function inject_pixels(){

        // check if cookie prevention has been activated
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-cookie-consent-management.php';

        // load the cookie consent management functions
        $cookie_consent = new WgactCookieConsentManagement();
        $cookie_consent->setPluginPrefix( WGACT_PLUGIN_PREFIX );

        if ( $cookie_consent->is_cookie_prevention_active() == false ) {

            // add the Google Ads tag to the thankyou part of the page within the body tags
            add_action( 'wp_head', function () {
                ( new WgactPixel )->GoogleAdsTag();
            } );
        }
    }

    // initialise the options
    private function wgact_options_init() {
        // set options equal to defaults
//		global $wgact_plugin_options;
        $this->options = get_option( WGACT_DB_OPTIONS_NAME );

        if ( false === $this->options ) { // of no options have been set yet, initiate default options

            update_option( WGACT_DB_OPTIONS_NAME, $this->wgact_get_default_options() );

        } else {  // Check if each single option has been set. If not, set them. That is necessary when new options are introduced.

            // cleanup the db of this setting
            // remove by end of 2021 latest
            if(array_key_exists('google_business_vertical', $this->options)){
                unset($this->options['google_business_vertical']);
            }

            // add new default options to the options db array
            update_option( WGACT_DB_OPTIONS_NAME, $this->update_with_defaults( $this->options, $this->wgact_get_default_options() ) );
        }
    }

    protected function update_with_defaults( $array_input, $array_default ) {

        foreach ( $array_default as $key => $value ) {
            if ( array_key_exists( $key, $array_input ) ) {
                if ( is_array( $value ) ) {
                    $array_input[ $key ] = $this->update_with_defaults( $array_input[ $key ], $value );
                }
            } else {
                $array_input[ $key ] = $value;
            }
        }

        return $array_input;
    }

    // get the default options
    private function wgact_get_default_options(): array {
        // default options settings
        return [
            'gads'       => [
                'conversion_id'            => '',
                'conversion_label'         => '',
                'order_total_logic'        => 0,
                'add_cart_data'            => 0,
                'aw_merchant_id'           => '',
                'product_identifier'       => 0,
                'google_business_vertical' => 'retail',
                'dynamic_remarketing'      => 0
            ],
            'gtag'       => [
                'deactivation' => 0,
            ],
            'db_version' => WGACT_DB_VERSION,
        ];
    }

    // adds a link on the plugins page for the wgdr settings
    // ! Can't be required. Must be in the main plugin file.
    public function wgact_settings_link( $links ) {
        $links[] = '<a href="' . admin_url( 'admin.php?page=wgact' ) . '">Settings</a>';

        return $links;
    }
}

new WGACT();