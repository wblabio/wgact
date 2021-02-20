<?php

namespace WGACT\Classes\Pixels;

// TODO disable Yoast SEO Open Graph wp_option: wpseo_social => opengraph => true / false

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Facebook_Microdata extends Pixel
{

    use Trait_Product;

    public function inject_product($product_id, $product, $product_attributes)
    {
        $product_microdata = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            'productID'   => $product_id,
            'name'        => $product->get_name(),
            'description' => $this->get_description($product),
            'url'         => get_permalink(),
            'image'       => wp_get_attachment_url($product->get_image_id()),
            'brand'       => $product_attributes['brand'],
            'offers'      => [
                [
                    '@type'         => 'Offer',
                    'price'         => $product->get_price(),
                    'priceCurrency' => get_woocommerce_currency(),
                    'itemCondition' => 'https://schema.org/' . $this->get_schema_condition($product),
                    'availability'  => 'https://schema.org/' . $this->get_schema_stock_status($product),
                ]
            ],
            //            'additionalProperty' => [
            //                [
            //                    '@type'      => 'PropertyValue',
            //                    'propertyID' => 'item_group_id',
            //                    'value'      => 'fb_tshirts'
            //                ]
            //            ]
        ];

        ?>

        <script type="application/ld+json">
            <?php echo json_encode($product_microdata) ?>


        </script>
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