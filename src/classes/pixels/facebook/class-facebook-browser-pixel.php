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
        $data = [
            'content_type'     => 'product',
            'content_name'     => (string)$product->get_name(),
            'content_category' => $this->get_product_category($product->get_id()),
            'content_ids'      => $product_attributes['dyn_r_ids'][$this->get_dyn_r_id_type()],
            'currency'         => (string)$this->options_obj->shop->currency,
            'value'            => (float)$product->get_price(),
        ];

        ?>

            fbq('track', 'ViewContent', <?php echo json_encode($data) ?>);
        <?php
    }

    public function inject_cart($cart, $cart_total)
    {
        // AddToCart event is triggered in front-end event layer
    }

    public function inject_order_received_page($order, $order_total, $is_new_customer)
    {
        $data = [
            'value'        => $order_total,
            'currency'     => $this->options_obj->shop->currency,
            'content_ids'  => $this->get_order_item_ids($order),
            'content_type' => 'product',
        ];

        echo "
            wooptpmExists().then(function(){
                if (!wooptpm.isOrderIdStored(". $order->get_order_number() . ")) {
                    fbq('track', 'Purchase', " . json_encode($data) . ");
                }
            });

        ";

//        $html = "
//            if ((typeof wooptpm !== \"undefined\") && !wooptpm.isOrderIdStored(" . $order->get_order_number() . ")) {
//                fbq('track', 'Purchase', " . json_encode($data) .  ");
//            }";
//
//        echo $html;
    }

//    protected function get_order_item_ids($order): array
//    {
//        $order_items       = $order->get_items();
//        $order_items_array = [];
//
//        foreach ((array)$order_items as $order_item) {
//
//            $product_id = $this->get_variation_or_product_id($order_item->get_data(), $this->options_obj->general->variations_output);
//
//            $product = wc_get_product($product_id);
//
//            // only continue if WC retrieves a valid product
//            if (!is_bool($product)) {
//
//                $dyn_r_ids           = $this->get_dyn_r_ids($product);
//                $product_id_compiled = $dyn_r_ids[$this->get_dyn_r_id_type()];
//
//                array_push($order_items_array, $product_id_compiled);
//            }
//        }
//
//        return $order_items_array;
//    }
}