<?php

namespace WGACT\Classes\Pixels\Snapchat;

use WGACT\Classes\Pixels\Pixel;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Snapchat_Pixel extends Pixel
{
    protected $pixel_name;

    public function __construct($options)
    {
        parent::__construct($options);

        $this->pixel_name = 'snapchat';
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

        (function(e,t,n){if(e.snaptr)return;var a=e.snaptr=function()
        {a.handleRequest?a.handleRequest.apply(a,arguments):a.queue.push(arguments)};
            a.queue=[];var s='script';r=t.createElement(s);r.async=!0;
            r.src=n;var u=t.getElementsByTagName(s)[0];
            u.parentNode.insertBefore(r,u);})(window,document,
            'https://sc-static.net/scevent.min.js');

        snaptr('init', '<?php echo $this->options_obj->snapchat->pixel_id ?>', {});

        snaptr('track', 'PAGE_VIEW');

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
                    snaptr('track', 'PURCHASE', {
                        'currency'       : '" . $order->get_currency() . "',
                        'price'          : " . $order_total . ",
                        'transaction_id' : '" . $order->get_order_number() . "',
                        'item_ids'       : " . json_encode($this->get_order_item_ids($order)) . ",
                    });
                }
            });
        ";
    }
}