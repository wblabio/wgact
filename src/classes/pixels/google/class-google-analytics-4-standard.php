<?php


namespace WGACT\Classes\Pixels\Google;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google_Analytics_4_Standard extends Google_Analytics_4
{
    public function __construct($options)
    {
        parent::__construct($options);
    }
}