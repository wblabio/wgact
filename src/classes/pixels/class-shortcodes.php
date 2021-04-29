<?php

namespace WGACT\Classes\Pixels;

use WGACT\Classes\Pixels\Google\Trait_Google;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Shortcodes extends Pixel
{
    use Trait_Google;

    public function __construct($options)
    {
        parent::__construct($options);

        add_shortcode('conversion-pixel', [$this, 'woopt_conversion_pixel']);
    }

    public function woopt_conversion_pixel($attributes)
    {
        $shortcode_attributes = shortcode_atts([
            'pixel'                 => 'all',
            'gads-conversion-id'    => $this->options_obj->google->ads->conversion_id,
            'gads-conversion-label' => '',
            'fbc-event'             => 'Lead',
            'twc-event'             => 'CompleteRegistration',
            'pinc-event'            => 'lead',
            'pinc-lead-type'        => '',
            'ms-ads-event'          => 'submit',
            'ms-ads-event-category' => '',
            'ms-ads-event-label'    => 'lead',
            'ms-ads-event-value'    => 0,
        ], $attributes);

        if ($shortcode_attributes['pixel'] == 'google-ads') {
            if ($this->is_google_ads_active()) $this->google_ads_conversion_html($shortcode_attributes);
        } elseif ($shortcode_attributes['pixel'] == 'facebook') {
            if ($this->options_obj->facebook->pixel_id) $this->facebook_conversion_html($shortcode_attributes);
        } elseif ($shortcode_attributes['pixel'] == 'twitter') {
            if ($this->options_obj->twitter->pixel_id) $this->twitter_conversion_html($shortcode_attributes);
        } elseif ($shortcode_attributes['pixel'] == 'pinterest') {
            if ($this->options_obj->pinterest->pixel_id) $this->pinterest_conversion_html($shortcode_attributes);
        } elseif ($shortcode_attributes['pixel'] == 'ms-ads') {
            if ($this->options_obj->bing->uet_tag_id) $this->microsoft_ads_conversion_html($shortcode_attributes);
        } elseif ($shortcode_attributes['pixel'] == 'all') {
            if ($this->is_google_ads_active()) $this->google_ads_conversion_html($shortcode_attributes);
            if ($this->options_obj->facebook->pixel_id) $this->facebook_conversion_html($shortcode_attributes);
            if ($this->options_obj->twitter->pixel_id) $this->twitter_conversion_html($shortcode_attributes);
            if ($this->options_obj->pinterest->pixel_id) $this->pinterest_conversion_html($shortcode_attributes);
            if ($this->options_obj->bing->uet_tag_id) $this->microsoft_ads_conversion_html($shortcode_attributes);
        }
    }

    private function google_ads_conversion_html($shortcode_attributes)
    {
        ?>

        <script>
            gtag('event', 'conversion', {'send_to': 'AW-<?php echo $shortcode_attributes['gads-conversion-id'] ?>/<?php echo $shortcode_attributes['gads-conversion-label'] ?>'});
        </script>
        <?php
    }

    // https://developers.facebook.com/docs/analytics/send_data/events/
    private function facebook_conversion_html($shortcode_attributes)
    {
        ?>

        <script>
            fbq('track', '<?php echo $shortcode_attributes['fbc-event'] ?>');
        </script>
        <?php
    }

    // https://business.twitter.com/en/help/campaign-measurement-and-analytics/conversion-tracking-for-websites.html
    private function twitter_conversion_html($shortcode_attributes)
    {
        ?>

        <script>
            twq('track', '<?php echo $shortcode_attributes['twc-event'] ?>');
        </script>
        <?php
    }

    // https://help.pinterest.com/en/business/article/track-conversions-with-pinterest-tag
    // https://help.pinterest.com/en/business/article/add-event-codes
    private function pinterest_conversion_html($shortcode_attributes)
    {
        if ($shortcode_attributes['pinc-lead-type'] == '') {
            ?>

            <script>
                pintrk('track', '<?php echo $shortcode_attributes['pinc-event'] ?>');
            </script>
            <?php
        } else {
            ?>

            <script>
                pintrk('track', '<?php echo $shortcode_attributes['pinc-event'] ?>', {
                    lead_type: '<?php echo $shortcode_attributes['pinc-lead-type'] ?>'
                });
            </script>
            <?php
        }
    }

    // https://bingadsuet.azurewebsites.net/UETDirectOnSite_ReportCustomEvents.html
    private function microsoft_ads_conversion_html($shortcode_attributes)
    {
        ?>

        <script>
            window.uetq = window.uetq || [];
            window.uetq.push('event', '<?php echo $shortcode_attributes['ms-ads-event'] ?>', {
                'event_category': '<?php echo $shortcode_attributes['ms-ads-event-category'] ?>',
                'event_label'   : '<?php echo $shortcode_attributes['ms-ads-event-label'] ?>',
                'event_value'   : '<?php echo $shortcode_attributes['ms-ads-event-value'] ?>'
            });
        </script>
        <?php
    }
}