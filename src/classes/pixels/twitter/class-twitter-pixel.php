<?php

// TODO check if more values can be passed to product and cart pages

namespace WGACT\Classes\Pixels\Twitter;

use WGACT\Classes\Pixels\Pixel;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Twitter_Pixel extends Pixel
{
    protected $pixel_name;

    public function __construct()
    {
        parent::__construct();

        $this->pixel_name = 'twitter';
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

            !function(e,t,n,s,u,a){e.twq||(s=e.twq=function(){s.exe?s.exe.apply(s,arguments):s.queue.push(arguments);
            },s.version='1.1',s.queue=[],u=t.createElement(n),u.async=!0,u.src='//static.ads-twitter.com/uwt.js',
                a=t.getElementsByTagName(n)[0],a.parentNode.insertBefore(u,a))}(window,document,'script');

            twq('init','<?php echo $this->options_obj->twitter->pixel_id ?>');
        <?php if(!is_order_received_page()): ?>

            twq('track','PageView');
        <?php endif; ?>
        <?php
        // @formatter:on

    }

    public function inject_search()
    {
        ?>

            twq('track', 'Search');
        <?php
    }

    public function inject_product($product, $product_attributes)
    {
        ?>

            twq('track', 'ViewContent');
        <?php
    }

    public function inject_cart($cart, $cart_total)
    {
        ?>

            twq('track', 'AddToCart');
        <?php
    }

    public function inject_order_received_page($order, $order_total)
    {
        // TODO find out under which circumstances to use different values in content_type

        ?>

            if ((typeof wooptpm !== "undefined") && !wooptpm.isOrderIdStored(<?php echo $order->get_id() ?>)) {
                twq('track', 'Purchase', {
                    value       : '<?php echo $order_total ?>',
                    currency    : '<?php echo $order->get_currency() ?>',
                    num_items   : '<?php echo count($order->get_items()) ?>',
                    content_ids : <?php echo json_encode($this->get_order_item_ids($order)) ?>,
                    content_type: 'product',
                    order_id    : '<?php echo $order->get_order_number(); ?>'
                });
            }

        <?php
    }
}