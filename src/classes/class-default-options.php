<?php

namespace WGACT\Classes;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Default_Options
{

    // get the default options
    public function get_default_options(): array
    {
        // default options settings
        return [
            'google'     => [
                'ads'          => [
                    'conversion_id'            => '',
                    'conversion_label'         => '',
                    'aw_merchant_id'           => '',
                    'product_identifier'       => 0,
                    'google_business_vertical' => 0,
                    'dynamic_remarketing'      => 0,
                    'phone_conversion_number'  => '',
                    'phone_conversion_label'   => '',
                    'enhanced_conversions'     => 0,
                ],
                'analytics'    => [
                    'universal'        => [
                        'property_id' => '',
                    ],
                    'ga4'              => [
                        'measurement_id' => '',
                        'api_secret'     => '',
                    ],
                    'eec'              => 0,
                    'link_attribution' => 0,
                ],
                'optimize'     => [
                    'container_id' => '',
                ],
                'gtag'         => [
                    'deactivation' => 0,
                ],
                'consent_mode' => [
                    'active'  => 0,
                    'regions' => [],
                ],
                'user_id'      => 0,
            ],
            'facebook'   => [
                'pixel_id'  => '',
                'microdata' => 0,
                'capi'      => [
                    'token'             => '',
                    'user_transparency' => [
                        'process_anonymous_hits'             => false,
                        'send_additional_client_identifiers' => false,
                    ]
                ]
            ],
            'bing'       => [
                'uet_tag_id' => ''
            ],
            'twitter'    => [
                'pixel_id' => ''
            ],
            'pinterest'  => [
                'pixel_id' => ''
            ],
            'snapchat' => [
                'pixel_id' => ''
            ],
            'tiktok' => [
                'pixel_id' => ''
            ],
            'hotjar'     => [
                'site_id' => ''
            ],
            'shop'       => [
                'order_total_logic'   => 0,
                'cookie_consent_mgmt' => [
                    'cookiebot' => [
                        'active' => 0
                    ],
                ],
                'order_deduplication' => 1
            ],
            'general'    => [
                'variations_output'          => 1,
                'maximum_compatibility_mode' => 0,
                'pro_version_demo'           => 0,
            ],
            'db_version' => WOOPTPM_DB_VERSION,
        ];
    }

    public function update_with_defaults($array_input, $array_default)
    {
        foreach ($array_default as $key => $value) {
            if (array_key_exists($key, $array_input)) {
                if (is_array($value)) {
                    $array_input[$key] = $this->update_with_defaults($array_input[$key], $value);
                }
            } else {
                $array_input[$key] = $value;
            }
        }

        return $array_input;
    }
}