<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WgactPixel {

	public $conversion_id;
	public $conversion_label;
	public $add_cart_data;
	public $product_identifier;
	public $gtag_deactivation;
	public $ip;

	public function GoogleAdsTag() {

		$this->conversion_id      = $this->get_conversion_id();
		$this->conversion_label   = $this->get_conversion_label();
		$this->add_cart_data      = $this->get_add_cart_data();
		$this->product_identifier = $this->get_product_identifier();
		$this->gtag_deactivation  = $this->get_gtag_deactivation();

		if($this->gtag_deactivation == 0) {

		    $wgact_gtag = new WgactGtag();
		    $wgact_gtag->set_conversion_id($this->get_conversion_id());
		    $wgact_gtag->inject();
		}

		if ( is_order_received_page() ) {

			// get order from URL and evaluate order total
			$order_key      = $_GET['key'];
			$order          = new WC_Order( wc_get_order_id_by_order_key( $order_key ) );

			$options             = get_option( WGACT_DB_OPTIONS_NAME );
			$order_total_setting = $options['gads']['order_total_logic'];
			$order_total         = 0 == $order_total_setting ? $order->get_subtotal() - $order->get_total_discount(): $order->get_total();

			// use the right function to get the currency depending on the WooCommerce version
			$order_currency = $this->woocommerce_3_and_above() ? $order->get_currency() : $order->get_order_currency();

			// filter to adjust the order value
			$order_total_filtered = apply_filters( 'wgact_conversion_value_filter', $order_total, $order );

			$ratings = get_option(WGACT_DB_RATINGS);
			$ratings['conversions_count'] = $ratings['conversions_count'] + 1;
			update_option(WGACT_DB_RATINGS, $ratings);

			?>

			<!--noptimize-->
			<!-- Global site tag (gtag.js) - Google Ads: <?php echo esc_html( $this->conversion_id ) ?> -->
			<?php

			// Only run conversion script if the payment has not failed. (has_status('completed') is too restrictive)
			// Also don't run the pixel if an admin or shop manager is logged in.
			if ( ! $order->has_status( 'failed' ) && ! current_user_can( 'edit_others_pages' ) && $this->add_cart_data == 0 ) {
//           if ( ! $order->has_status( 'failed' ) ) {
				?>

				<!-- Event tag for WooCommerce Checkout conversion page -->
				<script>
					<?php if($options['gtag']['deactivation'] == true) : ?>

                    gtag( 'config', 'AW-<?php echo $this->conversion_id ?>');
					<?php endif; ?>

                    gtag('event', 'conversion', {
                        'send_to': 'AW-<?php echo esc_html( $this->conversion_id ) ?>/<?php echo esc_html( $this->conversion_label ) ?>',
                        'value': <?php echo $order_total_filtered; ?>,
                        'currency': '<?php echo $order_currency; ?>',
                        'transaction_id': '<?php echo $order->get_order_number(); ?>',
                    });
				</script>
				<?php

			} elseif ( ! $order->has_status( 'failed' ) && ! current_user_can( 'edit_others_pages' ) && $this->add_cart_data == 1 ){
				?>

				<!-- Event tag for WooCommerce Checkout conversion page -->
				<script>
					<?php if($options['gtag']['deactivation'] == true) : ?>

                    gtag( 'config', 'AW-<?php echo $this->conversion_id ?>');
					<?php endif; ?>

                    gtag('event', 'purchase', {
                        'send_to': 'AW-<?php echo esc_html( $this->conversion_id ) ?>/<?php echo esc_html( $this->conversion_label ) ?>',
                        'transaction_id': '<?php echo $order->get_order_number(); ?>',
                        'value': <?php echo $order_total_filtered; ?>,
                        'currency': '<?php echo $order_currency; ?>',
                        'discount': <?php echo $order->get_total_discount(); ?>,
                        'aw_merchant_id': '<?php echo $options['gads']['aw_merchant_id']; ?>',
                        'aw_feed_country': '<?php echo $this->get_visitor_country(); ?>',
                        'aw_feed_language': '<?php echo $this->get_gmc_language(); ?>',
                        'items': <?php echo json_encode( $this->get_order_items($order) ); ?>
                    });
				</script>
				<?php

			} else {

				?>

				<!-- The Google Ads pixel has not been inserted. Possible reasons: -->
				<!--    You are logged into WooCommerce as admin or shop manager. -->
				<!--    The order payment has failed. -->
				<!--    The pixel has already been fired. To prevent double counting the pixel is not being fired again. -->

				<?php
			} // end if order status

			?>

			<!-- END Google Code for Sales (Google Ads) Conversion Page -->
			<!--/noptimize-->
			<?php
		}
	}

	public function get_conversion_id() {
		$options = get_option( 'wgact_plugin_options' );

		return $options['gads']['conversion_id'];
	}

	public function get_conversion_label() {
		$options = get_option( 'wgact_plugin_options' );

		return $options['gads']['conversion_label'];
	}

	public function get_add_cart_data() {
		$options = get_option( 'wgact_plugin_options' );

		if ($options == ''){
			return 0;
		} else {
			return $options['gads']['add_cart_data'];
		}
	}

	public function get_product_identifier() {
		$options = get_option( 'wgact_plugin_options' );

		return $options['gads']['product_identifier'];
	}

	public function get_gtag_deactivation() {
		$options = get_option( 'wgact_plugin_options' );

		return $options['gtag']['deactivation'];
	}

	public function woocommerce_3_and_above(){
		global $woocommerce;
		if( version_compare( $woocommerce->version, 3.0, ">=" ) ) {
			return true;
		} else {
			return false;
		}
	}

	public function get_visitor_country(){

		if ( $this->isLocalhost() ){
//	        error_log('check external ip');
			$this->ip = WC_Geolocation::get_external_ip_address();
		} else {
//		    error_log('check regular ip');
			$this->ip = WC_Geolocation::get_ip_address();
		}

		$location = WC_Geolocation::geolocate_ip($this->ip);

//	    error_log ('ip: ' . $this->ip);
//	    error_log ('country: ' . $location['country']);
		return $location['country'];
	}

	public function isLocalhost() {
		return in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']);
	}

	public function get_gmc_language(){
		return strtoupper(substr( get_locale(), 0, 2 ));
	}

	public function get_order_items($order){

		$order_items       = $order->get_items();
		$order_items_array = array();

		foreach ( (array) $order_items as $item ) {

			$product = wc_get_product( $item['product_id'] );

			$item_details_array = array();

			// depending on setting use product IDs or SKUs
			if ( 0 == $this->product_identifier ) {

				$item_details_array['id'] = (string)$item['product_id'];

			} elseif ( 1 == $this->product_identifier ) {

				$item_details_array['id'] = 'woocommerce_gpf_' . $item['product_id'];

			} else {

				// fill the array with all product SKUs
				$item_details_array['id'] = (string)$product->get_sku();

			}

			$item_details_array['quantity'] = (int)$item['quantity'];
			$item_details_array['price']    = (int)$product->get_price();

			array_push($order_items_array, $item_details_array);

		}

		// apply filter to the $order_items_array array
		$order_items_array = apply_filters( 'wgdr_filter', $order_items_array, 'order_items_array' );

		return $order_items_array;

	}
}