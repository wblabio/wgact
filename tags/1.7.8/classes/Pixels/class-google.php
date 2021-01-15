<?php

namespace WGACT\Classes\Pixels;

use WC_Geolocation;
use WC_Order;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Google
{
	protected $add_cart_data;
	protected $aw_merchant_id;
	protected $conversion_id;
	protected $conversion_label;
	protected $dynamic_remarketing;
	protected $google_business_vertical;
	protected $gtag_deactivation;
	protected $ip;
	protected $order_total_logic;
	protected $product_identifier;

	public function __construct(){
        $options = get_option(WGACT_DB_OPTIONS_NAME);

        $this->add_cart_data            = $options['gads']['add_cart_data'] == '' ? 0 : $options['gads']['add_cart_data'];
        $this->aw_merchant_id           = $options['gads']['aw_merchant_id'];
        $this->conversion_id            = $options['gads']['conversion_id'];
        $this->conversion_label         = $options['gads']['conversion_label'];
        $this->dynamic_remarketing      = $options['gads']['dynamic_remarketing'];
        $this->gtag_deactivation        = $options['gtag']['deactivation'];
        $this->order_total_logic        = $options['gads']['order_total_logic'];
        $this->product_identifier       = $options['gads']['product_identifier'];
        $this->google_business_vertical = $options['gads']['google_business_vertical'];
    }

	public function GoogleAdsTag()
	{
		global $woocommerce, $wp_query;

        if ($this->gtag_deactivation == 0) {
			$wgact_gtag = new Gtag($this->conversion_id);
			$wgact_gtag->inject();
		}

		if (true == $this->dynamic_remarketing && is_product_category()) {
			?>

            <!--noptimize-->
            <script type="text/javascript">
				<?php echo $this->gtag_config() ?>

                gtag('event', 'view_item_list', {
                    'send_to': 'AW-<?php echo esc_html($this->conversion_id) ?>',
                    'items': <?php echo json_encode($this->get_products_from_wp_query($wp_query)) . PHP_EOL ?>
                });
            </script>
            <!--/noptimize-->
			<?php
		} elseif (true == $this->dynamic_remarketing && is_search()) {
			?>

            <!--noptimize-->
            <script type="text/javascript">
                <?php echo $this->gtag_config() ?>

                gtag('event', 'view_search_results',
                    {
                        'send_to': 'AW-<?php echo esc_html($this->conversion_id) ?>',
                        'items': <?php echo json_encode($this->get_products_from_wp_query($wp_query)) . PHP_EOL ?>
                    });
            </script>
            <!--/noptimize-->
			<?php
		} elseif ( true == $this->dynamic_remarketing && is_product() && (!isset($_POST['add-to-cart'])) ) {
            $product_id = get_the_ID();
            $product    = wc_get_product( $product_id );

            if ( is_bool( $product ) ) {
//                error_log( 'WooCommerce detects the page ID ' . $product_id . ' as product, but when invoked by wc_get_product( ' . $product_id . ' ) it returns no product object' );
                return;
            }

            $product_details = $this->get_product_details_from_product_id($product_id);
            ?>

            <!--noptimize-->
            <script type="text/javascript">
                <?php echo $this->gtag_config() ?>

                gtag('event', 'view_item', {
                    'send_to': 'AW-<?php echo esc_html( $this->conversion_id ) ?>',
                    'value': <?php echo $product_details['price'] ?>,
                    'items': [<?php echo(json_encode($product_details)) ?>]
                });
            </script>
            <!--/noptimize-->
            <?php

        } elseif ( true == $this->dynamic_remarketing && is_cart() ) {
            $cart = $woocommerce->cart->get_cart();
            ?>

            <!--noptimize-->
            <script type="text/javascript">
                <?php echo $this->gtag_config() ?>

                gtag('event', 'add_to_cart', {
                    'send_to': 'AW-<?php echo esc_html( $this->conversion_id ) ?>',
                    'value': <?php echo WC()->cart->get_cart_contents_total(); ?>,
                    'items': <?php echo(json_encode($this->get_cart_items($cart))) . PHP_EOL ?>
                });
            </script>
            <!--/noptimize-->
            <?php
        } elseif (is_order_received_page()) {

			// get order from URL and evaluate order total
			$order_key = $_GET['key'];
			$order = new WC_Order(wc_get_order_id_by_order_key($order_key));

			$order_total = 0 == $this->order_total_logic ? $order->get_subtotal() - $order->get_total_discount() : $order->get_total();

			// use the right function to get the currency depending on the WooCommerce version
			$order_currency = $this->woocommerce_3_and_above() ? $order->get_currency() : $order->get_order_currency();

			// filter to adjust the order value
			$order_total_filtered = apply_filters('wgact_conversion_value_filter', $order_total, $order);

			$ratings = get_option(WGACT_DB_RATINGS);
			$ratings['conversions_count'] = $ratings['conversions_count'] + 1;
			update_option(WGACT_DB_RATINGS, $ratings);

			?>

            <!--noptimize-->
            <!-- Global site tag (gtag.js) - Google Ads: <?php echo esc_html($this->conversion_id) ?> -->
			<?php

			// Only run conversion script if the payment has not failed. (has_status('completed') is too restrictive)
			// Also don't run the pixel if an admin or shop manager is logged in.
			if (!$order->has_status('failed') && !current_user_can('edit_others_pages') && $this->add_cart_data == 0) {
//           if ( ! $order->has_status( 'failed' ) ) {
				?>

                <!-- Event tag for WooCommerce Checkout conversion page -->
                <script>

					<?php echo $this->gtag_config() ?>

                    gtag('event', 'conversion', {
                        'send_to': 'AW-<?php echo esc_html($this->conversion_id) ?>/<?php echo esc_html($this->conversion_label) ?>',
                        'value': <?php echo $order_total_filtered; ?>,
                        'currency': '<?php echo $order_currency; ?>',
                        'transaction_id': '<?php echo $order->get_order_number(); ?>',
                    });
                    <?php echo $this->get_dyn_remarketing_purchase_script($order, $order_total) ?>

                </script>
				<?php

			} else if (!$order->has_status('failed') && !current_user_can('edit_others_pages') && $this->add_cart_data == 1) {
				?>

                <!-- Event tag for WooCommerce Checkout conversion page -->
                <script>
					<?php echo $this->gtag_config() ?>

                    gtag('event', 'purchase', {
                        'send_to':          'AW-<?php echo esc_html($this->conversion_id) ?>/<?php echo esc_html($this->conversion_label) ?>',
                        'transaction_id':   '<?php echo $order->get_order_number(); ?>',
                        'value':            <?php echo $order_total_filtered; ?>,
                        'currency':         '<?php echo $order_currency; ?>',
                        'discount':         <?php echo $order->get_total_discount(); ?>,
                        'aw_merchant_id':   '<?php echo $this->aw_merchant_id ?>',
                        'aw_feed_country':  '<?php echo $this->get_visitor_country(); ?>',
                        'aw_feed_language': '<?php echo $this->get_gmc_language(); ?>',
                        'items':            <?php echo json_encode($this->get_order_items($order)) . PHP_EOL ?>
                    });

                    <?php // echo $this->get_dyn_remarketing_purchase_script($order, $order_total) ?>

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

	protected function woocommerce_3_and_above(): bool
    {
		global $woocommerce;
		if (version_compare($woocommerce->version, 3.0, ">=")) {
			return true;
		} else {
			return false;
		}
	}

	protected function get_visitor_country()
	{
		if ($this->isLocalhost()) {
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

	protected function isLocalhost(): bool
    {
		return in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']);
	}

	protected function get_gmc_language(): string
    {
		return strtoupper(substr(get_locale(), 0, 2));
	}

	protected function get_order_items($order)
	{
		$order_items = $order->get_items();
		$order_items_array = [];

		foreach ((array) $order_items as $item) {

			$product = wc_get_product($item['product_id']);

			$item_details_array = [];

            $item_details_array['id'] = $this->get_compiled_product_id($item['product_id'], $product->get_sku());
			$item_details_array['quantity'] = (int) $item['quantity'];
			$item_details_array['price'] = (int) $product->get_price();

			array_push($order_items_array, $item_details_array);
		}

		// apply filter to the $order_items_array array
		$order_items_array = apply_filters('wgact_filter', $order_items_array, 'order_items_array');

		return $order_items_array;
	}

	protected function get_compiled_product_id($product_id, $product_sku): string
    {
	    // depending on setting use product IDs or SKUs
        if (0 == $this->product_identifier) {
            return (string) $product_id;
        } else if (1 == $this->product_identifier) {
            return (string) 'woocommerce_gpf_' . $product_id;
        } else {
            return (string) $product_sku;
        }
    }

	// get products from wp_query
	protected function get_products_from_wp_query($wp_query): array
	{
		$items = [];

		$posts = $wp_query->posts;

		foreach ($posts as $key => $post) {

			if ($post->post_type == 'product') {

			    $item_details = [];

				$product = wc_get_product($post->ID);
                $item_details['id'] = $this->get_compiled_product_id($post->ID, $product->get_sku());
				$item_details['google_business_vertical'] = $this->google_business_vertical;

				array_push($items, $item_details);
			}
		}

		return $items;
	}

	protected function gtag_config(): string
    {
		if ($this->gtag_deactivation == true) {
			return "gtag('config', 'AW-" . $this->conversion_id . "');" . PHP_EOL;
		} else {
			return '';
		}
	}

    protected function get_product_details_from_product_id( $product_id ): array
    {
        $product    = wc_get_product( $product_id );

        $product_details['id'] = $this->get_compiled_product_id(get_the_ID(), $product->get_sku());
        $product_details['category'] = $this->get_product_category( $product_id );
        // $product_details['list_position'] = 1;
        $product_details['quantity'] = 1;
        $product_details['price'] = (float)$product->get_price();
        $product_details['google_business_vertical'] = $this->google_business_vertical;

        return $product_details;
    }

    // get an array with all product categories
    protected function get_product_category($product_id): array
    {
        $prod_cats = get_the_terms($product_id, 'product_cat');
        $prod_cats_output = [];

        // only continue with the loop if one or more product categories have been set for the product
        if (!empty($prod_cats)) {
            foreach ((array)$prod_cats as $key) {
                array_push($prod_cats_output, $key->name);
            }

            // apply filter to the $prod_cats_output array
            $prod_cats_output = apply_filters('wgact_filter', $prod_cats_output, 'prod_cats_output');
        }

        return $prod_cats_output;
    }

    // get an array with all cart product ids
    protected function get_cart_items($cart)
    {
        // error_log(print_r($cart, true));
        // initiate product identifier array
        $cart_items = [];
        $item_details = [];

        // go through the array and get all product identifiers
        foreach ((array)$cart as $item) {

            $product = wc_get_product($item['product_id']);

            $item_details['id'] = $this->get_compiled_product_id($item['product_id'], $product->get_sku());
            $item_details['quantity'] = (int)$item['quantity'];
            $item_details['price'] = (int)$product->get_price();
            $item_details['google_business_vertical'] = $this->google_business_vertical;

            array_push($cart_items, $item_details);
        }

        // apply filter to the $cartprods_items array
        $cart_items = apply_filters('wgact_filter', $cart_items, 'cart_items');

        return $cart_items;
    }

    protected function get_dyn_remarketing_purchase_script($order, $order_total){
	    if(true == $this->dynamic_remarketing) {
	    ?>

                    gtag('event', 'purchase', {
                        'send_to': 'AW-<?php echo esc_html( $this->conversion_id ) ?>',
                        'value': <?php echo $order_total; ?>,
                        'items': <?php echo(json_encode($this->get_order_items($order))) . PHP_EOL ?>
                    });
        <?php
        }
    }
}