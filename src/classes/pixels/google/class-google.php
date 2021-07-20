<?php

namespace WGACT\Classes\Pixels\Google;

use WGACT\Classes\Admin\Environment_Check;
use WGACT\Classes\Pixels\Pixel;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google extends Pixel
{
    use Trait_Google;

    protected $google_ads_conversion_identifiers;

    public function __construct($options)
    {
        parent::__construct($options);

        $this->google_business_vertical = $this->get_google_business_vertical($this->options['google']['ads']['google_business_vertical']);

        $this->google_ads_conversion_identifiers[$this->conversion_id] = $this->conversion_label;

        $this->google_ads_conversion_identifiers = apply_filters_deprecated('wgact_google_ads_conversion_identifiers', [$this->google_ads_conversion_identifiers], '1.10.2', 'wooptpm_google_ads_conversion_identifiers');
        $this->google_ads_conversion_identifiers = apply_filters('wooptpm_google_ads_conversion_identifiers', $this->google_ads_conversion_identifiers);

        $this->pixel_name = 'google_ads';
    }

    public function inject_everywhere()
    {
        $this->inject_data_layer_pixels();

        if ($this->options_obj->google->optimize->container_id) {
            echo "<script async src='https://www.googleoptimize.com/optimize.js?id=" . $this->options_obj->google->optimize->container_id . "'></script>" . PHP_EOL;
        }

        echo "
        <script async src='https://www.googletagmanager.com/gtag/js?id=" . $this->get_gtag_id() . "'></script>";

        echo $this->get_modified_script_opening_tag() . PHP_EOL;

        echo $this->get_google_init_js();

        foreach ($this->google_ads_conversion_identifiers as $conversion_id => $conversion_label): ?>
            <?php echo $this->options_obj->google->ads->conversion_id ? $this->gtag_config($conversion_id, 'ads') : PHP_EOL; ?>
        <?php endforeach; ?>

        <?php echo $this->options_obj->google->analytics->universal->property_id ? $this->gtag_config($this->options_obj->google->analytics->universal->property_id, 'ga_ua') . PHP_EOL : PHP_EOL; ?>
        <?php echo $this->options_obj->google->analytics->ga4->measurement_id ? $this->gtag_config($this->options_obj->google->analytics->ga4->measurement_id, 'ga_4') : PHP_EOL; ?>

        <?php

        if ($this->is_google_ads_active() && $this->options_obj->google->ads->phone_conversion_number && $this->options_obj->google->ads->phone_conversion_label) {
            $this->inject_phone_conversion_number_html__premium_only();
        }

        if ($this->options_obj->google->consent_mode->active && (new Environment_Check())->is_borlabs_cookie_active()) {
            echo $this->inject_borlabs_consent_mode_update();
        }
        //        $this->inject_closing_script_tag();
    }

    private function get_modified_script_opening_tag(): string
    {
        $cookiebot_snippet = $this->options_obj->shop->cookie_consent_mgmt->cookiebot->active ? ' data-cookieconsent="ignore"' : '';

        return "
        <script" . $cookiebot_snippet . ">";
    }

    private function inject_phone_conversion_number_html__premium_only()
    {
        echo "    
            gtag('config', 'AW-" . $this->options_obj->google->ads->conversion_id . "/" . $this->options_obj->google->ads->phone_conversion_label . "', {
                'phone_conversion_number': '" . $this->options_obj->google->ads->phone_conversion_number . "'
            });" . PHP_EOL;
    }


    protected function get_google_init_js(): string
    {
        return "
            window.dataLayer = window.dataLayer || [];

            window.gtag = function gtag() {
                dataLayer.push(arguments);
            }
    " . $this->consent_mode_gtag_html()
            . $this->linker_html() . "
            gtag('js', new Date());";
    }

    // https://developers.google.com/gtagjs/devguide/linker
    private function linker_html(): string
    {
        $linker_domains = apply_filters('wooptpm_google_cross_domain_linker_settings', null);

        if ($linker_domains) {

            return "\t\t" . "gtag('set', 'linker', " . json_encode($linker_domains) . ");";
        } else {

            return '';
        }
    }

    private function consent_mode_gtag_html(): string
    {
        if ($this->options_obj->google->consent_mode->active) {

            $data = [
                'ad_storage'        => 'denied',
                'analytics_storage' => 'denied',
                'wait_for_update'   => 500,
            ];
            if ($this->options_obj->google->consent_mode->regions) {
                $data['regions'] = $this->options_obj->google->consent_mode->regions;
            }
            $ads_data_redaction = 'true';
            // needs to be output as text
            $url_passthrough = 'true';
            // needs to be output as text
            return "
                gtag('consent', 'default', " . json_encode($data) . ");
                gtag('set', 'ads_data_redaction', " . $ads_data_redaction . ");
                gtag('set', 'url_passthrough', " . $url_passthrough . ");" . PHP_EOL;
        } else {
            return '';
        }
    }

    protected function get_google_business_vertical($id): string
    {
        $verticals = [
            0 => 'retail',
            1 => 'education',
            2 => 'flights',
            3 => 'hotel_rental',
            4 => 'jobs',
            5 => 'local',
            6 => 'real_estate',
            7 => 'travel',
            8 => 'custom'
        ];

        return $verticals[$id];
    }

    protected function get_formatted_order_items($order, $channel = '')
    {
        $order_items       = $this->wooptpm_get_order_items($order);
        $order_items_array = [];

        $list_position = 1;

        foreach ((array)$order_items as $order_item) {

            $product_id = $this->get_variation_or_product_id($order_item->get_data(), $this->options_obj->general->variations_output);
            $product    = wc_get_product($product_id);

            if (!is_object($product)) {

                $this->log_problematic_product_id($product_id);
                continue;
            }

            $item_details_array = [];

            $dyn_r_ids = $this->get_dyn_r_ids($product);

            if ($channel === 'ads') {

                $item_details_array['id'] = (string)$dyn_r_ids[$this->get_dyn_r_id_type()];
            } else {

                $item_details_array['id'] = (string)$dyn_r_ids[$this->get_ga_id_type()];
            }

            $item_details_array['quantity'] = (int)$order_item['quantity'];
            $item_details_array['price']    = (float)$product->get_price();
            if ($this->is_google_ads_active()) {
                $item_details_array['google_business_vertical'] = (string)$this->google_business_vertical;
            }

            if ($this->is_google_analytics_active() && $channel <> 'ads') {
                $item_details_array['name'] = (string)$product->get_name();
//                $item_details_array['list_name'] = '';
                $item_details_array['brand']    = (string)$this->get_brand_name($product_id);
                $item_details_array['category'] = (array)$this->get_product_category($product_id);
//                $item_details_array['variant'] = '';
                $item_details_array['list_position'] = (int)$list_position++;
            }

            array_push($order_items_array, $item_details_array);
        }

        // apply filter to $order_items_array
        $order_items_array = apply_filters_deprecated('wgact_filter', [$order_items_array], '1.10.2', '', 'This filter has been deprecated without replacement.');

        return $order_items_array;
    }

    protected function get_event_purchase_json($order, $order_total, $order_currency, $is_new_customer, $channel)
    {
        $gtag_data = [
            'send_to'        => [],
            'transaction_id' => (string)$order->get_order_number(),
            'currency'       => (string)$order_currency,
            'discount'       => (float)$order->get_total_discount(),
            'items'          => (array)$this->get_formatted_order_items($order, $channel),
        ];

        if ('ads' === $channel) {
            $gtag_data['send_to']          = $this->get_google_ads_conversion_ids(true);
            $gtag_data['value']            = (float)$order_total;
            $gtag_data['aw_merchant_id']   = (int)$this->aw_merchant_id;
            $gtag_data['aw_feed_country']  = (string)$this->get_visitor_country();
            $gtag_data['aw_feed_language'] = (string)$this->get_gmc_language();
            $gtag_data['new_customer']     = (string)$is_new_customer;
        } else if ('ga_ua' === $channel) {
            if ($this->options_obj->google->analytics->universal->property_id) array_push($gtag_data['send_to'], $this->options_obj->google->analytics->universal->property_id);
//            if ($this->options_obj->google->analytics->ga4->measurement_id) array_push($gtag_data['send_to'], $this->options_obj->google->analytics->ga4->measurement_id);
            $gtag_data['affiliation'] = (string)get_bloginfo('name');
            $gtag_data['tax']         = (float)$order->get_total_tax();
            $gtag_data['shipping']    = (float)$order->get_total_shipping();
            $gtag_data['value']       = (float)$order->get_total();
        } else if ('ga_4' === $channel) {
//            if ($this->options_obj->google->analytics->universal->property_id) array_push($gtag_data['send_to'], $this->options_obj->google->analytics->universal->property_id);
            if ($this->options_obj->google->analytics->ga4->measurement_id) array_push($gtag_data['send_to'], $this->options_obj->google->analytics->ga4->measurement_id);
            $gtag_data['affiliation'] = (string)get_bloginfo('name');
            $gtag_data['tax']         = (float)$order->get_total_tax();
            $gtag_data['shipping']    = (float)$order->get_total_shipping();
            $gtag_data['value']       = (float)$order->get_total();
        }

        return json_encode($gtag_data);
    }

    protected function get_gmc_language(): string
    {
        return strtoupper(substr(get_locale(), 0, 2));
    }

    protected function get_google_ads_conversion_ids($purchase = false): array
    {
        $formatted_conversion_ids = [];

        if ($purchase) {
            foreach ($this->google_ads_conversion_identifiers as $conversion_id => $conversion_label) {
                array_push($formatted_conversion_ids, 'AW-' . $conversion_id . '/' . $conversion_label);
            }
        } else {
            foreach ($this->google_ads_conversion_identifiers as $conversion_id => $conversion_label) {
                array_push($formatted_conversion_ids, 'AW-' . $conversion_id);
            }
        }
        return $formatted_conversion_ids;
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

    private function inject_data_layer_pixels()
    {

        $data = [
            'google' => [
                'ads'       => [
                    'dynamic_remarketing'      => [
                        'status'  => $this->options_obj->google->ads->dynamic_remarketing ? true : false,
                        'id_type' => $this->get_dyn_r_id_type(),
                    ],
                    'conversionIds'            => $this->get_google_ads_conversion_ids(),
                    'google_business_vertical' => $this->google_business_vertical,
                ],
                'analytics' => [
                    'universal' => [
                        'property_id' => $this->options_obj->google->analytics->universal->property_id
                    ],
                    'ga4'       => [
                        'measurement_id' => $this->options_obj->google->analytics->ga4->measurement_id,
                    ],
                    'id_type'   => $this->get_ga_id_type(),
                    'eec'       => $this->options_obj->google->analytics->eec ? true : false,
                ]
            ],
        ];

        ?>

        <script>
            wooptpmDataLayer.pixels = <?php echo json_encode($data) ?>;
        </script>
        <?php
    }


    protected function gtag_config($id, $channel = ''): string
    {
        if ('ads' === $channel) {

            if ($this->options_obj->google->ads->enhanced_conversions) {
                return PHP_EOL . "\t\t\t" . "gtag('config', 'AW-" . $id . "', {'allow_enhanced_conversions':true});" . PHP_EOL;
            } else {
                return PHP_EOL . "\t\t\t" . "gtag('config', 'AW-" . $id . "');" . PHP_EOL;
            }

        } elseif ('ga_ua' === $channel) {

            $ga_ua_parameters = [
                'anonymize_ip'     => 'true', // must be a string for correct output
                'link_attribution' => $this->options_obj->google->analytics->link_attribution ? 'true' : 'false', // must be a string for correct output
            ];

            if ($this->options_obj->google->user_id && is_user_logged_in()) {
                $ga_ua_parameters['user_id'] = get_current_user_id();
            }

            $ga_ua_parameters = apply_filters_deprecated('woopt_pm_analytics_parameters', [$ga_ua_parameters, $id], '1.10.10', 'wooptpm_ga_ua_parameters');
            $ga_ua_parameters = apply_filters('wooptpm_ga_ua_parameters', $ga_ua_parameters, $id);

            return "\t" . "gtag('config', '" . $id . "', " . json_encode($ga_ua_parameters) . ");";
        } elseif ('ga_4' === $channel) {

            $ga_4_parameters = [];

            if ($this->options_obj->google->user_id && is_user_logged_in()) {
                $ga_4_parameters = [
                    'user_id' => get_current_user_id(),
                ];
            }

            $ga_4_parameters = apply_filters('wooptpm_ga_4_parameters', $ga_4_parameters, $id);

            if (empty($ga_4_parameters)) {
                return "\t" . "gtag('config', '" . $id . "');";
            } else {
                return "\t" . "gtag('config', '" . $id . "', " . json_encode($ga_4_parameters) . ");";
            }
        }
    }

    private function inject_borlabs_consent_mode_update(): string
    {
        return "
                (function updateGoogleConsentMode() {
                    if (typeof BorlabsCookie == 'undefined' || typeof gtag == 'undefined') {
                        window.setTimeout(updateGoogleConsentMode, 50);
                    } else {
                        if (window.BorlabsCookie.checkCookieGroupConsent('statistics')) {
//                            console.log('update analytics_storage to granted');

                            gtag('consent', 'update', {
                                'analytics_storage': 'granted'
                        });
                    }
        
                    if (window.BorlabsCookie.checkCookieGroupConsent('marketing')) {
//                        console.log('update ad_storage to granted');
                        gtag('consent', 'update', {
                            'ad_storage': 'granted'
                            });
                        }
                    }
                })();" . PHP_EOL;
    }
}