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

    public function __construct($options)
    {
//        $this->options     = get_option(WGACT_DB_OPTIONS_NAME);
        $this->options     = $options;
        $this->options_obj = json_decode(json_encode($this->options));

        $this->post_request_args = [
            'body'        => '',
            'timeout'     => 5,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => apply_filters('wooptpm_send_http_api_requests_blocking', false),
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

//    protected function send_hit($payload)
//    {
//        $request_url = $this->server_base_path;
//
////        error_log(print_r($payload, true));
//
//        $this->post_request_args['body'] = json_encode($payload);
//
//
////        error_log(print_r($this->post_request_args['body'], true));
//
////        error_log('request url: ' . $request_url);
//
//        // if we're sending the request non-blocking we won't receive a response back
//        if ($this->post_request_args['blocking'] === true) {
//            $response = wp_safe_remote_post($request_url, $this->post_request_args);
//            error_log('response code: ' . wp_remote_retrieve_response_code($response));
//            error_log(print_r($response, true));
//        } else {
//            wp_safe_remote_post($request_url, $this->post_request_args);
//        }
//    }

    protected function send_hit($request_url, $payload = null)
    {
        if ($payload) {
            $this->post_request_args['body'] = json_encode($payload);
        }

        // if we're sending the request non-blocking we won't receive a response back
        if ($this->post_request_args['blocking'] === true) {

            error_log(print_r($this->post_request_args, true));
            error_log('request url: ' . $request_url);
            error_log(print_r($payload, true));

            $response = wp_safe_remote_post($request_url, $this->post_request_args);

            error_log('hit was sent');
            error_log('response code: ' . wp_remote_retrieve_response_code($response));
            error_log(print_r($response, true));
        } else {
            wp_safe_remote_post($request_url, $this->post_request_args);
        }
    }
}