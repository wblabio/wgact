<?php


namespace WGACT\Classes\Pixels\Google;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google_Analytics_UA_Standard extends Google_Analytics_UA
{
    public function __construct($options)
    {
        parent::__construct($options);
    }
}