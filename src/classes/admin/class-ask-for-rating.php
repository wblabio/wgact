<?php

namespace WGACT\Classes\Admin;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Ask_For_Rating
{

    private $option_name = WOOPTPM_DB_RATINGS;

    public function __construct()
    {

//	    $options = get_option($this->option_name);
//	    $options['conversions_count'] = 8;
//	    $options['rating_threshold'] = 10;
//	    unset($options['conversion_count']);
//	    $options['rating_done'] = false;
//	    update_option($this->option_name,$options);

        // ask for a rating in a plugin notice
        add_action('admin_enqueue_scripts', [$this, 'wgact_rating_script']);
        add_action('wp_ajax_wgact_dismissed_notice_handler', [$this, 'ajax_rating_notice_handler']);
        add_action('admin_notices', [$this, 'ask_for_rating_notices_if_not_asked_before']);
    }

    public function wgact_rating_script()
    {

//        wp_enqueue_script('wooptpm-ask-for-rating', plugin_dir_url(__DIR__) . '../js/admin/ask-for-rating.js', ['jquery'], WGACT_CURRENT_VERSION, true);
        wp_enqueue_script('wooptpm-ask-for-rating', WOOPTPM_PLUGIN_DIR_PATH . 'js/admin/ask-for-rating.js', ['jquery'], WGACT_CURRENT_VERSION, true);

//	    wp_localize_script(
//		    'ask-for-rating', // Handle
//		    'ask-for-rating_ajax_object', // Object name
//		    [
//			    'ajaxurl'     => admin_url( 'admin-ajax.php' ),
//			    'ajaxnonce'   => wp_create_nonce( 'ask-for-rating_security_nonce' )
//		    ]
//	    );
    }

    // server side php ajax handler for the admin rating notice
    public function ajax_rating_notice_handler()
    {

        $set = $_POST['set'];

        $options = get_option($this->option_name);

        if ('rating_done' === $set) {

//			error_log('saving rating done');
            $options['rating_done'] = true;
            update_option($this->option_name, $options);

        } elseif ('later' === $set) {

//			error_log('saving later');
            $options['rating_threshold'] = $this->get_next_threshold($options['conversions_count']);
            update_option($this->option_name, $options);
        }

        wp_die(); // this is required to terminate immediately and return a proper response
    }

    public function ask_for_rating_notices_if_not_asked_before()
    {
        if (current_user_can('administrator')) {

            $wgact_ratings = get_option($this->option_name);

            if (isset($wgact_ratings['conversions_count'])) {

                $conversions_count = $wgact_ratings['conversions_count'];
//		    error_log('conversion count: ' . $wgact_ratings['conversions_count'] );

                // in rare cases this option has not been set
                // in those cases we set it to avoid further errors
                if (!isset($wgact_ratings['rating_done'])) {
                    $wgact_ratings['rating_done'] = false;
                    update_option($this->option_name, $wgact_ratings);
                }

                // in rare cases this option has not been set
                // in those cases we set it to avoid further errors
                if (!isset($wgact_ratings['rating_threshold'])) {
                    $wgact_ratings['rating_threshold'] = 10;
                    update_option($this->option_name, $wgact_ratings);
                }

                if ((false === $wgact_ratings['rating_done'] && $conversions_count > $wgact_ratings['rating_threshold']) || (defined('WGACT_ALWAYS_AKS_FOR_RATING') && true === WGACT_ALWAYS_AKS_FOR_RATING)) {

                    $this->ask_for_rating_notices($conversions_count);
                }
            } else {

                // set default settings for wgact_ratings
                update_option($this->option_name, $this->get_default_settings());
            }
        }
    }

    private function get_next_threshold($conversions_count)
    {

        return $conversions_count * 10;
    }


    private function get_default_settings(): array
    {

        return [
            'conversions_count' => 1,
            'rating_threshold'  => 10,
            'rating_done'       => false,
        ];
    }

    // show an admin notice to ask for a plugin rating
    public function ask_for_rating_notices($conversions_count)
    {

        ?>
        <div class="notice notice-success wgact-rating-success-notice" style="display: none">
            <div style="color:#02830b;font-weight: bold">

                <span>
                        <?php
                        printf(
                        /* translators: %d: the amount of purchase conversions that have been measured */
                            esc_html__('Hey, I noticed that you tracked more than %d purchase conversions with the Google Ads Conversion Tracking plugin - that\'s awesome! Could you please do me a BIG favour and give it a 5-star rating on WordPress? Just to help us spread the word and boost our motivation.', 'woocommerce-google-adwords-conversion-tracking-tag'),
                            $conversions_count
                        );
                        ?>

                </span>
                <br>
                <span>- Aleksandar</span>
            </div>
            <div style="font-weight: bold;">

                <ul style="list-style-type: disc ;padding-left:20px">
                    <li>
                        <a id="wooptpm-rate-it" href="#">
                            <?php esc_html_e('Ok, you deserve it', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
                        </a>
                    </li>
                    <li>
                        <a id="wooptpm-maybe-later" href="#">
                            <?php esc_html_e('Nope, maybe later', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
                        </a>
                    </li>
                    <li>

                        <div style=" margin-bottom: 10px; display: flex; justify-content: space-between">

                            <div id="wooptpm-paypal-standard-error-dismissal-button" style="white-space:normal;">
                                <a id="wooptpm-already-did" href="#">
                                    <?php esc_html_e('I already did', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
                                </a>
                            </div>
                            <div style="white-space:normal; bottom:0; right: 0; margin-bottom: 0px; margin-right: 5px;align-self: flex-end;">
                                <a href="https://docs.woopt.com/wgact/?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=woopt-pixel-manager-docs&utm_content=dismiss-button-info#/faq?id=the-dismiss-button-doesnt-work-why"
                                   target="_blank">
                                    <?php esc_html_e('If the dismiss button is not working, here\'s why >>', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
                                </a>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>




        </div>
        <?php

    }

}