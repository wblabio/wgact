<?php


namespace WGACT\Classes\Pixels\Google;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google_Analytics_UA_Standard_Pixel extends Google_Analytics
{
    public function __construct()
    {
        parent::__construct();
    }

    public function inject_order_received_page($order, $order_total, $is_new_customer)
    {
        $order_currency = $this->get_order_currency($order);

        ?>

                if  ((typeof wooptpm !== "undefined") && !wooptpm.isOrderIdStored(<?php echo $order->get_id() ?>)) {
                    gtag('event', 'purchase', <?php echo $this->get_event_purchase_json($order, $order_total, $order_currency, $is_new_customer, 'ga_ua') ?>);
                }
        <?php
    }
}