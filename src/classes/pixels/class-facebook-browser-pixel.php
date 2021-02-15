<?php

namespace WGACT\Classes\Pixels;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Facebook_Browser_Pixel extends Pixel
{
    public function inject_everywhere()
    {
        // @formatter:off
        ?>

        <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};
                if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
                n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t,s)}(window, document,'script',
                'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '<?php echo $this->options_obj->facebook->pixel_id ?>');
            fbq('track', 'PageView');
        </script>
        <noscript><img height="1" width="1" style="display:none"
                       src="https://www.facebook.com/tr?id=<?php echo $this->options_obj->facebook->pixel_id ?>&ev=PageView&noscript=1"
            /></noscript>
        <?php
        // @formatter:on
    }

    public function inject_search()
    {
        ?>

        <script>
            fbq('track', 'Search');
        </script>
        <?php
    }

    public function inject_product($product_id, $product, $product_attributes)
    {
        ?>

        <script>
            fbq('track', 'ViewContent', {
                'content_type'    : 'product',
                'content_name'    : '<?php echo $product->get_name() ?>',
                'content_category': <?php echo json_encode($this->get_product_category($product_id)) ?>,
                'content_ids'     : '<?php echo $product_id ?>',
                'currency'        : '<?php echo $this->options_obj->shop->currency ?>',
                'value'           : <?php echo (float)$product->get_price() . PHP_EOL ?>
            });
        </script>
        <?php
    }

    public function inject_cart($cart, $cart_total)
    {
        ?>
        <script>
            fbq('track', 'AddToCart', {
                'content_name': 'Shopping Cart',
                'content_type': 'product',
                'content_ids' : <?php echo json_encode($this->get_cart_ids($cart)) ?>,
                'currency'    : '<?php echo $this->options_obj->shop->currency ?>',
                'value'       : <?php echo $cart_total . PHP_EOL ?>
            });
        </script>
        <?php
    }

    public function inject_order_received_page($order, $order_total, $order_item_ids)
    {
        ?>

        <script>
            if ((typeof wgact !== "undefined") && !wgact.isOrderIdStored(<?php echo $order->get_id() ?>)) {
                fbq('track', 'Purchase', {
                    'content_type': 'product',
                    'content_ids' : <?php echo json_encode($order_item_ids) ?>,
                    'currency'    : '<?php echo $this->options_obj->shop->currency ?>',
                    'value'       : <?php echo $order_total . PHP_EOL ?>
                });
            }
        </script>

        <?php
    }
}