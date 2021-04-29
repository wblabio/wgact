<?php

namespace WGACT\Classes\Pixels\Google;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google_Analytics_Refund_UA extends Google_Analytics
{
    public function __construct($options)
    {
        parent::__construct($options);
    }

    public function output_refund_to_frontend($order, $refund, $dataLayer_refund_items = null)
    {
        $data = [
            'send_to'        => $this->options_obj->google->analytics->universal->property_id,
            'transaction_id' => (string)$order->get_order_number(),
            //'coupon'=> (array)$order->get_coupon_codes()),
            'affiliation'    => (string)get_bloginfo('name'),
            'currency'       => (string)$this->get_order_currency($order),
            //'value'=> (float)$order->get_total(),
            'value'          => (float)$refund->get_amount(),
            'tax'            => (float)$order->get_total_tax(),
            'shipping'       => (float)$order->get_total_shipping(),
            'items'          => (array)$dataLayer_refund_items,
        ];

        ?>
        <script>
            // console.log('pushed refund to dataLayer');
            gtag('event', 'refund', <?php echo json_encode($data) ?>);
        </script>
        <?php
    }
}