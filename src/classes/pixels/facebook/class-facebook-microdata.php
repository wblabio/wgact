<?php

namespace WGACT\Classes\Pixels\Facebook;

// TODO disable Yoast SEO Open Graph wp_option: wpseo_social => opengraph => true / false

use WGACT\Classes\Pixels\Pixel;
use WGACT\Classes\Pixels\Trait_Product;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Facebook_Microdata extends Pixel
{
    use Trait_Product;

    public function __construct($options)
    {
        parent::__construct($options);
    }

    public function inject_product($product, $product_attributes)
    {
        if (wp_get_post_parent_id($product->get_id()) <> 0) {
            $parent_product_id = wp_get_post_parent_id($product->get_id());
            $parent_product    = wc_get_product($parent_product_id);
        } else {
            $parent_product_id = $product->get_id();
            $parent_product    = $product;
        }

        $product_microdata = [
            '@context'           => 'https://schema.org',
            '@type'              => 'Product',
            'productID'          => $product_attributes['product_id_compiled'],
            'name'               => $product->get_name(),
            'description'        => $this->get_description($product),
            'url'                => get_permalink(),
            'image'              => wp_get_attachment_url($product->get_image_id()),
            'brand'              => $product_attributes['brand'],
            'offers'             => [
                [
                    '@type'         => 'Offer',
                    'price'         => $product->get_price(),
                    'priceCurrency' => get_woocommerce_currency(),
                    'itemCondition' => 'https://schema.org/' . $this->get_schema_condition($product),
                    'availability'  => 'https://schema.org/' . $this->get_schema_stock_status($product),
                ]
            ],
            'additionalProperty' => [
                [
                    '@type'      => 'PropertyValue',
                    'propertyID' => 'item_group_id',
                    'value'      => $this->get_compiled_product_id($parent_product_id, $parent_product->get_sku(), $this->options,''),
                ]
            ]
        ];

        ?>
        <!-- START Facebook Microdata script -->
        <script type="application/ld+json">
            <?php echo json_encode($product_microdata) ?>

        </script>
        <!-- END Facebook Microdata script -->
        <?php
    }

    private function get_description($product): string
    {
        $text = strip_tags($product->get_description());

        if (strlen($text) > 497) {
            $text = substr($text, 0, 497) . '...';
        }

        return $text;
    }

    // https://schema.org/ItemAvailability
    // Possible WC values: instock, outofstock, onbackorder
    private function get_schema_stock_status($product): string
    {
        $wc_stock_status = $product->get_stock_status($product);

        if ('instock' == $wc_stock_status) {
            return 'InStock';
        } elseif ('outofstock' == $wc_stock_status) {
            return 'OutOfStock';
        } elseif ('onbackorder' == $wc_stock_status) {
            return 'PreOrder';
        } else {
            return '';
        }
    }

    // https://schema.org/OfferItemCondition
    // Possible WC values: Standard WC doesn't offer condition
    private function get_schema_condition($product): string
    {
        return 'NewCondition';
    }
}