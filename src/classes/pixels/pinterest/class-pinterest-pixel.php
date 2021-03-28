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

    public function __construct()
    {
        parent::__construct();

        $this->pixel_name = 'pinterest';
    }

    public function inject_everywhere()
    {
        // @formatter:off
        ?>
            wooptpmDataLayer.pixels.<?php echo $this->pixel_name ?> = {
                'dynamic_remarketing': {
                    'id_type': '<?php echo $this->get_dyn_r_id_type() ?>'
                }
            };

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
        ?>
            pintrk('track', 'viewcategory');
        <?php

    }

    public function inject_search()
    {

        ?>
            pintrk('track', 'search', {
                search_query: '<?php echo get_search_query() ?>'
            });
        <?php

    }

    public function inject_product($product, $product_attributes)
    {
        ?>

            pintrk('track', 'pagevisit');
        <?php
    }

    public function inject_cart($cart, $cart_total)
    {
        ?>

            pintrk('track', 'addtocart', {
                value         : <?php echo $cart_total ?>,
                order_quantity: <?php echo count($cart) ?>,
                currency      : '<?php echo get_woocommerce_currency() ?>'
            });

        <?php
    }

    public function inject_order_received_page($order, $order_total)
    {
        ?>

            if ((typeof wooptpm !== "undefined") && !wooptpm.isOrderIdStored(<?php echo $order->get_id() ?>)) {
                pintrk('track', 'checkout', {
                    value         : <?php echo $order_total ?>,
                    order_quantity: <?php echo count($order->get_items()) ?>,
                    currency      : '<?php echo $order->get_currency() ?>',
                    order_id      : '<?php echo $order->get_order_number(); ?>',
                    product_ids   : <?php echo json_encode($this->get_order_item_ids($order)) ?>
                });
            }
        <?php
    }
}