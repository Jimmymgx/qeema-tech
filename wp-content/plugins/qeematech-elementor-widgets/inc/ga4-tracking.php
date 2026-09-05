<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The live site's actual GA4 tag (G-4GN9H7Y4K4, same property reused per
 * launch decision) is loaded via gtag.js directly, not a full GTM container.
 * The GTM4WP plugin installed here (1.22.3) only supports GTM-xxxx container
 * IDs, not bare G- measurement IDs, and Google Site Kit needs an interactive
 * Google-account OAuth connection that can't happen pre-launch (Search
 * Console domain verification requires the real domain to be live). This
 * hooks the exact same gtag.js snippet directly so tracking matches live
 * today; Site Kit can be connected later for its own reporting dashboard
 * without needing to own tag injection.
 */
function qeema_ga4_tracking_tag() {
	if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		return;
	}
	?>
<script async src="https://www.googletagmanager.com/gtag/js?id=G-4GN9H7Y4K4"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', 'G-4GN9H7Y4K4');
</script>
	<?php
}
add_action( 'wp_head', 'qeema_ga4_tracking_tag', 1 );
