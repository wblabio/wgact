<?php


namespace WGACT\Classes\Pixels;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google_Standard_Ecommerce extends Google_Pixel
{
    public function inject_order_received_page($order, $order_total, $order_item_ids)
    {
        error_log('standard ecommerce');
    }
}