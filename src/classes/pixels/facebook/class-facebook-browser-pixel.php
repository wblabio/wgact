<?php

namespace WGACT\Classes\Pixels\Facebook;

use WGACT\Classes\Pixels\Pixel;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Facebook_Browser_Pixel extends Pixel
{
    protected $pixel_name;

    public function __construct($options)
    {
        parent::__construct($options);

        $this->pixel_name = 'facebook';
    }

    public function inject_everywhere()
    {
        $facebook_data_layer = [
                'dynamic_remarketing' => [
                        'id_type' => $this->get_dyn_r_id_type(),
                ],
                'pixel_id' => $this->options_obj->facebook->pixel_id,
                'capi' => $this->options_obj->facebook->capi->token ? true : false,
        ];


        // @formatter:off
        ?>
            wooptpmDataLayer.pixels.<?php echo $this->pixel_name ?> = <?php echo json_encode($facebook_data_layer) ?>;

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
        // handled on front-end
    }

    public function inject_cart($cart, $cart_total)
    {
        // AddToCart event is triggered in front-end event layer
    }

    public function inject_product($product, $product_attributes)
    {
        // handled on front-end
    }

    public function inject_order_received_page($order, $order_total, $is_new_customer)
    {
        $data = [
            'value'        => $order_total,
            'currency'     => get_woocommerce_currency(),
            'content_ids'  => $this->get_order_item_ids($order),
            'content_type' => 'product',
        ];

        echo "
            wooptpmExists().then(function(){
                if (!wooptpm.isOrderIdStored('". $order->get_id() . "')) {
                    fbq('track', 'Purchase', " . json_encode($data) . ", {
                        'eventID': '" . $order->get_order_number() . "',
                    });
                }
            });

        ";
    }
}