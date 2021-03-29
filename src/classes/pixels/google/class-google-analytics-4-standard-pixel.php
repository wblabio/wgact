<?php


namespace WGACT\Classes\Pixels\Google;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google_Analytics_4_Standard_Pixel extends Google_Analytics
{
    public function __construct()
    {
        parent::__construct();
    }

    public function inject_order_received_page($order, $order_total, $is_new_customer)
    {
        $gtag_data = [
            'send_to'        => [],
            'transaction_id' => $order->get_order_number(),
            'currency'       => $this->get_order_currency($order),
            'discount'       => $order->get_total_discount(),
            'affiliation'    => (string)get_bloginfo('name'),
            'tax'            => (float)$order->get_total_tax(),
            'shipping'       => (float)$order->get_total_shipping(),
            'value'          => (float)$order->get_total(),
            'items'          => $this->get_formatted_order_items($order),
        ];

        array_push($gtag_data['send_to'], $this->options_obj->google->analytics->ga4->measurement_id);

        ?>

                if  ((typeof wooptpm !== "undefined") && !wooptpm.isOrderIdStored(<?php echo $order->get_id() ?>)) {
                  gtag('event', 'purchase', <?php echo json_encode($gtag_data) ?>);
                }
        <?php
    }
}