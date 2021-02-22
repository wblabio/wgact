<?php

namespace WGACT\Classes\Pixels;

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

    protected function is_google_analytics_active(): bool
    {
        if ($this->options_obj->google->analytics->universal->property_id) {
            return true;
        } elseif ($this->options_obj->google->analytics->ga4->measurement_id) {
            return true;
        } else {
            return false;
        }
    }
}