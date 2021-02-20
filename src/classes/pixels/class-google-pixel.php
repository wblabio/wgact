<?php

namespace WGACT\Classes\Pixels;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google_Pixel extends Pixel
{
    public function __construct($options, $options_obj)
    {
        parent::__construct($options, $options_obj);
    }
}