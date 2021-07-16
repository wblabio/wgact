<?php

// TODO add enhanced match email hash to uncached pages like cart and purchase confirmation page
// TODO check if more values can be passed to product and category pages

namespace WGACT\Classes\Pixels\Pinterest;

use WGACT\Classes\Pixels\Pixel;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Pinterest_Pixel extends Pixel
{
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

            pintrk('load', '<?php echo $this->options_obj->pinterest->pixel_id ?>');
            pintrk('page');
        <?php
        // @formatter:on

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
        echo "
            wooptpmExists().then(function(){
                if (!wooptpm.isOrderIdStored('" . $order->get_id() . "')) {
                    pintrk('track', 'checkout', {
                        'value'         : " . $order_total . ",
                        'order_quantity': " . count($order->get_items()) . ",
                        'currency'      : '" . $order->get_currency() . "',
                        'order_id'      : '" . $order->get_order_number() . "',
                        'product_ids'   : " . json_encode($this->get_order_item_ids($order)) . "
                    });
                }
            });
        ";
    }


}