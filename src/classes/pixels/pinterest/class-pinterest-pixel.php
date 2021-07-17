<?php

// TODO add enhanced match email hash to uncached pages like cart and purchase confirmation page
// TODO check if more values can be passed to product and category pages

namespace WGACT\Classes\Pixels\Pinterest;

use WGACT\Classes\Pixels\Pixel;
use WGACT\Classes\Pixels\Trait_Shop;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Pinterest_Pixel extends Pixel
{
    use Trait_Shop;

    protected $pixel_name;

    public function __construct($options)
    {
        parent::__construct($options);

        $this->pixel_name = 'pinterest';
    }

    public function inject_everywhere()
    {
        $data = [
            'pixel_id'            => $this->options_obj->pinterest->pixel_id,
            'dynamic_remarketing' => [
                'id_type' => $this->get_dyn_r_id_type(),
            ],
        ];

        // @formatter:off
        ?>
            wooptpmDataLayer.pixels.<?php echo $this->pixel_name ?> = <?php echo json_encode($data) ?>;

            !function(e){if(!window.pintrk){window.pintrk = function () {
            window.pintrk.queue.push(Array.prototype.slice.call(arguments))};var
            n=window.pintrk;n.queue=[],n.version="3.0";var
            t=document.createElement("script");t.async=!0,t.src=e;var
            r=document.getElementsByTagName("script")[0];
            r.parentNode.insertBefore(t,r)}}("https://s.pinimg.com/ct/core.js");
            // pintrk('load', '1111111111111', {em: '<user_email_address>'});

            <?php echo $this->get_pintrk_load_event() ?>
            pintrk('page');
        <?php
        // @formatter:on

    }

    private function get_pintrk_load_event(): string
    {
        if ((is_order_received_page() || is_user_logged_in()) && apply_filters('wooptpm_pinterest_enhanced_match', false)) {
            if (is_user_logged_in()) {
                $current_user = wp_get_current_user();
                $email        = $current_user->user_email;
            } else {
                $order = $this->get_order_from_order_received_page();
                $email = $order->get_billing_email();
            }
            return "pintrk('load', '" . $this->options_obj->pinterest->pixel_id . "', {em: '" . $email . "'});" . PHP_EOL;
        } else {
            return "pintrk('load', '" . $this->options_obj->pinterest->pixel_id . "');" . PHP_EOL;
        }
    }

    public function inject_product_category()
    {
        // handled on front-end
    }

    public function inject_search()
    {
        // handled on front-end
    }

    public function inject_product($product, $product_attributes)
    {
        // handled on front-end
    }

    public function inject_cart($cart, $cart_total)
    {
        // handled on front-end
    }

    public function inject_order_received_page($order, $order_total)
    {

        $formatted_order_items = $this->get_formatted_order_items($order);

        echo "
            wooptpmExists().then(function(){
                if (!wooptpm.isOrderIdStored('" . $order->get_id() . "')) {
                    pintrk('track', 'checkout', {
                        'value'         : " . $order_total . ",
                        'order_quantity': " . count($order->get_items()) . ",
                        'currency'      : '" . $order->get_currency() . "',
                        'order_id'      : '" . $order->get_order_number() . "',
                        'line_items'   : " . json_encode($formatted_order_items) . "
                    });
                }
            });
        ";
    }

    private function get_formatted_order_items($order): array
    {
        $order_items = $this->get_order_items_formatted_for_purchase_event($order);

        $formatted_order_items = [];

        foreach ($order_items as $key => $order_item) {

            $formatted_order_item = [
                'product_name'     => $order_item['name'],
                'product_category' => $order_item['category'],
                'product_price'    => $order_item['price'],
                'product_quantity' => $order_item['quantity'],
                'product_brand'    => $order_item['brand'],
            ];

            if (array_key_exists('parent_id', $order_item)) {
                $formatted_order_item['product_id']         = $order_item['parent_id'];
                $formatted_order_item['product_variant_id'] = $order_item['id'];
                $formatted_order_item['product_variant']    = $order_item['variant'];
            } else {
                $formatted_order_item['product_id'] = $order_item['id'];
            }

            $formatted_order_items[] = $formatted_order_item;
        }

//        error_log(print_r($order_items, true));

//        error_log(print_r($formatted_order_items, true));

        return $formatted_order_items;
    }
}