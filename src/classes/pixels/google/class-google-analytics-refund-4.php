<?php

namespace WGACT\Classes\Pixels\Google;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google_Analytics_Refund_4 extends Google_Analytics
{
    public function __construct($options)
    {
        parent::__construct($options);
    }

    public function output_refund_to_frontend($order, $refund, $dataLayer_refund_items = null)
    {
        $data = [
            'send_to'        => $this->options_obj->google->analytics->ga4->measurement_id,
            'affiliation'    => get_bloginfo('name'),
            //'coupon'=> $order->get_coupon_codes()),
            'currency'       => (string)$this->get_order_currency($order),
            'items'          => (array)$dataLayer_refund_items,
            'transaction_id' => (string)$order->get_order_number(),
            'shipping'       => (float)$order->get_total_shipping(),
            'tax'            => (float)$order->get_total_tax(),
            'value'          => (float)$refund->get_amount(),
            //'value': (float)$order->get_total(),
        ];

        ?>
        <script>
            // console.log('pushed refund to dataLayer');
            gtag('event', 'refund', <?php echo json_encode($data)?>);
        </script>
        <?php
    }
}