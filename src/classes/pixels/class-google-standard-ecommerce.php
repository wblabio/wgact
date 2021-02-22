<?php


namespace WGACT\Classes\Pixels;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google_Standard_Ecommerce extends Google_Pixel
{
    public function inject_order_received_page($order, $order_total, $order_item_ids, $is_new_customer)
    {
        $order_currency = $this->get_order_currency($order);

        ?>
        <script>
            if ((typeof wgact !== "undefined") && !wgact.isOrderIdStored(<?php echo $order->get_id() ?>)) {
                gtag('event', 'purchase', <?php echo $this->get_event_purchase_json($order, $order_total, $order_currency, $is_new_customer, 'analytics') ?>);
            }
        </script>
        <?php
    }
}