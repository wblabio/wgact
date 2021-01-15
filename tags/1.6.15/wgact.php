<?php
/**
 * Plugin Name:  WooCommerce Google Ads Conversion Tracking
 * Description:  Google Ads dynamic conversion value tracking for WooCommerce.
 * Author:       Wolf+Bär Agency
 * Plugin URI:   https://wordpress.org/plugins/woocommerce-google-adwords-conversion-tracking-tag/
 * Author URI:   https://wolfundbaer.ch
 * Version:      1.6.15
 * License:      GPLv2 or later
 * Text Domain:  woocommerce-google-adwords-conversion-tracking-tag
 * WC requires at least: 2.6
 * WC tested up to: 4.8
 **/

// TODO in case Google starts to use alphabetic characters in the conversion ID, output the conversion ID with ''
// TODO give users choice to use content or footer based code insertion
// TODO only run if WooCommerce is active
// TODO make this class a singleton
// TODO don't run if minimum versions of PHP, WordPress and WooCommerce are not met, and issue a warning notification
// TODO implement cookiebot support


if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

if ( ! function_exists( 'wga_fs' ) ) {
	// Create a helper function for easy SDK access.
	function wga_fs() {
		global $wga_fs;

		if ( ! isset( $wga_fs ) ) {
			// Include Freemius SDK.
			require_once dirname(__FILE__) . '/freemius/start.php';

			$wga_fs = fs_dynamic_init( array(
				'id'                  => '7498',
				'slug'                => 'woocommerce-google-adwords-conversion-tracking-tag',
				'premium_slug'        => 'wgact-premium',
				'type'                => 'plugin',
				'public_key'          => 'pk_d4182c5e1dc92c6032e59abbfdb91',
				'is_premium'          => false,
				'has_addons'          => false,
				'has_paid_plans'      => false,
				'menu'                => array(
					'slug'           => 'wgact',
					'override_exact' => true,
					'account'        => false,
					'contact'        => false,
					'support'        => false,
					'parent'         => array(
						'slug' => 'woocommerce',
					),
				),
			) );
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

	wga_fs()->add_filter('connect_url', 'wga_fs_settings_url');
	wga_fs()->add_filter('after_skip_url', 'wga_fs_settings_url');
	wga_fs()->add_filter('after_connect_url', 'wga_fs_settings_url');
	wga_fs()->add_filter('after_pending_connect_url', 'wga_fs_settings_url');
}

class WGACT
{
	const PLUGIN_PREFIX = 'wgact_';

	public function __construct()
	{
		if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

			if ( is_admin() ) {
				$plugin_data    = get_file_data( __FILE__, [ 'Version' => 'Version' ], false );
				$plugin_version = $plugin_data['Version'];
				define( 'WGACT_CURRENT_VERSION', $plugin_version );
			}

			// preparing the DB check and upgrade routine
			require_once plugin_dir_path( __FILE__ ) . 'includes/class-db-upgrade.php';

			// running the DB updater
			( new WgactDbUpgrade )->run_options_db_upgrade();

			// $this->runCookieConsentManagement();

			// run the following function after_setup_theme in order to allow
			// the cookie_prevention filter to be used in functions.php
			// https://stackoverflow.com/a/19279650
			add_action( 'after_setup_theme', [ $this, 'runCookieConsentManagement' ] );
		}
	}

	public function runCookieConsentManagement()
	{

		require_once plugin_dir_path(__FILE__) . 'includes/class-cookie-consent-management.php';

		// load the cookie consent management functions
		$cookie_consent = new WgactCookieConsentManagement();
		$cookie_consent->setPluginPrefix(self::PLUGIN_PREFIX);

		// check if third party cookie prevention has been requested
		// if not, run the plugin
		if ($cookie_consent->is_cookie_prevention_active() == false) {

			// startup main plugin functions
			$this->init();

		} else {
//			error_log('third party cookie prevention active');
		}
	}

	// startup all functions
	public function init()
	{

		// load the options
		$this->wgact_options_init();

		require_once plugin_dir_path(__FILE__) . 'includes/class-ask-for-rating.php';
		require_once plugin_dir_path(__FILE__) . 'includes/class-gtag.php';
		require_once plugin_dir_path(__FILE__) . 'includes/class-pixel.php';
		require_once plugin_dir_path(__FILE__) . 'admin/class-admin.php';

		// display admin views
		(new WgactAdmin())->init();

		// ask visitor for rating
		(new WgactAskForRating)->init();

		// add the Google Ads tag to the thankyou part of the page within the body tags
		add_action('wp_head', function(){
			(new WgactPixel)->GoogleAdsTag();
		});

		// add a settings link on the plugins page
		add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'wgact_settings_link']);
	}

	// initialise the options
	private function wgact_options_init()
	{

		// set options equal to defaults
		global $wgact_plugin_options;
		$wgact_plugin_options = get_option('wgact_plugin_options');

		if (false === $wgact_plugin_options) {

			$wgact_plugin_options = $this->wgact_get_default_options();
			update_option('wgact_plugin_options', $wgact_plugin_options);

		} else {  // Check if each single option has been set. If not, set them. That is necessary when new options are introduced.

			// get default plugins options
			$wgact_default_plugin_options = $this->wgact_get_default_options();

			// go through all default options an find out if the key has been set in the current options already
			foreach ($wgact_default_plugin_options as $key => $value) {

				// Test if the key has been set in the options already
				if (!array_key_exists($key, $wgact_plugin_options)) {

					// set the default key and value in the options table
					$wgact_plugin_options[$key] = $value;

					// update the options table with the new key
					update_option('wgact_plugin_options', $wgact_plugin_options);

				}
			}
		}
	}

	// get the default options
	private function wgact_get_default_options()
	{
		// default options settings
		$options = [
			'conversion_id'      => '',
			'conversion_label'   => '',
			'order_total_logic'  => 0,
			'gtag_deactivation'  => 0,
			'add_cart_data'      => 0,
			'aw_merchant_id'     => '',
			'product_identifier' => 0,
		];

		return $options;
	}

	// adds a link on the plugins page for the wgdr settings
	// ! Can't be required. Must be in the main plugin file.
	public function wgact_settings_link($links)
	{
		$links[] = '<a href="' . admin_url('admin.php?page=wgact') . '">Settings</a>';
		return $links;
	}
}

$wgact = new WGACT();