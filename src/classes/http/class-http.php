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
    protected $server_base_path;
    protected $mp_purchase_hit_key;
    protected $mp_full_refund_hit_key;
    protected $mp_partial_refund_hit_key;
    protected $hit_testing;

    public function __construct()
    {
        $this->options     = get_option(WGACT_DB_OPTIONS_NAME);
        $this->options_obj = json_decode(json_encode($this->options));

        $this->post_request_args = [
            'body'        => '',
            'timeout'     => 5,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => false,
            'headers'     => [],
            'cookies'     => [],
            'sslverify'   => false,
        ];

        $this->post_request_args = apply_filters('wooptpm_http_post_request_args', $this->post_request_args);
    }

    protected function full_tracking_enabled(): bool
    {
        $full_tracking_enabled = false;

        $full_tracking_enabled = apply_filters('wooptpm_full_tracking_enabled', $full_tracking_enabled);

        return $full_tracking_enabled;
    }
}