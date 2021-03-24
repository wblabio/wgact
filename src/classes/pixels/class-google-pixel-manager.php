<?php


namespace WGACT\Classes\Pixels;

use WGACT\Classes\Admin\Environment_Check;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google_Pixel_Manager extends Google_Pixel
{
    use Trait_Google;

    private $gads;

    public function __construct($options, $options_obj)
    {
        parent::__construct($options, $options_obj);

        $this->gads = new Google_Ads($this->options, $this->options_obj);

        add_action('wp_enqueue_scripts', [$this, 'google_front_end_scripts']);

        if ($this->options_obj->google->analytics->eec) {

            add_action('woocommerce_order_refunded', [$this, 'eec_action_woocommerce_order_refunded'], 10, 2);
            add_action('wp_footer', [$this, 'process_refund_to_frontend']);
            add_action('admin_footer', [$this, 'process_refund_to_frontend']);
        }
    }

    public function google_front_end_scripts()
    {
        if (wga_fs()->is__premium_only()) {
            wp_enqueue_script('eec', plugin_dir_url(__DIR__) . '../js/public/eec__premium_only.js', [], WGACT_CURRENT_VERSION, false);
            wp_localize_script('eec', 'ajax_object', ['ajax_url' => admin_url('admin-ajax.php')]);
        }
    }

    public function eec_action_woocommerce_order_refunded($order_id, $refund_id)
    {
        // safe refund task into database
        update_post_meta($refund_id, 'wooptpm_refund_processed', false);
    }

    /**
     * Processes all prepared refunds in post_meta and outputs them on the frontend into the dataLayer.
     * We only process this on the frontend since the output on is_order_received_page has a higher chance to get
     * processed properly through GTM.
     */
    public function process_refund_to_frontend()
    {
        global $wpdb;

        // the following condition is to limit running the following script and potentially overload the server
        if (is_admin() || is_order_received_page()) {

            $sql = "SELECT meta_id, post_id FROM wp_postmeta WHERE meta_key = 'wooptpm_refund_processed' AND `meta_value` = false";

            $results = $wpdb->get_results($sql);

            foreach ($results as $result) {

                $refund   = new WC_Order_Refund($result->post_id);
                $order_id = $refund->get_parent_id();

                $refund_items = $refund->get_items();

                $dataLayer_refund_items = [];
                foreach ($refund_items as $refund_item) {

                    $dataLayer_refund_items[] = [
                        'id'       => $refund_item->get_product_id(),
                        'quantity' => $refund_item->get_quantity()
                    ];
                }

                $this->output_refund_to_frontend($order_id, $dataLayer_refund_items);

                update_post_meta($result->post_id, 'wooptpm_refund_processed', true);
            }
        }
    }

    public function inject_everywhere()
    {
        if ($this->options_obj->google->optimize->container_id) {
            ?>

            <script async src="https://www.googleoptimize.com/optimize.js?id=<?php
            echo $this->options_obj->google->optimize->container_id ?>"></script>
            <?php
        }

        if (!$this->options_obj->google->gtag->deactivation) {
            ?>

            <script async src="https://www.googletagmanager.com/gtag/js?id=<?php
            echo $this->get_gtag_id() ?>"></script>
            <script<?php echo
            $this->options_obj->shop->cookie_consent_mgmt->cookiebot->active ? ' data-cookieconsent="ignore"' : ''; ?>>
                window.dataLayer = window.dataLayer || [];

                function gtag() {
                    dataLayer.push(arguments);
                }

                <?php echo $this->options_obj->google->consent_mode->active ? $this->consent_mode_gtag_html() : ''; ?>

                gtag('js', new Date());

            </script>

            <?php
        }

        ?>

        <script>
            <?php foreach ($this->conversion_identifiers as $conversion_id => $conversion_label): ?>
            <?php echo $this->options_obj->google->ads->conversion_id ? $this->gtag_config($conversion_id, 'ads') : PHP_EOL; ?>
            <?php endforeach; ?>

            <?php echo $this->options_obj->google->analytics->universal->property_id ? $this->gtag_config($this->options_obj->google->analytics->universal->property_id, 'ga_ua') . PHP_EOL : PHP_EOL; ?>
            <?php echo $this->options_obj->google->analytics->ga4->measurement_id ? $this->gtag_config($this->options_obj->google->analytics->ga4->measurement_id, 'ga_4') : PHP_EOL; ?>

        </script>
        <?php

        if ($this->options_obj->google->consent_mode->active && (new Environment_Check())->is_borlabs_cookie_active()) {
            $this->inject_borlabs_consent_mode_update();
        }

        if ($this->is_google_ads_active() && $this->options_obj->google->ads->phone_conversion_number) {
            $this->inject_phone_conversion_number_html__premium_only();
        }

        $this->inject_data_layer_init();
        $this->inject_data_layer_shop();
        $this->inject_data_layer_product();
        $this->inject_data_layer_pixels();
    }

    private function inject_data_layer_pixels()
    {

//        $data = [
//                'google' => [
//                        'ads' => [
//                            'dynamic_remarketing' => $this->options_obj->google->ads->dynamic_remarketing,
//                            'conversionIds' => $this->get_google_ads_conversion_ids(),
//                            'google_business_vertical' => $this->google_business_vertical,
//                        ],
//                        'analytics' => [
//                                'universal' => [
//                                        'property_id' => $this->options_obj->google->analytics->universal->property_id
//                                ],
//                                'ga4'=> [
//                                        'measurement_id' => $this->options_obj->google->analytics->ga4->measurement_id,
//                                ]
//                        ]
//                ],
//        ];

        ?>

        <script>
            wooptpmDataLayer['pixels'] = {
                'google': {
                    'ads': {
                        'dynamic_remarketing'     : <?php echo $this->options_obj->google->ads->dynamic_remarketing ?>,
                        'conversionIds'           : <?php echo json_encode($this->get_google_ads_conversion_ids()) ?>,
                        'google_business_vertical': '<?php echo $this->google_business_vertical ?>'
                    },
                    'analytics': {
                        'universal': {
                            'property_id': '<?php echo $this->options_obj->google->analytics->universal->property_id ?>',
                        },
                        'ga4': {
                            'measurement_id': '<?php echo $this->options_obj->google->analytics->ga4->measurement_id ?>',
                        }
                    }
                }
            };
        </script>
        <?php
    }

    private function inject_data_layer_init()
    {
        ?>
        <script>
            window.wooptpmDataLayer = window.wooptpmDataLayer || [];
            // window.wooptpmDataLayer['cart'] = window.wooptpmDataLayer['cart'] || {};
        </script>

        <?php
    }

    private function inject_data_layer_shop()
    {
        $data = [];

        if (is_product_category()) {
            $data['list_name'] = 'Product Category';
            $data['page_type'] = 'product_category';
        } elseif (is_product_tag()) {
            $data['list_name'] = 'Product Tag';
            $data['page_type'] = 'product_tag';
        } elseif (is_search()) {
            $data['list_name'] = 'Product Search';
            $data['page_type'] = 'search';
        } elseif (is_shop()) {
            $data['list_name'] = 'Shop';
            $data['page_type'] = 'product_shop';
        } elseif (is_product()) {
            $data['page_type'] = 'product';

            $product              = wc_get_product();
            $data['product_type'] = $product->get_type();
        } elseif (is_cart()) {
            $data['list_name'] = '';
            $data['page_type'] = 'cart';
        } else {
            $data['list_name'] = '';
        }

        $data['currency'] = get_woocommerce_currency();
        ?>

        <script>
            wooptpmDataLayer['shop'] = <?php echo json_encode($data) ?>;
        </script>
        <?php
    }

    private function inject_data_layer_product()
    {
        global $wp_query, $woocommerce;

        if (is_shop() || is_product_category() || is_product_tag() || is_search()) {

            $product_ids = [];
            $posts       = $wp_query->posts;
            foreach ($posts as $key => $post) {
                if ($post->post_type == 'product') {
                    array_push($product_ids, $post->ID);
                }
            }

            ?>

            <script>
                wooptpmDataLayer['visible_products'] = <?php echo json_encode($this->eec_get_visible_products($product_ids)) ?>;
            </script>
            <?php
        } elseif (is_cart()) {
            $visible_product_ids = [];
            $upsell_product_ids  = [];

            $items = $woocommerce->cart->get_cart();
            foreach ($items as $item => $values) {
                array_push($visible_product_ids, $values['data']->get_id());
                $product = wc_get_product($values['data']->get_id());

                // only continue if WC retrieves a valid product
                if (!is_bool($product)) {
                    $single_product_upsell_ids = $product->get_upsell_ids();
//                error_log(print_r($single_product_upsell_ids,true));

                    foreach ($single_product_upsell_ids as $item => $value) {
//                    error_log('item ' . $item);
//                    error_log('value' . $value);

                        if (!in_array($value, $upsell_product_ids, true)) {
                            array_push($upsell_product_ids, $value);
                        }
                    }
                }

            }

//            error_log(print_r($upsell_product_ids,true));

            ?>

            <script>
                wooptpmDataLayer['visible_products'] = <?php echo json_encode($this->eec_get_visible_products($visible_product_ids)) ?>;
                wooptpmDataLayer['upsell_products']  = <?php echo json_encode($this->eec_get_visible_products($upsell_product_ids)) ?>;
            </script>
            <?php
        } elseif (is_product()) {

            $visible_product_ids = [];

            $product = wc_get_product();
            array_push($visible_product_ids, $product->get_id());

            $related_products = wc_get_related_products($product->get_id());
            foreach ($related_products as $item => $value) {
                array_push($visible_product_ids, $value);
            }

            $upsell_product_ids = $product->get_upsell_ids();
            foreach ($upsell_product_ids as $item => $value) {
                array_push($visible_product_ids, $value);
            }
//            error_log(print_r($visible_product_ids, true));

            if ($product->get_type() === 'grouped') {
                $visible_product_ids = array_merge($visible_product_ids, $product->get_children());
            }

            ?>

            <script>
                wooptpmDataLayer['visible_products'] = <?php echo json_encode($this->eec_get_visible_products($visible_product_ids)) ?>;
            </script>
            <?php
        }
    }

    private function eec_get_visible_products($product_ids): array
    {
        $data = [];

        $position = 1;

        foreach ($product_ids as $key => $product_id) {

            $product = wc_get_product($product_id);

            // only continue if WC retrieves a valid product
            if (!is_bool($product)) {
                $data[$product->get_id()] = [
                    'id'       => (string)$product->get_id(),
                    'sku'      => (string)$product->get_sku(),
                    'name'     => (string)$product->get_name(),
                    'price'    => (int)$product->get_price(),
                    'brand'    => $this->get_brand_name($product->get_id()),
                    'category' => (array)$this->get_product_category($product->get_id()),
                    // 'variant'  => '',
                    'quantity' => (int)1,
                    'position' => (int)$position,
                    'dyn_r_ids' => [
                            'post_id' => (string)$product->get_id(),
                            'sku'     => (string)$product->get_sku(),
                            'gpf'   => 'woocommerce_gpf_' . (string)$product->get_id(),
                    ]
                ];
                $position++;
            }
        }

        return $data;
    }

    private function inject_phone_conversion_number_html__premium_only()
    {
        ?>

        <script>
            gtag('config', 'AW-<?php echo $this->options_obj->google->ads->conversion_id ?>/<?php echo $this->options_obj->google->ads->conversion_label ?>', {
                'phone_conversion_number': '<?php echo $this->options_obj->google->ads->phone_conversion_number ?>'
            });
        </script>
        <?php
    }

    private function inject_borlabs_consent_mode_update()
    {
        ?>

        <script>
            (function updateGoogleConsentMode() {
                if (typeof BorlabsCookie == "undefined" || typeof gtag == "undefined") {
                    window.setTimeout(updateGoogleConsentMode, 50);
                } else {
                    if (window.BorlabsCookie.checkCookieGroupConsent('statistics')) {
                        gtag('consent', 'update', {
                            'analytics_storage': 'granted'
                        });
                    }

                    if (window.BorlabsCookie.checkCookieGroupConsent('marketing')) {
                        gtag('consent', 'update', {
                            'ad_storage': 'granted'
                        });
                    }
                }
            })();
        </script>
        <?php
    }

    public function inject_product_category()
    {
        $this->gads->inject_product_category();

        if (wga_fs()->is__premium_only()) {
            if ($this->options_obj->google->analytics->eec) (new Google_Enhanced_Ecommerce($this->options, $this->options_obj))->inject_product_list_object('product_category');
        }
    }

    public function inject_product_tag()
    {
        if (wga_fs()->is__premium_only()) {
            if ($this->options_obj->google->analytics->eec) (new Google_Enhanced_Ecommerce($this->options, $this->options_obj))->inject_product_list_object('product_tag');
        }
    }

    public function inject_shop_top_page()
    {
        if (wga_fs()->is__premium_only()) {
            if ($this->options_obj->google->analytics->eec) (new Google_Enhanced_Ecommerce($this->options, $this->options_obj))->inject_product_list_object('shop');
        }
    }

    public function inject_search()
    {
        $this->gads->inject_search();

        if (wga_fs()->is__premium_only()) {
            if ($this->options_obj->google->analytics->eec) (new Google_Enhanced_Ecommerce($this->options, $this->options_obj))->inject_product_list_object('search');
        }
    }

    public function inject_product($product_id, $product, $product_attributes)
    {
        $this->gads->inject_product($product_id, $product, $product_attributes);

        if (wga_fs()->is__premium_only()) {
            if ($this->options_obj->google->analytics->eec) (new Google_Enhanced_Ecommerce($this->options, $this->options_obj))->inject_product($product_id, $product, $product_attributes);
        }
    }

    public function inject_cart($cart, $cart_total)
    {
        $this->gads->inject_cart($cart, $cart_total);

        if (wga_fs()->is__premium_only()) {
            if ($this->options_obj->google->analytics->eec) (new Google_Enhanced_Ecommerce($this->options, $this->options_obj))->inject_cart($cart, $cart_total);
        }
    }

    public function inject_order_received_page($order, $order_total, $order_item_ids, $is_new_customer)
    {
        if ($this->options_obj->google->ads->conversion_id) $this->gads->inject_order_received_page($order, $order_total, $order_item_ids, $is_new_customer);
        if ($this->is_google_analytics_active()) {

            // this is the same code for standard and eec, therefore using the same for both
            (new Google_Standard_Ecommerce($this->options, $this->options_obj))->inject_order_received_page($order, $order_total, $order_item_ids, $is_new_customer);
        }
    }

    private function get_gtag_id(): string
    {
        if ($this->options_obj->google->analytics->universal->property_id) {
            return $this->options_obj->google->analytics->universal->property_id;
        } elseif ($this->options_obj->google->analytics->ga4->measurement_id) {
            return $this->options_obj->google->analytics->ga4->measurement_id;
        } elseif ($this->options_obj->google->ads->conversion_id) {
            return 'AW-' . $this->options_obj->google->ads->conversion_id;
        }
    }

    protected function gtag_config($id, $channel = ''): string
    {
        if ('ads' === $channel) {
            return "gtag('config', 'AW-" . $id . "');" . PHP_EOL;
        } elseif ('ga_ua' === $channel) {

            $ga_ua_parameters = [
                'anonymize_ip'     => 'true', // must be a string for correct output
                'link_attribution' => $this->options_obj->google->analytics->link_attribution ? 'true' : 'false', // must be a string for correct output
            ];

            $ga_ua_parameters = apply_filters('woopt_pm_analytics_parameters', $ga_ua_parameters, $id);

            return "gtag('config', '" . $id . "', " . json_encode($ga_ua_parameters) . ");";
        } elseif ('ga_4' === $channel) {
            return "gtag('config', '" . $id . "');";
        }
    }

    private function consent_mode_gtag_html(): string
    {
        $data = [
            'ad_storage'        => 'denied',
            'analytics_storage' => 'denied',
            'wait_for_update'   => 500
        ];

        if ($this->options_obj->google->consent_mode->regions) {
            $data['regions'] = $this->options_obj->google->consent_mode->regions;
        }

        $ads_data_redaction = 'true'; // needs to be output as text
        $url_passthrough    = 'true'; // needs to be output as text

        return "gtag('consent', 'default', " . json_encode($data) . ");
                
                gtag('set', 'ads_data_redaction', " . $ads_data_redaction . ");
                
                gtag('set', 'url_passthrough', " . $url_passthrough . ");" . PHP_EOL;
    }
}

