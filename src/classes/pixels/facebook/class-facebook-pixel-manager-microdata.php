<?php

namespace WGACT\Classes\Pixels\Facebook;

use WGACT\Classes\Pixels\Pixel_Manager_Base;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Facebook_Pixel_Manager_Microdata extends Pixel_Manager_Base
{
    protected $facebook_microdata_pixel;

    public function __construct()
    {
        parent::__construct();
        $this->facebook_microdata_pixel = new Facebook_Microdata();
    }

    public function inject_product($product, $product_attributes)
    {
        $this->facebook_microdata_pixel->inject_product($product, $product_attributes);
    }

    protected function inject_opening_script_tag()
    {
        // remove default script output
    }

    protected function inject_closing_script_tag()
    {
        // remove default script output
    }
}