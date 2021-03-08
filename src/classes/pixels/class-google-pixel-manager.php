<?php


namespace WGACT\Classes\Pixels;

use WGACT\Classes\Admin\Environment_Check;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google_Pixel_Manager extends Google_Pixel
{
    use Trait_Google;

    public function __construct($options, $options_obj)
    {
        parent::__construct($options, $options_obj);
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

            <?php echo $this->options_obj->google->analytics->universal->property_id ? $this->gtag_config($this->options_obj->google->analytics->universal->property_id, 'analytics') . PHP_EOL : PHP_EOL; ?>
            <?php echo $this->options_obj->google->analytics->ga4->measurement_id ? $this->gtag_config($this->options_obj->google->analytics->ga4->measurement_id, 'analytics') : PHP_EOL; ?>

        </script>
        <?php

        if ($this->options_obj->google->consent_mode->active && (new Environment_Check())->is_borlabs_cookie_active()) {
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

        if ($this->is_google_ads_active() && $this->options_obj->google->ads->phone_conversion_number) {
               $this->inject_phone_conversion_number_html__premium_only();
        }
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

    public function inject_product_category()
    {
        (new Google_Ads($this->options, $this->options_obj))->inject_product_category();

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
        (new Google_Ads($this->options, $this->options_obj))->inject_search();

        if (wga_fs()->is__premium_only()) {
            if ($this->options_obj->google->analytics->eec) (new Google_Enhanced_Ecommerce($this->options, $this->options_obj))->inject_product_list_object('search');
        }
    }

    public function inject_product($product_id, $product, $product_attributes)
    {
        (new Google_Ads($this->options, $this->options_obj))->inject_product($product_id, $product, $product_attributes);

        if (wga_fs()->is__premium_only()) {
            if ($this->options_obj->google->analytics->eec) (new Google_Enhanced_Ecommerce($this->options, $this->options_obj))->inject_product($product_id, $product, $product_attributes);
        }
    }

    public function inject_cart($cart, $cart_total)
    {
        (new Google_Ads($this->options, $this->options_obj))->inject_cart($cart, $cart_total);

        if (wga_fs()->is__premium_only()) {
            if ($this->options_obj->google->analytics->eec) (new Google_Enhanced_Ecommerce($this->options, $this->options_obj))->inject_cart($cart, $cart_total);
        }
    }

    public function inject_order_received_page($order, $order_total, $order_item_ids, $is_new_customer)
    {
        if ($this->options_obj->google->ads->conversion_id) (new Google_Ads($this->options, $this->options_obj))->inject_order_received_page($order, $order_total, $order_item_ids, $is_new_customer);
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
        $analytics_parameters = [
            'anonymize_ip'     => 'true', // must be a string for correct output
            'link_attribution' => $this->options_obj->google->analytics->link_attribution ? 'true' : 'false', // must be a string for correct output
        ];

        if ('ads' === $channel) {
            return "gtag('config', 'AW-" . $id . "');" . PHP_EOL;
        } elseif ('analytics') {
            return "gtag('config', '" . $id . "', " . json_encode($analytics_parameters) . ");";
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

