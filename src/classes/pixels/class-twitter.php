<?php

// TODO check if more values can be passed to product and cart pages

namespace WGACT\Classes\Pixels;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Twitter extends Pixel
{
    public function inject_everywhere()
    {
        // @formatter:off
        ?>

        <script>
            !function(e,t,n,s,u,a){e.twq||(s=e.twq=function(){s.exe?s.exe.apply(s,arguments):s.queue.push(arguments);
            },s.version='1.1',s.queue=[],u=t.createElement(n),u.async=!0,u.src='//static.ads-twitter.com/uwt.js',
                a=t.getElementsByTagName(n)[0],a.parentNode.insertBefore(u,a))}(window,document,'script');
            twq('init','<?php echo $this->options_obj->twitter->pixel_id ?>');
        </script>

        <?php if(!is_order_received_page()): ?>

        <script>
            twq('track','PageView');
        </script>
        <?php endif; ?>
        <?php
        // @formatter:on

    }

    public function inject_search()
    {
        ?>

        <script>
            twq('track', 'Search');
        </script>
        <?php
    }

    public function inject_product($product_id, $product)
    {
        ?>

        <script>
            twq('track', 'ViewContent');
        </script>
        <?php
    }

    public function inject_cart($cart, $cart_total)
    {
        ?>

        <script>
            twq('track', 'AddToCart');
        </script>
        <?php
    }

    public function inject_order_received_page($order, $order_total, $order_item_ids)
    {
        // TODO find out under which circumstances to use different values in content_type

        ?>

        <script>
            if ((typeof wgact !== "undefined") && !wgact.isOrderIdStored(<?php echo $order->get_id() ?>)) {
                twq('track', 'Purchase', {
                    value       : '<?php echo $order_total ?>',
                    currency    : '<?php echo $order->get_currency() ?>',
                    num_items   : '<?php echo count($order->get_items()) ?>',
                    content_ids : <?php echo json_encode($order_item_ids) ?>,
                    content_type: 'product',
                    order_id    : '<?php echo $order->get_order_number(); ?>'
                });
            }

        </script>
        <?php
    }
}