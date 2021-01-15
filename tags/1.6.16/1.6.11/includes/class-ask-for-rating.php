<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WgactAskForRating {

	public function init() {

		// ask for a rating in a plugin notice
		add_action( 'admin_head', [$this, 'ask_for_rating_js'] );
		add_action( 'wp_ajax_wgact_dismissed_notice_handler', [$this, 'ajax_rating_notice_handler'] );
		add_action( 'admin_notices', [$this, 'ask_for_rating_notices_if_not_asked_before'] );

	}

	// client side ajax js handler for the admin rating notice
	public function ask_for_rating_js() {

		?>
		<script type="text/javascript">
            jQuery(document).on('click', '.notice-success.wgact-rating-success-notice, wgact-rating-link, .wgact-rating-support', function ($) {

                var data = {
                    'action': 'wgact_dismissed_notice_handler',
                };

                jQuery.post(ajaxurl, data);
                jQuery('.wgact-rating-success-notice').remove();

            });
		</script> <?php

	}

	// server side php ajax handler for the admin rating notice
	public function ajax_rating_notice_handler() {

		// prepare the data that needs to be written into the user meta
		$wgdr_admin_notice_user_meta = array(
			'date-dismissed' => date( 'Y-m-d' ),
		);

		// update the user meta
		update_user_meta( get_current_user_id(), 'wgact_admin_notice_user_meta', $wgdr_admin_notice_user_meta );

		wp_die(); // this is required to terminate immediately and return a proper response
	}

	// only ask for rating if not asked before or longer than a year
	public function ask_for_rating_notices_if_not_asked_before() {

		// get user meta data for this plugin
		$user_meta = get_user_meta( get_current_user_id(), 'wgact_admin_notice_user_meta' );

		// check if there is already a saved value in the user meta
		if ( isset( $user_meta[0]['date-dismissed'] ) ) {

			$date_1 = date_create( $user_meta[0]['date-dismissed'] );
			$date_2 = date_create( date( 'Y-m-d' ) );

			// calculate day difference between the dates
			$interval = date_diff( $date_1, $date_2 );

			// check if the date difference is more than 360 days
			if ( 360 < $interval->format( '%a' ) ) {
				$this->ask_for_rating_notices();
			}

		} else {

			$this->ask_for_rating_notices();
		}
	}

	// show an admin notice to ask for a plugin rating
	public function ask_for_rating_notices() {

		$current_user = wp_get_current_user();

		?>
		<div class="notice notice-success is-dismissible wgact-rating-success-notice">
			<p>
				<span><?php _e( 'Hi ', 'woocommerce-google-adwords-conversion-tracking-tag' ); ?></span>
				<span><?php echo( $current_user->user_firstname ? $current_user->user_firstname : $current_user->nickname ); ?></span>
				<span><?php _e( '! ', 'woocommerce-google-adwords-conversion-tracking-tag' ); ?></span>
				<span><?php _e( 'You\'ve been using the ', 'woocommerce-google-adwords-conversion-tracking-tag' ); ?></span>
				<span><b><?php _e( 'WGACT Google Ads Conversion Tracking Plugin', 'woocommerce-google-adwords-conversion-tracking-tag' ); ?></b></span>
				<span><?php _e( ' for a while now. If you like the plugin please support our development by leaving a ★★★★★ rating: ', 'woocommerce-google-adwords-conversion-tracking-tag' ); ?></span>
				<span class="wgact-rating-link">
                    <a href="https://wordpress.org/support/view/plugin-reviews/woocommerce-google-adwords-conversion-tracking-tag?rate=5#postform"
                       target="_blank"><?php _e( 'Rate it!', 'woocommerce-google-adwords-conversion-tracking-tag' ); ?></a>
                </span>
			</p>
			<p>
				<span><?php _e( 'Or else, please leave us a support question in the forum. We\'ll be happy to assist you: ', 'woocommerce-google-adwords-conversion-tracking-tag' ); ?></span>
				<span class="wgact-rating-support">
                    <a href="https://wordpress.org/support/plugin/woocommerce-google-adwords-conversion-tracking-tag"
                       target="_blank"><?php _e( 'Get support', 'woocommerce-google-adwords-conversion-tracking-tag' ); ?></a>
                </span>
			</p>
		</div>
		<?php

	}
}