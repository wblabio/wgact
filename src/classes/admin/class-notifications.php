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
                <a href="https://docs.woopt.com/wgact/?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=woopt-pixel-manager-docs&utm_content=wp-rocket-javascript-concatenation-error#/troubleshooting?id=wp-rocket-javascript-concatenation"
                   target="_blank"
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
                <?php esc_html_e('Click here to simply turn off the WP Rocket JavaScript concatenation', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
            </div>
            </p>
            <p>
            <div id="wgact-dismiss-wp-rocket-js-concatenation-error" class="button" style="white-space:normal;">
                <?php esc_html_e('Click here to dismiss this warning forever.', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
                <br>
                <?php esc_html_e('And I swear that I triple checked that the visitor and conversion tracking is working just fine and that I won\'t ask for support as long as the WP Rocket JavaScript concatenation is turned on!', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>

            </div>
            </p>

        </div>
        <?php
    }

    public function litespeed_js_defer_error()
    {
        ?>
        <div class="notice notice-error wgact-litespeed-inline-js-dom-ready-error">
            <p style="color:red;font-weight: bold">
                <span>
                        <?php esc_html_e('We detected that the LiteSpeed Inline JavaScript After DOM Ready function has been enabled. This function has been proven to be incompatible with the WooCommerce Google Ads Conversion Tracking plugin. 
                         Please turn off the LiteSpeed Inline JavaScript After DOM Ready function.', 'woocommerce-google-adwords-conversion-tracking-tag') ?>
                </span><br>
            </p>
            <p>
                <a href="https://docs.woopt.com/wgact/?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=woopt-pixel-manager-docs&utm_content=litespeed-inline-js-dom-ready-error#/troubleshooting?id=litespeed-cache-inline-javascript-after-dom-ready"
                   target="_blank"
                   style="font-weight: bold;color:blue">
                    <?php esc_html_e('Learn more', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
                </a>
            </p>
            <p>
                <a href="<?php echo get_admin_url() . 'admin.php?page=litespeed-page_optm' ?>"
                   style="font-weight: bold;color:blue">
                    <?php esc_html_e('Open the LiteSpeed Inline JavaScript After DOM Ready settings', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
                </a>
            </p>
            <p>
            <div id="wgact-litespeed-inline-js-dom-ready-disable" class="button button-primary">
                <?php esc_html_e('Click here to simply turn off LiteSpeed Inline JavaScript After DOM Ready', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
            </div>
            </p>
            <p>
            <div id="wgact-dismiss-litespeed-inline-js-dom-ready-error" class="button" style="white-space:normal;">
                <?php esc_html_e('Click here to dismiss this warning forever.', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
                <br>
                <?php esc_html_e('And I swear that I triple checked that the visitor and conversion tracking is working just fine and that I won\'t ask for support as long as the LiteSpeed Inline JavaScript After DOM Ready is turned on!', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>

            </div>
            </p>

        </div>
        <?php
    }

    public function paypal_standard_active_warning()
    {
        ?>
        <div class="notice notice-error wooptpm-paypal-standard-error" >
            <p style="color:red;">
                <span>
                        <?php esc_html_e('The woopt WooCommerce Pixel Manager plugin detected that the PayPal standard payment gateway is active. The PayPal standard payment gateway is an off-site payment gateway which impairs conversion tracking significantly. Please switch to an on-site payment gateway as soon as possible in order to increase your conversion tracking accuracy.', 'woocommerce-google-adwords-conversion-tracking-tag') ?>
                </span><br>
            </p>
            <p>
                <a href="https://docs.woopt.com/wgact/?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=woopt-pixel-manager-docs&utm_content=paypal-standard-warning#/requirements?id=payment-gateways"
                   target="_blank">
                    <?php esc_html_e('Learn more', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
                </a>
            </p>
            <p>
                <a href="<?php echo get_admin_url() . 'admin.php?page=wc-settings&tab=checkout' ?>">
                    <?php esc_html_e('Open the WooCommerce payment methods settings', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
                </a>
            </p>

            <div style=" margin-bottom: 10px; display: flex; justify-content: space-between">

                <div id="wooptpm-paypal-standard-error-dismissal-button" class="button" style="white-space:normal;">
                    <?php esc_html_e('Click here to dismiss this warning forever', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
                </div>
                <div style="white-space:normal; bottom:0; right: 0; margin-bottom: 0px; margin-right: 5px;align-self: flex-end;">
                    <a href="https://docs.woopt.com/wgact/?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=woopt-pixel-manager-docs&utm_content=dismiss-button-info#/faq?id=the-dismiss-button-doesnt-work-why"
                       target="_blank">
                        <?php esc_html_e('If the dismiss button is not working, here\'s why >>', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
                    </a>
                </div>
            </div>

        </div>
        <?php
    }
}