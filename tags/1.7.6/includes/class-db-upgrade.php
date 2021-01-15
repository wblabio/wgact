<?php
/**
 * DB upgrade function
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WgactDbUpgrade {

	public function run_options_db_upgrade() {

		$mysql_db_version = $this->get_mysql_db_version();

		// determine version and run version specific upgrade function
		// check if options db version zero by looking if the old entries are still there.
		if ( '0.0' === $mysql_db_version ) {
			$this->up_from_zero_to_1();
		}

		if ( version_compare(WGACT_DB_VERSION, $mysql_db_version, '>' ) ){
			$this->up_from_1_to_2();
		}
	}

	protected function up_from_1_to_2(){
		$options_old = get_option(WGACT_DB_OPTIONS_NAME);

		$options_new = [
			'gads' => [
				'conversion_id'      => $options_old['conversion_id'],
				'conversion_label'   => $options_old['conversion_label'],
				'order_total_logic'  => $options_old['order_total_logic'],
				'add_cart_data'      => $options_old['add_cart_data'],
				'aw_merchant_id'     => $options_old['aw_merchant_id'],
				'product_identifier' => $options_old['product_identifier'],
			],
			'gtag' => [
				'deactivation' => $options_old['gtag_deactivation'],
			],
			'db_version' => '2',
		];

		update_option(WGACT_DB_OPTIONS_NAME, $options_new);
	}

	protected function get_mysql_db_version(): string {

		$options = get_option(WGACT_DB_OPTIONS_NAME);

//		error_log(print_r($options,true));

		if ( ( get_option( 'wgact_plugin_options_1' ) ) || ( get_option( 'wgact_plugin_options_2' ) ) ) {
			return '0';
		} elseif (array_key_exists('conversion_id', $options)) {
			return '1';
		} else {
			return $options['db_version'];
		}
	}

	public function up_from_zero_to_1() {

		$option_name_old_1 = 'wgact_plugin_options_1';
		$option_name_old_2 = 'wgact_plugin_options_2';

		// db version place options into new array
		$options = [
			'conversion_id'    => $this->get_option_value_v1( $option_name_old_1 ),
			'conversion_label' => $this->get_option_value_v1( $option_name_old_2 ),
		];

		// store new option array into the options table
		update_option( WGACT_DB_OPTIONS_NAME, $options );

		// delete old options
		// only on single site
		// we will run the multisite deletion only during uninstall
		delete_option( $option_name_old_1 );
		delete_option( $option_name_old_2 );
	}

	protected function get_option_value_v1( string $option_name ): string {
		if ( ! get_option( $option_name ) ) {
			$option_value = "";
		} else {
			$option = get_option( $option_name );
			$option_value       = $option['text_string'];
		}

		return $option_value;
	}
}