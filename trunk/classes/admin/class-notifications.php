<?php

namespace WGACT\Classes\Admin;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Notifications
{
    public function wp_rocket_js_concatenation_error()
    {
        ?>
        <div class="notice notice-error wgact-wp-rocket-js-concatenation-error">
            <p style="color:red;font-weight: bold">
                <span>
                        <?php esc_html_e('We detected that the WP Rocket JavaScript concatenation function has been enabled. This function has been proven to be incompatible with the WooCommerce Google Ads Conversion Tracking plugin. 
                         Please turn off the WP Rocket JavaScript concatenation.', 'woocommerce-google-adwords-conversion-tracking-tag') ?>
                </span><br>
            </p>
            <p>
                <a href="https://docs.wolfundbaer.ch/wgact/?utm_source=plugin&utm_medium=notice-error&utm_campaign=wgact_wp_rocket_javascript_concatenation_error#/troubleshooting?id=wp-rocket-javascript-concatenation" target="_blank"
                   style="font-weight: bold;color:blue">
                    <?php esc_html_e('Learn more', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
                </a>
            </p>
            <p>
                <a href="<?php echo get_admin_url() . 'options-general.php?page=wprocket#file_optimization' ?>"
                   style="font-weight: bold;color:blue">
                    <?php esc_html_e('Open the WP Rocket JavaScript concatenation settings', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
                </a>
            </p>
            <p>
            <div id="wgact-wp-rocket-js-concatenation-disable" class="button button-primary">
                <?php esc_html_e('Click here to turn off the WP Rocket JavaScript concatenation', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
            </div>
            </p>

        </div>
        <?php
    }
}