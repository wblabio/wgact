<?php

// TODO move script for copying debug info into a proper .js enqueued file, or switch tabs to JavaScript switching and always save all settings at the same time
// TODO debug info list of active payment gateways

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WgactAdmin {

	public    $ip;
	protected $text_domain;
	protected $options;
	protected $plugin_hook;

	public function init() {

		$this->plugin_hook = 'woocommerce_page_wgact';

		$this->options = get_option( 'wgact_plugin_options' );

//		error_log(print_r($this->options, true));

		add_action( 'admin_enqueue_scripts', [$this,'wgact_admin_scripts'] );

		// add the admin options page
		add_action( 'admin_menu', [ $this, 'wgact_plugin_admin_add_page' ], 99 );

		// install a settings page in the admin console
		add_action( 'admin_init', [ $this, 'wgact_plugin_admin_init' ] );

		// Load textdomain
		add_action( 'init', [ $this, 'load_plugin_textdomain' ] );
	}

	public function wgact_admin_scripts($hook)
    {
	    if ( $this->plugin_hook != $hook ) {
		    return;
	    }
	    wp_enqueue_script( 'admin-helpers', plugin_dir_url( __DIR__ ) . 'js/admin-helpers.js', array(), WGACT_CURRENT_VERSION, true );
	    wp_enqueue_script( 'admin-tabs', plugin_dir_url( __DIR__ ) . 'js/admin-tabs.js', array(), WGACT_CURRENT_VERSION, true );

	    wp_enqueue_style( 'admin-css', plugin_dir_url( __DIR__ ) . 'css/admin.css', array(), WGACT_CURRENT_VERSION );
    }

	// Load text domain function
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'woocommerce-google-adwords-conversion-tracking-tag', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	// add the admin options page
	public function wgact_plugin_admin_add_page() {
		//add_options_page('WGACT Plugin Page', 'WGACT Plugin Menu', 'manage_options', 'wgact', array($this, 'wgact_plugin_options_page'));
		add_submenu_page(
			'woocommerce',
			esc_html__( 'Google Ads Conversion Tracking', 'woocommerce-google-adwords-conversion-tracking-tag' ),
			esc_html__( 'Google Ads Conversion Tracking', 'woocommerce-google-adwords-conversion-tracking-tag' ),
			'manage_options',
			'wgact',
			[ $this, 'wgact_plugin_options_page' ]
		);
	}

	// add the admin settings and such
	public function wgact_plugin_admin_init() {

		register_setting(
			'wgact_plugin_options_group',
			'wgact_plugin_options',
			[ $this, 'wgact_options_validate' ]
		);

		$this->add_section_main();
		$this->add_section_advanced();
		$this->add_section_beta();
		$this->add_section_support();
		$this->add_section_author();
	}

	public function add_section_main() {

	    $section_ids = [
	      'title' => 'Main',
          'slug' => 'main',
          'settings_name' => 'wgact_plugin_main_section',
        ];

		$this->output_section_data_field( $section_ids );

		add_settings_section(
			$section_ids['settings_name'],
			esc_html__( $section_ids['title'], 'woocommerce-google-adwords-conversion-tracking-tag' ),
			[ $this, 'wgact_plugin_section_main_description' ],
			'wgact_plugin_options_page'
		);

		$this->add_section_main_subsection_google_ads($section_ids);
//		$this->add_section_main_subsection_facebook($section_ids);
	}

	public function add_section_main_subsection_google_ads($section_ids){

	    $sub_section_ids = [
	            'title' => 'Google Ads',
                'slug' => 'google-ads'
        ];

		add_settings_field(
			'wgact_plugin_subsection_' . $sub_section_ids['slug'] . '_opening_div',
			esc_html__(
				$sub_section_ids['title'],
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			function() use ($section_ids, $sub_section_ids) {
			    $this->wgact_subsection_generic_opening_div_html($section_ids, $sub_section_ids);
            },
			'wgact_plugin_options_page',
			$section_ids['settings_name']
		);

		// add the field for the conversion id
		add_settings_field(
			'wgact_plugin_conversion_id',
			esc_html__(
				'Conversion ID',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[ $this, 'wgact_plugin_setting_conversion_id' ],
			'wgact_plugin_options_page',
			$section_ids['settings_name']
		);

		// add the field for the conversion label
		add_settings_field(
			'wgact_plugin_conversion_label',
			esc_html__(
				'Conversion Label',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[ $this, 'wgact_plugin_setting_conversion_label' ],
			'wgact_plugin_options_page',
			$section_ids['settings_name']
		);
    }

	public function add_section_main_subsection_facebook($section_ids){

		$sub_section_ids = [
			'title' => 'Facebook',
			'slug' => 'facebook'
		];

		add_settings_field(
			'wgact_plugin_subsection_' . $sub_section_ids['slug'] . '_opening_div',
			esc_html__(
				$sub_section_ids['title'],
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			function() use ($section_ids, $sub_section_ids) {
				$this->wgact_subsection_generic_opening_div_html($section_ids, $sub_section_ids);
			},
			'wgact_plugin_options_page',
			$section_ids['settings_name']
		);

		// add the field for the conversion label
		add_settings_field(
			'wgact_plugin_facebook_id',
			esc_html__(
				'Facebook ID',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[ $this, 'wgact_plugin_setting_conversion_label' ],
			'wgact_plugin_options_page',
			$section_ids['settings_name']
		);

	}

	public function add_section_advanced() {

		$section_ids = [
			'title' => 'Advanced',
			'slug' => 'advanced',
			'settings_name' => 'wgact_plugin_advanced_section',
		];

		add_settings_section(
			$section_ids['settings_name'],
			esc_html__(
				$section_ids['title'],
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[ $this, 'wgact_plugin_section_advanced_description' ],
			'wgact_plugin_options_page'
		);

		$this->output_section_data_field( $section_ids );

		$this->add_section_advanced_subsection_order_logic($section_ids);
		$this->add_section_advanced_subsection_gtag($section_ids);
	}

	public function add_section_advanced_subsection_order_logic($section_ids){

		$sub_section_ids = [
			'title' => 'Order Logic',
			'slug' => 'order-logic'
		];

		add_settings_field(
			'wgact_plugin_subsection_' . $sub_section_ids['slug'] . '_opening_div',
			esc_html__(
				$sub_section_ids['title'],
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			function() use ($section_ids, $sub_section_ids) {
				$this->wgact_subsection_generic_opening_div_html($section_ids, $sub_section_ids);
			},
			'wgact_plugin_options_page',
			$section_ids['settings_name']
		);

		// add fields for the order total logic
		add_settings_field(
			'wgact_plugin_order_total_logic',
			esc_html__(
				'Order Total Logic',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[ $this, 'wgact_plugin_setting_order_total_logic' ],
			'wgact_plugin_options_page',
			$section_ids['settings_name']
		);

    }

	public function add_section_advanced_subsection_gtag($section_ids){

		$sub_section_ids = [
			'title' => 'Gtag',
			'slug' => 'gtag'
		];

		add_settings_field(
			'wgact_plugin_subsection_' . $sub_section_ids['slug'] . '_opening_div',
			esc_html__(
				$sub_section_ids['title'],
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			function() use ($section_ids, $sub_section_ids) {
				$this->wgact_subsection_generic_opening_div_html($section_ids, $sub_section_ids);
			},
			'wgact_plugin_options_page',
			$section_ids['settings_name']
		);

		// add fields for the gtag insertion
		add_settings_field(
			'wgact_plugin_gtag',
			esc_html__(
				'gtag Deactivation',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[ $this, 'wgact_plugin_setting_gtag_deactivation' ],
			'wgact_plugin_options_page',
			$section_ids['settings_name']
		);
	}

	public function add_section_beta() {

		$section_ids = [
			'title' => 'Beta',
			'slug' => 'beta',
			'settings_name' => 'wgact_plugin_beta_section',
		];

		$this->output_section_data_field( $section_ids );

		// add new section for cart data
		add_settings_section(
			'wgact_plugin_beta_section',
			esc_html__(
				'Beta',
				'woocommerce-google-adwords-conversion-tracking-tag'
            ),
			[ $this, 'wgact_plugin_section_add_cart_data_description' ],
			'wgact_plugin_options_page'
		);

		// add fields for cart data
		add_settings_field(
			'wgact_plugin_add_cart_data',
			esc_html__(
				'Activation',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[ $this, 'wgact_plugin_setting_add_cart_data' ],
			'wgact_plugin_options_page',
			'wgact_plugin_beta_section'
		);

		// add the field for the aw_merchant_id
		add_settings_field(
			'wgact_plugin_aw_merchant_id',
			esc_html__(
				'aw_merchant_id',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[ $this, 'wgact_plugin_setting_aw_merchant_id' ],
			'wgact_plugin_options_page',
			'wgact_plugin_beta_section'
		);

		// add the field for the aw_feed_country
		add_settings_field(
			'wgact_plugin_aw_feed_country',
			esc_html__(
				'aw_feed_country',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[ $this, 'wgact_plugin_setting_aw_feed_country' ],
			'wgact_plugin_options_page',
			'wgact_plugin_beta_section'
		);

		// add the field for the aw_feed_language
		add_settings_field(
			'wgact_plugin_aw_feed_language',
			esc_html__(
				'aw_feed_language',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[ $this, 'wgact_plugin_setting_aw_feed_language' ],
			'wgact_plugin_options_page',
			'wgact_plugin_beta_section'
		);

		// add fields for the product identifier
		add_settings_field(
			'wgact_plugin_option_product_identifier',
			esc_html__(
				'Product Identifier',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[ $this, 'wgact_plugin_option_product_identifier' ],
			'wgact_plugin_options_page',
			'wgact_plugin_beta_section'
		);
	}

	public function add_section_support() {

		$section_ids = [
			'title' => 'Support',
			'slug' => 'support',
			'settings_name' => 'wgact_plugin_support_section',
		];

		$this->output_section_data_field( $section_ids );

		add_settings_section(
			'wgact_plugin_support_section',
			esc_html__( 'Support', 'woocommerce-google-adwords-conversion-tracking-tag' ),
			[ $this, 'wgact_plugin_section_support_description' ],
			'wgact_plugin_options_page'
		);
	}

	public function add_section_author(){

		$section_ids = [
			'title' => 'Author',
			'slug' => 'author',
			'settings_name' => 'wgact_plugin_author_section',
		];

		$this->output_section_data_field( $section_ids );

		add_settings_section(
			'wgact_plugin_author_section',
			esc_html__( 'Author', 'woocommerce-google-adwords-conversion-tracking-tag' ),
			[ $this, 'wgact_plugin_section_author_description' ],
			'wgact_plugin_options_page'
		);
	}

	protected function output_section_data_field( array $section_ids ) {
		add_settings_field(
			'wgact_plugin_section_' . $section_ids['slug'] . '_opening_div',
			'',
			function () use ( $section_ids ) {
				$this->wgact_section_generic_opening_div_html( $section_ids );
			},
			'wgact_plugin_options_page',
			$section_ids['settings_name']
		);
	}

	public function wgact_section_generic_opening_div_html($section_ids) {
		echo '<div class="section" data-section-title="' . $section_ids['title'] . '" data-section-slug="' . $section_ids['slug'] . '"></div>';
	}

	public function wgact_subsection_generic_opening_div_html($section_ids, $sub_section_ids) {
		echo '<div class="subsection" data-section-slug="' . $section_ids['slug'] . '" data-subsection-title="' . $sub_section_ids['title'] . '" data-subsection-slug="' . $sub_section_ids['slug'] . '"></div>';
	}

	// display the admin options page
	public function wgact_plugin_options_page() {
		?>
        <div style="width:90%; float: left; margin: 5px">
				<?php settings_errors(); ?>

                <h2 class="nav-tab-wrapper">
                </h2>

                <form id="wgact_settings_form" action="options.php" method="post">

					<?php

					settings_fields( 'wgact_plugin_options_group' );
					do_settings_sections( 'wgact_plugin_options_page' );
					submit_button();
					?>

                </form>

                <div style="background: #0073aa; padding: 10px; font-weight: bold; color: white; margin-bottom: 20px; border-radius: 3px">
					<span>
						<?php
						/* translators: 'Wolf+Bär' needs to always stay the same*/
						esc_html_e( 'Profit Driven Marketing by Wolf+Bär', 'woocommerce-google-adwords-conversion-tracking-tag' );
                        ?>
					</span>
                    <span style="float: right; padding-left: 20px">
							<?php esc_html_e( 'Visit us here:', 'woocommerce-google-adwords-conversion-tracking-tag' ) ?>
                        <a href="https://wolfundbaer.ch/<?php echo $this->is_german_locale() ? 'de' : 'en'; ?>/?utm_source=plugin&utm_medium=banner&utm_campaign=wgact_plugin" target="_blank" style="color: white">https://wolfundbaer.ch
						</a>
					</span>
                </div>
        </div>
		<?php
	}

	private function is_german_locale(): bool {

	    if(substr(get_user_locale(), 0, 2) === 'de') {
	        return true;
        } else {
    	    return false;
        }
	}

	/*
	 * descriptions
	 */

	public function wgact_plugin_section_main_description() {
		// do nothing
	}

	public function wgact_plugin_section_advanced_description() {
		// do nothing
	}

	public function wgact_plugin_section_add_cart_data_description() {
	    echo '<div id="beta-description" style="margin-top:20px">';

		esc_html_e( 'Find out more about this new feature: ', 'woocommerce-google-adwords-conversion-tracking-tag' );
		echo '<a href="https://support.google.com/google-ads/answer/9028254" target="_blank">https://support.google.com/google-ads/answer/9028254</a><br>';
		esc_html_e( 'At the moment we are testing this feature. It might go into a PRO version of this plugin in the future.', 'woocommerce-google-adwords-conversion-tracking-tag' );
	    echo '</div>';
	}

	public function wgact_plugin_section_support_description() {
		?>
        <div style="margin-top:20px">
			<?php esc_html_e( 'Use the following two resources for support: ', 'woocommerce-google-adwords-conversion-tracking-tag' ); ?>
        </div>
        <div style="margin-bottom: 30px;">
            <ul>

                <li>
					<?php esc_html_e( 'Post a support request in the WordPress support forum here: ', 'woocommerce-google-adwords-conversion-tracking-tag' ); ?>
                    <a href="https://wordpress.org/support/plugin/woocommerce-google-adwords-conversion-tracking-tag/"
                       target="_blank">https://wordpress.org/support/plugin/woocommerce-google-adwords-conversion-tracking-tag/</a>
                </li>
                <li>
					<?php esc_html_e( 'Or send us an email to the following address: ', 'woocommerce-google-adwords-conversion-tracking-tag' ); ?>
                    <a href="mailto:support@wolfundbaer.ch" target="_blank">support@wolfundbaer.ch</a>
                </li>
            </ul>
        </div>
        <div class=" woocommerce-message">

            <div >
                <textarea id="debug-info-textarea" class="" style="display:block; margin-bottom: 10px; width: 100%;resize: none;color:dimgrey;" cols="100%" rows="30"
                          readonly><?php echo $this->get_debug_info() ?></textarea>
                <button id="debug-info-button" type="button"><?php esc_html_e('copy to clipboard', 'woocommerce-google-adwords-conversion-tracking-tag' ); ?></button>
            </div>

        </div>

		<?php
	}

	public function wgact_plugin_section_author_description() {
		?>
            <div style="margin-top:20px;margin-bottom: 30px">
                <?php esc_html_e( 'More details about the developer of this plugin: ', 'woocommerce-google-adwords-conversion-tracking-tag' ); ?>
            </div>
            <div style="margin-bottom: 30px;">
                <div><?php
	                /* translators: 'Wolf+Bär' needs to always stay the same, while 'Agency' can be translated */
                    esc_html_e( 'Developer: Wolf+Bär Agency', 'woocommerce-google-adwords-conversion-tracking-tag' );
                    ?></div>
                <div><?php esc_html_e( 'Website: ', 'woocommerce-google-adwords-conversion-tracking-tag' ); ?><a href="https://wolfundbaer.ch" target="_blank">https://wolfundbaer.ch</a></div>

            </div>
		<?php
	}

	public function get_debug_info(): string {
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
		if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {
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
		$last_order = new WC_Order( wc_get_order( $last_order_id ) );
		$html       .= 'Last order URL: ' . $last_order->get_checkout_order_received_url() . PHP_EOL;


		$html .= PHP_EOL . '## Theme ##' . PHP_EOL . PHP_EOL;

		$is_child_theme = is_child_theme() ? 'yes' : 'no';
		$html           .= 'Is child theme: ' . $is_child_theme . PHP_EOL;
		$theme_support  = current_theme_supports( 'woocommerce' ) ? 'yes' : 'no';
		$html           .= 'WooCommerce support: ' . $theme_support . PHP_EOL;

		$html .= PHP_EOL;

		$style_parent_theme        = wp_get_theme( get_template() );
		$style_parent_theme_author = $style_parent_theme->get( 'Author' );

		$theme_description_prefix = is_child_theme() ? 'Child theme ' : 'Theme ';

		$html .= $theme_description_prefix . 'Name: ' . wp_get_theme()->get( 'Name' ) . PHP_EOL;
		$html .= $theme_description_prefix . 'ThemeURI: ' . wp_get_theme()->get( 'ThemeURI' ) . PHP_EOL;
		$html .= $theme_description_prefix . 'Author: ' . wp_get_theme()->get( 'Author' ) . PHP_EOL;
		$html .= $theme_description_prefix . 'AuthorURI: ' . wp_get_theme()->get( 'AuthorURI' ) . PHP_EOL;
		$html .= $theme_description_prefix . 'Version: ' . wp_get_theme()->get( 'Version' ) . PHP_EOL;
		$html .= $theme_description_prefix . 'Template: ' . wp_get_theme()->get( 'Template' ) . PHP_EOL;
		$html .= $theme_description_prefix . 'Status: ' . wp_get_theme()->get( 'Status' ) . PHP_EOL;
		$html .= $theme_description_prefix . 'TextDomain: ' . wp_get_theme()->get( 'TextDomain' ) . PHP_EOL;
		$html .= $theme_description_prefix . 'DomainPath: ' . wp_get_theme()->get( 'DomainPath' ) . PHP_EOL;

		$html .= PHP_EOL;

		if ( is_child_theme() ) {
			$html .= 'Parent theme Name: ' . wp_get_theme()->parent()->get( 'Name' ) . PHP_EOL;
			$html .= 'Parent theme ThemeURI: ' . wp_get_theme()->parent()->get( 'ThemeURI' ) . PHP_EOL;
			$html .= 'Parent theme Author: ' . wp_get_theme()->parent()->get( 'Author' ) . PHP_EOL;
			$html .= 'Parent theme AuthorURI: ' . wp_get_theme()->parent()->get( 'AuthorURI' ) . PHP_EOL;
			$html .= 'Parent theme Version: ' . wp_get_theme()->parent()->get( 'Version' ) . PHP_EOL;
			$html .= 'Parent theme Template: ' . wp_get_theme()->parent()->get( 'Template' ) . PHP_EOL;
			$html .= 'Parent theme Status: ' . wp_get_theme()->parent()->get( 'Status' ) . PHP_EOL;
			$html .= 'Parent theme TextDomain: ' . wp_get_theme()->parent()->get( 'TextDomain' ) . PHP_EOL;
			$html .= 'Parent theme DomainPath: ' . wp_get_theme()->parent()->get( 'DomainPath' ) . PHP_EOL;
		}

		// TODO maybe add all active plugins

		$html .= PHP_EOL . PHP_EOL . '### End of Information ###';

		return $html;
	}

	public function get_last_order_id() {
		global $wpdb;
		$statuses = array_keys( wc_get_order_statuses() );
		$statuses = implode( "','", $statuses );

		// Getting last Order ID (max value)
		$results = $wpdb->get_col( "
            SELECT MAX(ID) FROM {$wpdb->prefix}posts
            WHERE post_type LIKE 'shop_order'
            AND post_status IN ('$statuses')
        " );

		return reset( $results );
	}

	public function wgact_plugin_setting_conversion_id() {
		echo "<input id='wgact_plugin_conversion_id' name='wgact_plugin_options[gads][conversion_id]' size='40' type='text' value='{$this->options['gads']['conversion_id']}' />";
		echo '<a style="text-decoration: none" href="https://docs.wolfundbaer.ch/wgact/#/plugin-configuration?id=cofigure-the-plugin" target="_blank"><span style="margin-left: 10px; vertical-align: middle" class="dashicons dashicons-info-outline tooltip"><span class="tooltiptext">' . esc_html__( 'open the documentation', 'woocommerce-google-adwords-conversion-tracking-tag' ) . '</span></span></a>';
		echo '<br><br>';
		esc_html_e( 'The conversion ID looks similar to this:', 'woocommerce-google-adwords-conversion-tracking-tag' );
		echo '&nbsp;<i>123456789</i>';
	}

	public function wgact_plugin_setting_conversion_label() {
		echo "<input id='wgact_plugin_conversion_label' name='wgact_plugin_options[gads][conversion_label]' size='40' type='text' value='{$this->options['gads']['conversion_label']}' />";
		echo '<a style="text-decoration: none" href="https://docs.wolfundbaer.ch/wgact/#/plugin-configuration?id=cofigure-the-plugin" target="_blank"><span style="margin-left: 10px; vertical-align: middle" class="dashicons dashicons-info-outline tooltip"><span class="tooltiptext">' . esc_html__( 'open the documentation', 'woocommerce-google-adwords-conversion-tracking-tag' ) . '</span></span></a>';
		echo '<br><br>';
		esc_html_e( 'The conversion Label looks similar to this:', 'woocommerce-google-adwords-conversion-tracking-tag' );
		echo '&nbsp;<i>Xt19CO3axGAX0vg6X3gM</i>';
	}

	public function wgact_plugin_setting_order_total_logic() {
		?>
        <input type='radio' id='wgact_plugin_option_product_identifier_0' name='wgact_plugin_options[gads][order_total_logic]'
               value='0'  <?php echo( checked( 0, $this->options['gads']['order_total_logic'], false ) ) ?> ><?php esc_html_e( 'Use order_subtotal: Doesn\'t include tax and shipping (default)', 'woocommerce-google-adwords-conversion-tracking-tag' ) ?>
        <br>
        <input type='radio' id='wgact_plugin_option_product_identifier_1' name='wgact_plugin_options[gads][order_total_logic]'
               value='1'  <?php echo( checked( 1, $this->options['gads']['order_total_logic'], false ) ) ?> ><?php esc_html_e( 'Use order_total: Includes tax and shipping', 'woocommerce-google-adwords-conversion-tracking-tag' ) ?>
        <br><br>
		<?php esc_html_e( 'This is the order total amount reported back to Google Ads', 'woocommerce-google-adwords-conversion-tracking-tag' ) ?>
		<?php
	}

	public function wgact_plugin_setting_gtag_deactivation() {

	    // adding the hidden input is a hack to make WordPress save the option with the value zero,
        // instead of not saving it and remove that array key entirely
        // https://stackoverflow.com/a/1992745/4688612
		?>
        <input type='hidden' value='0' name='wgact_plugin_options[gtag][deactivation]''>
        <input type='checkbox' id='wgact_plugin_option_gtag_deactivation' name='wgact_plugin_options[gtag][deactivation]'
               value='1' <?php checked( $this->options['gtag']['deactivation'] ); ?> />
		<?php esc_html_e( 'Disable gtag.js insertion if another plugin is inserting it already.', 'woocommerce-google-adwords-conversion-tracking-tag' );
	}

	public function wgact_plugin_setting_add_cart_data() {

		// adding the hidden input is a hack to make WordPress save the option with the value zero,
        // instead of not saving it and remove that array key entirely
        // https://stackoverflow.com/a/1992745/4688612
		?>
        <input type='hidden' value='0' name='wgact_plugin_options[gads][add_cart_data]'>
        <input type='checkbox' id='wgact_plugin_option_gads_add_cart_data' name='wgact_plugin_options[gads][add_cart_data]'
               value='1' <?php checked( $this->options['gads']['add_cart_data'] ); ?> />
		<?php esc_html_e( 'Add the cart data to the conversion event', 'woocommerce-google-adwords-conversion-tracking-tag' );
	}

	public function wgact_plugin_setting_aw_merchant_id() {
		echo "<input type='text' id='wgact_plugin_aw_merchant_id' name='wgact_plugin_options[gads][aw_merchant_id]' size='40' value='{$this->options['gads']['aw_merchant_id']}' />";
		echo '<br><br>';
		esc_html_e('Enter the ID of your Google Merchant Center account.', 'woocommerce-google-adwords-conversion-tracking-tag');
	}

	public function wgact_plugin_setting_aw_feed_country() {

	    ?><b><?php echo $this->get_visitor_country() ?></b><?php
//		echo '<br>' . 'get_external_ip_address: ' . WC_Geolocation::get_external_ip_address();
//		echo '<br>' . 'get_ip_address: ' . WC_Geolocation::get_ip_address();
//		echo '<p>' . 'geolocate_ip: ' . '<br>';
//		echo print_r(WC_Geolocation::geolocate_ip());
//		echo '<p>' . 'WC_Geolocation::geolocate_ip(WC_Geolocation::get_external_ip_address()): ' . '<br>';
//		echo print_r(WC_Geolocation::geolocate_ip(WC_Geolocation::get_external_ip_address()));
		?>
        <div style="margin-top:10px">
        <?php
		esc_html_e('Currently the plugin automatically detects the location of the visitor for this setting. In most, if not all, cases this will work fine. Please let us know if you have a use case where you need another output:', 'woocommerce-google-adwords-conversion-tracking-tag' ); ?>
		<a href="mailto:support@wolfundbaer.ch">support@wolfundbaer.ch</a>
        </div>
        <?php
	}

	// dupe in pixel
	public function get_visitor_country() {

		if ( $this->isLocalhost() ) {
//	        error_log('check external ip');
			$this->ip = WC_Geolocation::get_external_ip_address();
		} else {
//		    error_log('check regular ip');
			$this->ip = WC_Geolocation::get_ip_address();
		}

		$location = WC_Geolocation::geolocate_ip( $this->ip );

//	    error_log ('ip: ' . $this->>$ip);
//	    error_log ('country: ' . $location['country']);
		return $location['country'];
	}

	// dupe in pixel
	public function isLocalhost(): bool {
		return in_array( $_SERVER['REMOTE_ADDR'], [ '127.0.0.1', '::1' ] );
	}

	public function wgact_plugin_setting_aw_feed_language() {
	    ?><b><?php echo $this->get_gmc_language() ?></b>
        <div style="margin-top:10px">
        <?php esc_html_e('The plugin will use the WordPress default language for this setting. If the shop uses translations, in theory we could also use the visitors locale. But, if that language is  not set up in the Google Merchant Center we might run into issues. If you need more options here let us know:', 'woocommerce-google-adwords-conversion-tracking-tag' ); ?>
        <a href=\"mailto:support@wolfundbaer.ch\">support@wolfundbaer.ch</a>
        </div>
        <?php
	}

	// dupe in pixel
	public function get_gmc_language(): string {
		return strtoupper( substr( get_locale(), 0, 2 ) );
	}

	public function wgact_plugin_option_product_identifier() {
		?>
        <input type='radio' id='wgact_plugin_option_product_identifier_0'
               name='wgact_plugin_options[gads][product_identifier]'
               value='0' <?php echo( checked( 0, $this->options['gads']['product_identifier'], false ) ) ?>/><?php esc_html_e( 'post id (default)', 'woocommerce-google-adwords-conversion-tracking-tag' ) ?>
        <br>

        <input type='radio' id='wgact_plugin_option_product_identifier_1'
               name='wgact_plugin_options[gads][product_identifier]'
               value='1' <?php echo( checked( 1, $this->options['gads']['product_identifier'], false ) ) ?>/><?php esc_html_e( 'post id with woocommerce_gpf_ prefix *', 'woocommerce-google-adwords-conversion-tracking-tag' ) ?>
        <br>

        <input type='radio' id='wgact_plugin_option_product_identifier_1'
               name='wgact_plugin_options[gads][product_identifier]'
               value='2' <?php echo( checked( 2, $this->options['gads']['product_identifier'], false ) ) ?>/><?php esc_html_e( 'SKU', 'woocommerce-google-adwords-conversion-tracking-tag' ) ?>
        <br><br>

		<?php esc_html_e( 'Choose a product identifier.', 'woocommerce-google-adwords-conversion-tracking-tag' ); ?>
        <br><br>
		<?php esc_html_e( '* This is for users of the WooCommerce Google Product Feed Plugin', 'woocommerce-google-adwords-conversion-tracking-tag' ); ?>
        <a href="https://woocommerce.com/products/google-product-feed/" target="_blank">WooCommerce Google Product Feed Plugin</a>


		<?php
	}

	// validate the options
	public function wgact_options_validate( $input ): array {

		// validate ['gads']['conversion_id']
		if ( isset( $input['gads']['conversion_id'] ) ) {
			if ( ! $this->is_conversion_id( $input['gads']['conversion_id'] ) ) {
				$input['gads']['conversion_id'] = isset( $this->options['gads']['conversion_id'] ) ? $this->options['gads']['conversion_id'] : '';
				add_settings_error( 'wgact_plugin_options', 'invalid-conversion-id', esc_html__('You have entered an invalid conversion id. It only contains 8 to 10 digits.','woocommerce-google-adwords-conversion-tracking-tag') );
			}
		}

		// validate ['gads']['conversion_label']
		if ( isset( $input['gads']['conversion_label'] ) ) {
			if ( ! $this->is_conversion_label( $input['gads']['conversion_label'] ) ) {
				$input['gads']['conversion_label'] = isset( $this->options['gads']['conversion_label'] ) ? $this->options['gads']['conversion_label'] : '';
				add_settings_error( 'wgact_plugin_options', 'invalid-conversion-label', esc_html__('You have entered an invalid conversion label.', 'woocommerce-google-adwords-conversion-tracking-tag') );
			}
		}

		// validate ['gads']['aw_merchant_id']
		if ( isset( $input['gads']['aw_merchant_id'] ) ) {
			if ( ! $this->is_aw_merchant_id( $input['gads']['aw_merchant_id'] ) ) {
				$input['gads']['aw_merchant_id'] = isset( $this->options['gads']['aw_merchant_id'] ) ? $this->options['gads']['aw_merchant_id'] : '';
				add_settings_error( 'wgact_plugin_options', 'invalid-aw-merchant-id', esc_html__('You have entered an invalid merchant id. It only contains 8 to 10 digits.', 'woocommerce-google-adwords-conversion-tracking-tag') );
			}
		}

		// merging with the existing options
		// and overwriting old values

		// since disabling a checkbox doesn't send a value,
		// we need to set one to overwrite the old value

        $input = $this->merge_options($this->options, $input);

//		$this->options['woopt']['existing'] = true;
//		update_option(WGACT_DB_OPTIONS_NAME, $this->options);

//		error_log('options');
//		error_log(print_r($this->options, true));
//
//		error_log('input merged');
//		error_log(print_r($input, true));

		return $input;
	}

	// Recursively go through the array and merge (overwrite old values with new ones
    // if a value is missing in the input array, set it to value zero in the options array
    // Omit key like 'db_version' since they would be overwritten with zero.
	protected function merge_options($array_existing, $array_input): array {
		$output_array = [];

		foreach ($array_existing as $key => $value)
		{
			if(array_key_exists($key, $array_input)){
				if(is_array($value)){

					$output_array[$key] = $this->merge_options($value, $array_input[$key]);

				} else {
					$output_array[$key] = $array_input[$key];
				}
			} else {
				if(is_array($value) && ! in_array($key, $this->non_form_keys())){
					$output_array[$key] = $this->set_array_value_to_zero($value);
				} else {
				    if(in_array($key, $this->non_form_keys())) { // because db_version is not a form field, prevent it to be overwritten with 0
//error_log('value: ' . $value);
				        foreach ($this->non_form_keys() as $non_form_key => $non_form_value) {
					        $output_array[$non_form_value] = $array_existing[$non_form_value];
                        }
				    } else {
					    $output_array[$key] = 0;
                    }
				}
			}
		}

		return $output_array;
	}

	protected function non_form_keys(): array {
	    return [
	            'db_version',
        ];
	}

	function set_array_value_to_zero($array){

		foreach ($array as $key => $value) {
			if(is_array($value)) {
				$array[$key] = $this->set_array_value_to_zero($value);
			} else {
				$array[$key] = 0;
			}
		}

		return $array;
	}

	public function is_conversion_id( $string ): bool {

	    if( empty($string) ){
	        return true;
        }

		$re = '/^\d{8,11}$/m';

		return $this->validate_with_regex( $re, $string );
	}

	protected function is_conversion_label( $string ): bool {

		if( empty($string) ){
			return true;
		}

		$re = '/^[-a-zA-Z_0-9]{17,20}$/m';

		return $this->validate_with_regex( $re, $string );
	}

	protected function is_aw_merchant_id( $string ): bool {

		if( empty($string) ){
			return true;
		}

		$re = '/^\d{8,10}$/m';

		return $this->validate_with_regex( $re, $string );
	}

	protected function validate_with_regex( string $re, $string ): bool {
		preg_match_all( $re, $string, $matches, PREG_SET_ORDER, 0 );

		if ( isset( $matches[0] ) ) {
			return true;
		} else {
			return false;
		}
	}

}