<?php

namespace WGACT\Classes\Admin;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Launch_Deal
{

    public function __construct()
    {
//	    $options = get_option('wooptpm_launch_deal');
//	    $options['dismissed'] = false;
//        $options['later'] = false;
//        $options['later_date'] = '';
//	    update_option('wooptpm_launch_deal',$options);

        // ask for a rating in a plugin notice

        // front-end script
        add_action('admin_enqueue_scripts', [$this, 'wooptpm_launch_deal_front_end_script']);
        // back-end processor
        add_action('wp_ajax_wooptpm_launch_deal_notice_handler', [$this, 'ajax_launch_deal_notice_handler']);

        // display the notification
        add_action('admin_notices', [$this, 'launch_deal_notification']);
    }

    public function launch_deal_notification()
    {
        $wooptpm_launch_deal = get_option('wooptpm_launch_deal');

        if ($wooptpm_launch_deal['eligible'] === true && $wooptpm_launch_deal['dismissed'] === false) {
            if ($wooptpm_launch_deal['later'] === true) {

                if (strtotime("now") >= $wooptpm_launch_deal['later_date']) {
//                error_log('lat');
                    $this->wooptpm_pro_launch_deal();
                }

            } else {
                $this->wooptpm_pro_launch_deal();
            }
        }
    }

    public function wooptpm_launch_deal_front_end_script()
    {
        wp_enqueue_script('wooptpm-launch-deal', plugin_dir_url(__DIR__) . '../js/admin/launch-deal.js', ['jquery'], WGACT_CURRENT_VERSION, true);
    }

    // server side php ajax handler for the admin rating notice
    public function ajax_launch_deal_notice_handler()
    {

        $set = $_POST['set'];

        $options = get_option('wooptpm_launch_deal');

        if ('launch-deal-maybe-later' === $set) {

//            error_log('later');
//            error_log('later_date: ' . strtotime("+12 day"));
            $options['later']      = true;
            $options['later_date'] = strtotime("+12 day");
            update_option('wooptpm_launch_deal', $options);

        } elseif ('launch-deal-dismiss' === $set) {

            $options['dismissed'] = true;
//            error_log('saving dismissed');
            update_option('wooptpm_launch_deal', $options);
        }

        wp_die(); // this is required to terminate immediately and return a proper response
    }

    public function wooptpm_pro_launch_deal()
    {
        ?>
        <div class="notice notice-success is-dismissible launch-deal-notification">
            <div style="color:#02830b">

                <div id="party" style="letter-spacing: 20px;margin-top: 8px; margin-bottom: 0px"></div>
                <script>
                    jQuery(window).load(function () {

                        let dots = document.getElementById('party');

                        let savedContent;

                        let repetitions = dots.parentNode.getBoundingClientRect().width / 33
                        for (let i = 0; i < repetitions; i++) {
                            savedContent = dots.innerHTML;
                            dots.innerHTML += '🎉';
                        }
                        dots.innerHTML        = savedContent;
                        dots.style.visibility = 'visible';
                    })
                </script>
                <br>
                <div>
                    <?php
                    _e('I am super happy and excited to announce our <a href="https://woopt.com/launch-deal-8a63336b/" target="_blank">special deal</a> for the launch of the <a href="https://woopt.com/" target="_blank">pro version</a> of the <b>woopt WooCommerce Pixel Manager Plugin</b> (formerly known as the <b>WooCommmerce Google Ads Conversion Tracking Plugin</b>).', 'woocommerce-google-adwords-conversion-tracking-tag');


                    ?>

                </div>
                <br>
                <?php _e('Because you are an existing user of this plugin you are eligible to receive a generous discount if you chose to participate in the launch deal. After all, you helped to shape the plugin into what it is today. Go ahead and have a look.', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
                <div>

                </div>
                <br>
                <div>- Aleksandar, CTO of woopt</div>
            </div>
            <div style="font-weight: bold;">

                <ul style="list-style-type: disc ;padding-left:20px">
                    <li>
                        <a id="launch-deal-take-me-to-the-deal" href="https://woopt.com/launch-deal-8a63336b/"
                           target="_blank">
                            <?php esc_html_e('Take me to the deal!', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
                        </a>
                    </li>
                    <li>
                        <a id="launch-deal-maybe-later" href="#">
                            <?php esc_html_e('Maybe later', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
                        </a>
                    </li>
                    <li>
                        <a id="launch-deal-dismiss" href="#">
                            <?php esc_html_e('Not interested. Close this message.', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
                        </a>
                    </li>
                </ul>
            </div>


        </div>
        <?php
    }
}