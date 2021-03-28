<?php

namespace WGACT\Classes\Pixels\Google;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

trait Trait_Google
{
    protected function google_active(): bool
    {
        if ($this->options_obj->google->analytics->universal->property_id) {
            return true;
        } elseif ($this->options_obj->google->analytics->ga4->measurement_id) {
            return true;
        } elseif ($this->options_obj->google->ads->conversion_id) {
            return true;
        } else {
            return false;
        }
    }

    protected function is_google_ads_active(): bool
    {
        if($this->options_obj->google->ads->conversion_id){
            return true;
        } else {
            return false;
        }
    }

    private function is_dynamic_remarketing_active(): bool
    {
        if ($this->options_obj->google->ads->dynamic_remarketing && $this->options_obj->google->ads->conversion_id) {
            return true;
        } else {
            return false;
        }
    }

//    protected function is_google_ads_active(): bool
//    {
//        if ($this->options_obj->google->ads->conversion_id && $this->options_obj->google->ads->conversion_label) {
//            return true;
//        } else {
//            return false;
//        }
//    }

    protected function is_google_analytics_active(): bool
    {
        if ($this->is_google_analytics_ua_active() || $this->is_google_analytics_4_active()) {
            return true;
        } else {
            return false;
        }
    }

    protected function is_google_analytics_ua_active(): bool
    {
        if ($this->options_obj->google->analytics->universal->property_id) {
            return true;
        } else {
            return false;
        }
    }

    protected function is_google_analytics_4_active(): bool
    {
        if ($this->options_obj->google->analytics->ga4->measurement_id) {
            return true;
        } else {
            return false;
        }
    }
}