<?php

// TODO move script for copying debug info into a proper .js enqueed file, or switch tabs to JavaScript switching and always save all settings at the same time
// TODO debug info list of active paymanet gateways

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WgactAdmin {

	public    $ip;
	protected $text_domain;
	protected $options;

	public function init() {

		$this->text_domain = 'woocommerce-google-adwords-conversion-tracking-tag';

		$this->options = get_option( 'wgact_plugin_options' );

		// add the admin options page
		add_action( 'admin_menu', [ $this, 'wgact_plugin_admin_add_page' ], 99 );

		// install a settings page in the admin console
		add_action( 'admin_init', [ $this, 'wgact_plugin_admin_init' ] );

		// Load textdomain
		add_action( 'init', [ $this, 'load_plugin_textdomain' ] );
	}

	// Load text domain function
	public function load_plugin_textdomain() {
		load_plugin_textdomain( $this->text_domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	// add the admin options page
	public function wgact_plugin_admin_add_page() {
		//add_options_page('WGACT Plugin Page', 'WGACT Plugin Menu', 'manage_options', 'wgact', array($this, 'wgact_plugin_options_page'));
		add_submenu_page(
			'woocommerce',
			esc_html__( 'Google Ads Conversion Tracking', $this->text_domain ),
			esc_html__( 'Google Ads Conversion Tracking', $this->text_domain ),
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

		$this->add_section_for_main_options();
		$this->add_section_for_advanced_options();
		$this->add_section_for_beta_options();
		$this->add_section_for_support();
	}

	public function add_section_for_main_options() {
		add_settings_section(
			'wgact_plugin_main_options',
			esc_html__( 'Main Settings', $this->text_domain ),
			[ $this, 'wgact_plugin_section_main_description' ],
			'wgact_plugin_main_options'
		);

		// add the field for the conversion id
		add_settings_field(
			'wgact_plugin_conversion_id',
			esc_html__(
				'Conversion ID',
				$this->text_domain
			),
			[ $this, 'wgact_plugin_setting_conversion_id' ],
			'wgact_plugin_main_options',
			'wgact_plugin_main_options'
		);

		// ad the field for the conversion label
		add_settings_field(
			'wgact_plugin_conversion_label',
			esc_html__(
				'Conversion Label',
				$this->text_domain
			),
			[ $this, 'wgact_plugin_setting_conversion_label' ],
			'wgact_plugin_main_options',
			'wgact_plugin_main_options'
		);
	}

	public function add_section_for_advanced_options() {

		add_settings_section(
			'wgact_plugin_advanced_options',
			esc_html__(
				'Advanced Options',
				$this->text_domain
			),
			[ $this, 'wgact_plugin_section_advanced_description' ],
			'wgact_plugin_advanced_options'
		);

		// add fields for the order total logic
		add_settings_field(
			'wgact_plugin_order_total_logic',
			esc_html__(
				'Order Total Logic',
				$this->text_domain
			),
			[ $this, 'wgact_plugin_setting_order_total_logic' ],
			'wgact_plugin_advanced_options',
			'wgact_plugin_advanced_options'
		);

		// add fields for the gtag insertion
		add_settings_field(
			'wgact_plugin_gtag',
			esc_html__(
				'gtag Deactivation',
				$this->text_domain
			),
			[ $this, 'wgact_plugin_setting_gtag_deactivation' ],
			'wgact_plugin_advanced_options',
			'wgact_plugin_advanced_options'
		);
	}

	public function add_section_for_beta_options() {
		// add new section for cart data
		add_settings_section(
			'wgact_plugin_beta_options',
			esc_html__(
				'Add Cart Data',
				$this->text_domain
			) . ' (<span style="color:#ff0000">beta</span>)',
			[ $this, 'wgact_plugin_section_add_cart_data_description' ],
			'wgact_plugin_beta_options'
		);

		// add fields for cart data
		add_settings_field(
			'wgact_plugin_add_cart_data',
			esc_html__(
				'Activation',
				$this->text_domain
			),
			[ $this, 'wgact_plugin_setting_add_cart_data' ],
			'wgact_plugin_beta_options',
			'wgact_plugin_beta_options'
		);

		// add the field for the aw_merchant_id
		add_settings_field(
			'wgact_plugin_aw_merchant_id',
			esc_html__(
				'aw_merchant_id',
				$this->text_domain
			),
			[ $this, 'wgact_plugin_setting_aw_merchant_id' ],
			'wgact_plugin_beta_options',
			'wgact_plugin_beta_options'
		);

		// add the field for the aw_feed_country
		add_settings_field(
			'wgact_plugin_aw_feed_country',
			esc_html__(
				'aw_feed_country',
				$this->text_domain
			),
			[ $this, 'wgact_plugin_setting_aw_feed_country' ],
			'wgact_plugin_beta_options',
			'wgact_plugin_beta_options'
		);

		// add the field for the aw_feed_language
		add_settings_field(
			'wgact_plugin_aw_feed_language',
			esc_html__(
				'aw_feed_language',
				$this->text_domain
			),
			[ $this, 'wgact_plugin_setting_aw_feed_language' ],
			'wgact_plugin_beta_options',
			'wgact_plugin_beta_options'
		);

		// add fields for the product identifier
		add_settings_field(
			'wgact_plugin_option_product_identifier',
			esc_html__(
				'Product Identifier',
				$this->text_domain
			),
			[ $this, 'wgact_plugin_option_product_identifier' ],
			'wgact_plugin_beta_options',
			'wgact_plugin_beta_options'
		);
	}

	public function add_section_for_support() {
		add_settings_section(
			'wgact_plugin_support',
			esc_html__( 'Support Page', $this->text_domain ),
			[ $this, 'wgact_plugin_section_support_description' ],
			'wgact_plugin_support_page'
		);
	}

	// display the admin options page
	public function wgact_plugin_options_page() {
		?>

        <div style="width:980px; float: left; margin: 5px">
            <div style="float:left; margin: 5px; margin-right:20px; width:750px">

				<?php settings_errors(); ?>

				<?php

				$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'main_options';
				?>

                <h2 class="nav-tab-wrapper">
                    <a href="?page=wgact&tab=main_options"
                       class="nav-tab <?php echo $active_tab == 'main_options' ? 'nav-tab-active' : ''; ?>">Main
                        Options</a>
                    <a href="?page=wgact&tab=advanced_options"
                       class="nav-tab <?php echo $active_tab == 'advanced_options' ? 'nav-tab-active' : ''; ?>">Advanced
                        Options</a>
                    <a href="?page=wgact&tab=beta_options"
                       class="nav-tab <?php echo $active_tab == 'beta_options' ? 'nav-tab-active' : ''; ?>">Beta
                        Options</a>
                    <a href="?page=wgact&tab=support"
                       class="nav-tab <?php echo $active_tab == 'support' ? 'nav-tab-active' : ''; ?>">Support</a>
                </h2>

                <form action="options.php" method="post">

					<?php
					if ( $active_tab == 'main_options' ) {
						settings_fields( 'wgact_plugin_options_group' );
						do_settings_sections( 'wgact_plugin_main_options' );
					} else if ( $active_tab == 'advanced_options' ) {
						settings_fields( 'wgact_plugin_options_group' );
						do_settings_sections( 'wgact_plugin_advanced_options' );
					} else if ( $active_tab == 'beta_options' ) {
						settings_fields( 'wgact_plugin_options_group' );
						do_settings_sections( 'wgact_plugin_beta_options' );
					} else if ( $active_tab == 'support' ) {
						settings_fields( 'wgact_plugin_options_group' );
						do_settings_sections( 'wgact_plugin_support_page' );
					}
					?>

                    <table class="form-table" style="margin: 10px">
                        <tr>
                            <th scope="row" style="white-space: nowrap">
                                <input name="Submit" type="submit" value="<?php esc_attr_e( 'Save Changes' ); ?>"
                                       class="button button-primary"/>
                            </th>
                        </tr>
                    </table>
                </form>

                <div style="background: #0073aa; padding: 10px; font-weight: bold; color: white; margin-bottom: 20px; border-radius: 2px">
					<span>
						<?php esc_html_e( 'Profit Driven Marketing by Wolf+Bär', $this->text_domain ) ?>
					</span>
                    <span style="float: right;">
						<a href="https://wolfundbaer.ch/"
                           target="_blank" style="color: white">
							<?php esc_html_e( 'Visit us here: https://wolfundbaer.ch', $this->text_domain ) ?>
						</a>
					</span>
                </div>
            </div>
            <div style="float: left; margin: 5px">
                <a href="https://wordpress.org/plugins/woocommerce-google-dynamic-retargeting-tag/" target="_blank">
                    <img src="<?php echo( plugins_url( '../images/wgdr-icon-256x256.png', __FILE__ ) ) ?>" width="150px"
                         height="150px">
                </a>
            </div>
            <div style="float: left; margin: 5px">
                <a href="https://wordpress.org/plugins/woocommerce-google-adwords-conversion-tracking-tag/"
                   target="_blank">
                    <img src="<?php echo( plugins_url( '../images/wgact-icon-256x256.png', __FILE__ ) ) ?>"
                         width="150px"
                         height="150px">
                </a>
            </div>
        </div>
		<?php
	}

	// descpritions

	public function wgact_plugin_section_main_description() {
		// do nothing
	}

	public function wgact_plugin_section_advanced_description() {
		// do nothing
	}

	public function wgact_plugin_section_add_cart_data_description() {

		_e( 'Find out more about this wonderful new feature: ', $this->text_domain );
		echo '<a href="https://support.google.com/google-ads/answer/9028254" target="_blank">https://support.google.com/google-ads/answer/9028254</a><br>';
		_e( 'At the moment we are testing this feature. It might go into a PRO version of this plugin in the future.', $this->text_domain );
	}

	public function wgact_plugin_section_support_description() {
		?>
        <div>
			<?php _e( 'Use the following two resources for support: ', $this->text_domain ); ?>
        </div>
        <div style="margin-bottom: 30px;">
            <ul>

                <li>
					<?php _e( 'Post a support request in the WordPress support forum here: ', $this->text_domain ); ?>
                    <a href="https://wordpress.org/support/plugin/woocommerce-google-adwords-conversion-tracking-tag/"
                       target="_blank">https://wordpress.org/support/plugin/woocommerce-google-adwords-conversion-tracking-tag/</a>
                </li>
                <li>
					<?php _e( 'Or send us an email to the following address: ', $this->text_domain ); ?>
                    <a href="mailto:support@wolfundbaer.ch" target="_blank">support@wolfundbaer.ch</a>
                </li>
            </ul>
        </div>
        <div class=" woocommerce-message">

            <div>
                <textarea id="debug-info-textarea" class="" style="color:dimgrey;resize: none;" cols="100%" rows="30"
                          readonly><?php echo $this->get_debug_info() ?></textarea>
                <button id="debug-info-button" type="button">copy to clipboard</button>
            </div>

        </div>

        <script>
            jQuery("#debug-info-button").click(function () {
                jQuery("#debug-info-textarea").select();
                document.execCommand('copy');
            });
        </script>

		<?php
	}

	public function get_debug_info() {
		global $woocommerce, $wp_version, $current_user;

		$html = '### Debugging Information ###' . PHP_EOL . PHP_EOL;

		$html .= '## System Environment ##' . PHP_EOL . PHP_EOL;

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
		echo "<input id='wgact_plugin_conversion_id' name='wgact_plugin_options[conversion_id]' size='40' type='text' value='{$this->options['conversion_id']}' />";
		echo '<br><br>';
		_e( 'The conversion ID looks similar to this:', $this->text_domain );
		echo '&nbsp;<i>123456789</i>';
		echo '<p>';
		_e( 'Watch a video that explains how to find the conversion ID: ', $this->text_domain );
		echo '<a href="https://www.youtube.com/watch?v=p9gY3JSrNHU" target="_blank">https://www.youtube.com/watch?v=p9gY3JSrNHU</a>';
	}

	public function wgact_plugin_setting_conversion_label() {
		echo "<input id='wgact_plugin_conversion_label' name='wgact_plugin_options[conversion_label]' size='40' type='text' value='{$this->options['conversion_label']}' />";
		echo '<br><br>';
		_e( 'The conversion Label looks similar to this:', $this->text_domain );
		echo '&nbsp;<i>Xt19CO3axGAX0vg6X3gM</i>';
		echo '<p>';
		_e( 'Watch a video that explains how to find the conversion ID: ', $this->text_domain );
		echo '<a href="https://www.youtube.com/watch?v=p9gY3JSrNHU" target="_blank">https://www.youtube.com/watch?v=p9gY3JSrNHU</a>';
	}

	public function wgact_plugin_setting_order_total_logic() {
		?>
        <input type='radio' id='wgact_plugin_option_product_identifier_0' name='wgact_plugin_options[order_total_logic]'
               value='0'  <?php echo( checked( 0, $this->options['order_total_logic'], false ) ) ?> ><?php _e( 'Use order_subtotal: Doesn\'t include tax and shipping (default)', $this->text_domain ) ?>
        <br>
        <input type='radio' id='wgact_plugin_option_product_identifier_1' name='wgact_plugin_options[order_total_logic]'
               value='1'  <?php echo( checked( 1, $this->options['order_total_logic'], false ) ) ?> ><?php _e( 'Use order_total: Includes tax and shipping', $this->text_domain ) ?>
        <br><br>
		<?php _e( 'This is the order total amount reported back to Google Ads', $this->text_domain ) ?>
		<?php
	}

	public function wgact_plugin_setting_gtag_deactivation() {
		?>
        <input type='checkbox' id='wgact_plugin_option_gtag_deactivation' name='wgact_plugin_options[gtag_deactivation]'
               value='1' <?php checked( $this->options['gtag_deactivation'] ); ?> />
		<?php
		echo( esc_html__( 'Disable gtag.js insertion if another plugin is inserting it already.', $this->text_domain ) );
	}

	public function wgact_plugin_setting_add_cart_data() {
		?>
        <input type='checkbox' id='wgact_plugin_add_cart_data' name='wgact_plugin_options[add_cart_data]' size='40'
               value='1' <?php echo( checked( 1, $this->options['add_cart_data'], true ) ) ?> >
		<?php
		_e( 'Add the cart data to the conversion event', $this->text_domain );
	}

	public function wgact_plugin_setting_aw_merchant_id() {
		echo "<input type='text' id='wgact_plugin_aw_merchant_id' name='wgact_plugin_options[aw_merchant_id]' size='40' value='{$this->options['aw_merchant_id']}' />";
		echo '<br><br>Enter the ID of your Google Merchant Center account.';
	}

	public function wgact_plugin_setting_aw_feed_country() {

		echo '<b>' . $this->get_visitor_country() . '</b>&nbsp;';
//		echo '<br>' . 'get_external_ip_address: ' . WC_Geolocation::get_external_ip_address();
//		echo '<br>' . 'get_ip_address: ' . WC_Geolocation::get_ip_address();
//		echo '<p>' . 'geolocate_ip: ' . '<br>';
//		echo print_r(WC_Geolocation::geolocate_ip());
//		echo '<p>' . 'WC_Geolocation::geolocate_ip(WC_Geolocation::get_external_ip_address()): ' . '<br>';
//		echo print_r(WC_Geolocation::geolocate_ip(WC_Geolocation::get_external_ip_address()));
		echo '<br><br>Currently the plugin automatically detects the location of the visitor for this setting. In most, if not all, cases this will work fine. Please let us know if you have a use case where you need another output: <a href="mailto:support@wolfundbaer.ch">support@wolfundbaer.ch</a>';
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
	public function isLocalhost() {
		return in_array( $_SERVER['REMOTE_ADDR'], [ '127.0.0.1', '::1' ] );
	}

	public function wgact_plugin_setting_aw_feed_language() {
		echo '<b>' . $this->get_gmc_language() . '</b>';
		echo "<br><br>The plugin will use the WordPress default language for this setting. If the shop uses translations, in theory we could also use the visitors locale. But, if that language is  not set up in the Google Merchant Center we might run into issues. If you need more options here let us know:  <a href=\"mailto:support@wolfundbaer.ch\">support@wolfundbaer.ch</a>";
	}

	// dupe in pixel
	public function get_gmc_language() {
		return strtoupper( substr( get_locale(), 0, 2 ) );
	}

	public function wgact_plugin_option_product_identifier() {
		?>
        <input type='radio' id='wgact_plugin_option_product_identifier_0'
               name='wgact_plugin_options[product_identifier]'
               value='0' <?php echo( checked( 0, $this->options['product_identifier'], false ) ) ?>/><?php _e( 'post id (default)', $this->text_domain ) ?>
        <br>

        <input type='radio' id='wgact_plugin_option_product_identifier_1'
               name='wgact_plugin_options[product_identifier]'
               value='1' <?php echo( checked( 1, $this->options['product_identifier'], false ) ) ?>/><?php _e( 'post id with woocommerce_gpf_ prefix *', $this->text_domain ) ?>
        <br>

        <input type='radio' id='wgact_plugin_option_product_identifier_1'
               name='wgact_plugin_options[product_identifier]'
               value='2' <?php echo( checked( 2, $this->options['product_identifier'], false ) ) ?>/><?php _e( 'SKU', $this->text_domain ) ?>
        <br><br>

		<?php echo( esc_html__( 'Choose a product identifier.', $this->text_domain ) ); ?>
        <br><br>
		<?php _e( '* This is for users of the <a href="https://woocommerce.com/products/google-product-feed/" target="_blank">WooCommerce Google Product Feed Plugin</a>', $this->text_domain ); ?>


		<?php
	}

	// validate the options
	public function wgact_options_validate( $input ) {

//	    error_log('input');
//	    error_log(print_r($input, true));

		// validate ['conversion_id']
		if ( isset( $input['conversion_id'] ) ) {
			if ( ! $this->is_conversion_id( $input['conversion_id'] ) ) {
				$input['conversion_id'] = isset( $this->options['conversion_id'] ) ? $this->options['conversion_id'] : '';
				add_settings_error( 'wgact_plugin_options', 'invalid-conversion-id', 'You have entered an invalid conversion id.' );
			}
		}

		// validate ['conversion_label']
		if ( isset( $input['conversion_label'] ) ) {
			if ( ! $this->is_conversion_label( $input['conversion_label'] ) ) {
				$input['conversion_label'] = isset( $this->options['conversion_label'] ) ? $this->options['conversion_label'] : '';
				add_settings_error( 'wgact_plugin_options', 'invalid-conversion-label', 'You have entered an invalid conversion label.' );
			}
		}


		// merging with the existing options
		// and overwriting old values

		// since disabling a checkbox doesn't send a value,
		// we need to set one to overwrite the old value

		// list of all checkbox keys
		$checkbox_keys = [ 'add_cart_data', 'gtag_deactivation' ];

		foreach ( $checkbox_keys as $checkbox_key ) {
			if ( ! isset( $input[ $checkbox_key ] ) ) {
				$input[ $checkbox_key ] = 0;
			}
		}

		$input = array_merge( $this->options, $input );

		return $input;
	}

	public function is_conversion_id( $conversion_id ) {

		$re = '/^\d{9}$/m';

		preg_match_all( $re, $conversion_id, $matches, PREG_SET_ORDER, 0 );

		if ( isset( $matches[0] ) ) {
			return true;
		} else {
			return false;
		}
	}

	public function is_conversion_label( $conversion_label ) {

		$re = '/^[-a-zA-Z_0-9]{19,20}$/m';

		preg_match_all( $re, $conversion_label, $matches, PREG_SET_ORDER, 0 );

		if ( isset( $matches[0] ) ) {
			return true;
		} else {
			return false;
		}
	}

}