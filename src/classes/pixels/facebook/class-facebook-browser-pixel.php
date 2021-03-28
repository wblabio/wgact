<?php

namespace WGACT\Classes\Pixels\Facebook;

use WGACT\Classes\Pixels\Pixel;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Facebook_Browser_Pixel extends Pixel
{
    protected $pixel_name;

    public function __construct()
    {
        parent::__construct();

        $this->pixel_name = 'facebook';
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
        <?php
        // @formatter:on
    }

    public function inject_search()
    {
        ?>

            fbq('track', 'Search');
        <?php
    }

    public function inject_product($product, $product_attributes)
    {
        ?>

            fbq('track', 'ViewContent', {
                'content_type'    : 'product',
                'content_name'    : '<?php echo $product->get_name() ?>',
                'content_category': <?php echo json_encode($this->get_product_category($product->get_id())) ?>,
                'content_ids'     : '<?php echo $product_attributes['dyn_r_ids'][$this->get_dyn_r_id_type()] ?>',
                'currency'        : '<?php echo $this->options_obj->shop->currency ?>',
                'value'           : <?php echo (float)$product->get_price() . PHP_EOL ?>
            });
        <?php
    }

    public function inject_cart($cart, $cart_total)
    {
        // AddToCart event is triggered in front-end event layer
    }

    public function inject_order_received_page($order, $order_total, $is_new_customer)
    {
        ?>

            if ((typeof wooptpm !== "undefined") && !wooptpm.isOrderIdStored(<?php echo $order->get_id() ?>)) {
                fbq('track', 'Purchase', {
                    'content_type': 'product',
                    'content_ids' : <?php echo json_encode($this->get_order_item_ids($order)) ?>,
                    'currency'    : '<?php echo $this->options_obj->shop->currency ?>',
                    'value'       : <?php echo $order_total . PHP_EOL ?>
                });
            }

        <?php
    }
}