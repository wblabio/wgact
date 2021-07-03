<?php

namespace WGACT\Classes\Pixels\TikTok;

use WGACT\Classes\Pixels\Pixel;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class TikTok_Pixel extends Pixel
{
    protected $pixel_name;

    public function __construct($options)
    {
        parent::__construct($options);

        $this->pixel_name = 'tiktok';
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

            !function (w, d, t) {
                w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var o=document.createElement("script");o.type="text/javascript",o.async=!0,o.src=i+"?sdkid="+e+"&lib="+t;var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};
                ttq.load('<?php echo $this->options_obj->tiktok->pixel_id ?>');
                ttq.page();
            }(window, document, 'ttq');

            ttq.track('Browse')

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
        $formatted_order_items = $this->get_order_items_formatted_for_purchase_event($order);

        $data = [];
        foreach ($formatted_order_items as $key => $item) {

            $tiktok_formatted_item['content_id']   = $item['id'];
            $tiktok_formatted_item['content_type'] = 'product';
            $tiktok_formatted_item['content_name'] = $item['name'];
            $tiktok_formatted_item['quantity']     = $item['quantity'];
            $tiktok_formatted_item['price']        = $item['price'];

            $data[] = $tiktok_formatted_item;
        }

        echo "
            wooptpmExists().then(function(){
                if (!wooptpm.isOrderIdStored('" . $order->get_id() . "')) {
                    ttq.track('Purchase', {
                       contents: " . json_encode($data) . ",
                        'value': " . $order_total . ",
                        'currency': '" . $order->get_currency() . "',
                      });
                }
            });
        ";
    }
}