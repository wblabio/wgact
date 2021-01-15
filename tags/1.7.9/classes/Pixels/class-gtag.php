<?php

namespace WGACT\Classes\Pixels;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Gtag {

	protected $conversion_id;

	public function __construct ($conversion_id) {
		$this->conversion_id = $conversion_id;
	}

	public function inject () {
		?>

		<!--noptimize-->
		<!-- Global site tag (gtag.js) - Google Ads: <?php echo esc_html( $this->conversion_id ) ?> -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=AW-<?php echo esc_html( $this->conversion_id ) ?>"></script>
		<script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }

            gtag('js', new Date());

            gtag('config', 'AW-<?php echo esc_html( $this->conversion_id ) ?>');
		</script>
		<!--/noptimize-->

		<?php

	}
}