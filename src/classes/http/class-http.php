<?php

namespace WGACT\Classes\Http;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Http
{
    protected $options;
    protected $options_obj;
    protected $post_request_args;

    public function __construct()
    {
        $this->options     = get_option(WGACT_DB_OPTIONS_NAME);
        $this->options_obj = json_decode(json_encode($this->options));

        $this->post_request_args = [
            'body'        => '',
            'timeout'     => '5',
            'redirection' => '5',
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => [],
            'cookies'     => [],
        ];
    }

    protected function full_tracking_enabled(): bool
    {
        $full_tracking_enabled = false;

        $full_tracking_enabled = apply_filters('wooptpm_full_tracking_enabled', $full_tracking_enabled);

        return $full_tracking_enabled;
    }
}