<?php

// TODO add enhanced match email hash to uncached pages like cart and purchase confirmation page
// TODO check if more values can be passed to product and category pages

namespace WGACT\Classes\Pixels;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Pinterest extends Pixel
{
    public function inject_everywhere()
    {
        // @formatter:off
        ?>

        <script>
            !function(e){if(!window.pintrk){window.pintrk = function () {
                window.pintrk.queue.push(Array.prototype.slice.call(arguments))};var
                n=window.pintrk;n.queue=[],n.version="3.0";var
                t=document.createElement("script");t.async=!0,t.src=e;var
                r=document.getElementsByTagName("script")[0];
                r.parentNode.insertBefore(t,r)}}("https://s.pinimg.com/ct/core.js");
            // pintrk('load', '1111111111111', {em: '<user_email_address>'});
            pintrk('load', '<?php echo $this->options_obj->pinterest->pixel_id ?>');
            pintrk('page');
        </script>
        <?php
        // @formatter:on

    }

    public function inject_product_category()
    {
        ?>
        <script>
            pintrk('track', 'viewcategory');
        </script>
        <?php

    }

    public function inject_search()
    {

        ?>
        <script>
            pintrk('track', 'search', {
                search_query: '<?php echo get_search_query() ?>'
            });
        </script>
        <?php

    }

    public function inject_product($product_id, $product)
    {
        ?>

        <script>
            pintrk('track', 'pagevisit');
        </script>
        <?php
    }

    public function inject_cart($cart, $cart_total)
    {
        ?>

        <script>
            pintrk('track', 'addtocart', {
                value         : <?php echo $cart_total ?>,
                order_quantity: <?php echo count($cart) ?>,
                currency      : '<?php echo get_woocommerce_currency() ?>'
            });
        </script>

        <?php
    }

    public function inject_order_received_page($order, $order_total, $order_item_ids)
    {
        ?>

        <script>
            if ((typeof wgact !== "undefined") && !wgact.isOrderIdStored(<?php echo $order->get_id() ?>)) {
                pintrk('track', 'checkout', {
                    value         : <?php echo $order_total ?>,
                    order_quantity: <?php echo count($order->get_items()) ?>,
                    currency      : '<?php echo $order->get_currency() ?>',
                    order_id      : '<?php echo $order->get_order_number(); ?>',
                    product_ids   : <?php echo json_encode($order_item_ids) ?>
                });
            }
        </script>
        <?php
    }
}