<?php

namespace WGACT\Classes\Pixels;

use stdClass;
use WC_Geolocation;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Pixel
{
    use Trait_Product;

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
    protected $options;
    protected $options_obj;
    protected $dyn_r_id_type;
    protected $pixel_name = '';

    public function __construct($options)
    {
        /*
         * Initialize options
         */
//        $this->options = get_option(WGACT_DB_OPTIONS_NAME);
        $this->options = $options;
        $this->options_obj = json_decode(json_encode($this->options));

        $this->options_obj->shop->currency = new stdClass();
        $this->options_obj->shop->currency = get_woocommerce_currency();

        // avoid number output with too many decimals
        if (version_compare(phpversion(), '7.1', '>=')) {
            ini_set( 'serialize_precision', -1 );
        }

        $this->order_total_logic = $this->options['shop']['order_total_logic'];
//        $this->add_cart_data       = $this->options['google']['ads']['add_cart_data'];
        $this->add_cart_data       = $this->options['google']['ads']['aw_merchant_id'] ? true : false;
        $this->aw_merchant_id      = $this->options['google']['ads']['aw_merchant_id'];
        $this->conversion_id       = $this->options['google']['ads']['conversion_id'];
        $this->conversion_label    = $this->options['google']['ads']['conversion_label'];
        $this->dynamic_remarketing = $this->options['google']['ads']['dynamic_remarketing'];
        $this->product_identifier  = $this->options['google']['ads']['product_identifier'];

        if(array_key_exists('gtag', $this->options['google'])){
            $this->gtag_deactivation   = $this->options['google']['gtag']['deactivation'];
        }
    }

    // get an array with all cart product ids
    public function get_cart_ids($cart): array
    {
        // error_log(print_r($cart, true));
        // initiate product identifier array
        $cart_items = [];

        // go through the array and get all product identifiers
        foreach ((array)$cart as $cart_item) {

            $product_id = $this->get_variation_or_product_id($cart_item, $this->options_obj->general->variations_output);
            $product    = wc_get_product($product_id);

            $product_id_compiled = $this->get_compiled_product_id($product_id, $product->get_sku(), $this->options,'');

            array_push($cart_items, $product_id_compiled);
        }

        return $cart_items;
    }

    protected function get_visitor_country()
    {
        if ($this->isLocalhost()) {
            $this->ip = WC_Geolocation::get_external_ip_address();
        } else {
            $this->ip = WC_Geolocation::get_ip_address();
        }

        $location = WC_Geolocation::geolocate_ip($this->ip);

        return $location['country'];
    }

    protected function isLocalhost(): bool
    {
        return in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']);
    }








}