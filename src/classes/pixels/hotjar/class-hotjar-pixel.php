<?php

namespace WGACT\Classes\Pixels\Hotjar;

use WGACT\Classes\Pixels\Pixel;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Hotjar_Pixel extends Pixel
{
    public function __construct($options)
    {
        parent::__construct($options);
    }

    public function inject_everywhere()
    {
        // @formatter:off
        ?>

        <script>
            (function(h,o,t,j,a,r){
                h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
                h._hjSettings={hjid:<?php echo $this->options_obj->hotjar->site_id ?>,hjsv:6};
                a=o.getElementsByTagName('head')[0];
                r=o.createElement('script');r.async=1;
                r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
                a.appendChild(r);
            })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
        </script>
        <?php
        // @formatter:on
    }
}