<?php

namespace WGACT\Classes\Pixels;

use WC_Order;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

trait Trait_Shop
{
    protected function get_list_name_suffix(): string
    {
        $list_suffix = '';

        if (is_product_category()) {

            $category    = get_queried_object();
            $list_suffix = ' | ' . $category->name;
            $list_suffix = $this->add_parent_category_name($category, $list_suffix);
        } else if (is_product_tag()) {
            $tag         = get_queried_object();
            $list_suffix = ' | ' . $tag->name;
        }

        return $list_suffix;
    }

    protected function add_parent_category_name($category, $list_suffix)
    {
        if ($category->parent > 0) {

            $parent_category = get_term_by('id', $category->parent, 'product_cat');
            $list_suffix     = ' | ' . $parent_category->name . $list_suffix;
            $list_suffix     = $this->add_parent_category_name($parent_category, $list_suffix);
        }

        return $list_suffix;
    }

    protected function get_list_id_suffix(): string
    {
        $list_suffix = '';

        if (is_product_category()) {
            $category    = get_queried_object();
            $list_suffix = '.' . $category->slug;
            $list_suffix = $this->add_parent_category_id($category, $list_suffix);
        } else if (is_product_tag()) {
            $tag         = get_queried_object();
            $list_suffix = '.' . $tag->slug;
        }

        return $list_suffix;
    }

    protected function add_parent_category_id($category, $list_suffix)
    {
        if ($category->parent > 0) {

            $parent_category = get_term_by('id', $category->parent, 'product_cat');
//            error_log(print_r($parent_category, true));
            $list_suffix     = '.' . $parent_category->slug . $list_suffix;
            $list_suffix     = $this->add_parent_category_id($parent_category, $list_suffix);
        }

        return $list_suffix;
    }

    // https://stackoverflow.com/a/49616130/4688612
    protected function get_order_from_order_received_page()
    {
        global $wp;

        $order_id = absint($wp->query_vars['order-received']);

        if (empty($order_id) || $order_id == 0) {

            wc_get_logger()->debug(
                'WooCommerce couldn\'t retrieve the order ID from $wp->query_vars[\'order-received\']',
                ['source' => 'wooptpm']
            );

            wc_get_logger()->debug(
                print_r($wp->query_vars, true),
                ['source' => 'wooptpm']
            );

            return false;
        } else {
            return new WC_Order($order_id);
        }
    }
}