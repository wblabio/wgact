<?php

namespace WGACT\Classes\Pixels;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google_Optimize extends Google_Pixel
{
    public function inject_google_optimize_anti_flicker_snippet()
    {
        ?>

        <script>(function (a, s, y, n, c, h, i, d, e) {
                s.className += ' ' + y;
                h.start                  = 1 * new Date;
                h.end                    = i = function () {
                    s.className = s.className.replace(RegExp(' ?' + y), '')
                };
                (a[n] = a[n] || []).hide = h;
                setTimeout(function () {
                    i();
                    h.end = null
                }, c);
                h.timeout = c;
            })(window, document.documentElement, 'async-hide', 'dataLayer', 4000,
                {'<?php echo $this->options_obj->google->optimize->container_id ?>': true});</script>
        <?php
    }
}