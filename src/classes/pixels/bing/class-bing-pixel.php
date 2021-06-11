<?php

// TODO https://help.ads.microsoft.com/apex/index/3/en/56910
// TODO https://bingadsuet.azurewebsites.net/UETDirectOnSite_ReportCustomEvents.html
// TODO view-source:https://bingadsuet.azurewebsites.net/UETDirectOnSite_ReportCustomEvents.html

namespace WGACT\Classes\Pixels\Bing;

use WGACT\Classes\Pixels\Pixel;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Bing_Pixel extends Pixel
{
    protected $pixel_name;

    public function __construct($options)
    {
        parent::__construct($options);

        $this->pixel_name = 'bing';
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

            window.uetq = window.uetq || [];

            (function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[],f=function(){var o={ti:"<?php echo $this->options_obj->bing->uet_tag_id ?>"};o.q=w[u],w[u]=new UET(o),w[u].push("pageLoad")},n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function(){var s=this.readyState;s&&s!=="loaded"&&s!=="complete"||(f(),n.onload=n.onreadystatechange=null)},i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)})(window,document,"script","//bat.bing.com/bat.js","uetq");
        <?php
        // @formatter:on
    }

    public function inject_product_category()
    {
        ?>

            window.uetq.push('event', '', {
                'ecomm_pagetype': 'category'
            });
        <?php
    }

    public function inject_search()
    {
        ?>

            window.uetq.push('event', '', {
                'ecomm_pagetype': 'searchresults'
            });
        <?php
    }

    public function inject_product($product, $product_attributes)
    {
        ?>

            window.uetq.push('event', '', {
                'ecomm_pagetype': 'product',
                'ecomm_prodid'  : '<?php echo $product_attributes['product_id_compiled'] ?>'
            });
        <?php
    }

    public function inject_cart($cart, $cart_total)
    {
        ?>

            window.uetq.push('event', '', {
                'ecomm_pagetype': 'cart',
                'ecomm_prodid'  : <?php echo json_encode($this->get_cart_ids($cart)) . PHP_EOL ?>
            });
        <?php
    }


    public function inject_order_received_page($order, $order_total)
    {
        echo "
            wooptpmExists().then(function(){
                if (!wooptpm.isOrderIdStored('" . $order->get_id() . "')) {
                    window.uetq.push('event', 'purchase', {
                        'ecomm_pagetype': 'purchase',
                        'ecomm_prodid'  : " . json_encode($this->get_order_item_ids($order)) . ",
                        'revenue_value' : " . $order_total . ",
                        'currency'      : '" . $order->get_currency() . "'
                    });
                }
            });

        ";
    }
}