<?php
/**
 * Title: Instapass Trust Bar
 * Slug: instapass/trust-bar
 * Categories: instapass, featured
 * Description: Compact four-point trust bar that sits directly under the hero.
 * Viewport Width: 1400
 */
?>
<!-- wp:group {"tagName":"div","className":"ip-trust-bar","style":{"spacing":{"padding":{"top":"0","bottom":"0"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group ip-trust-bar" style="padding-top:0;padding-bottom:0"><!-- wp:columns {"isStackedOnMobile":true,"align":"wide","style":{"spacing":{"blockGap":{"top":"0","left":"0"}}}} -->
<div class="wp-block-columns alignwide"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:paragraph -->
<p><strong><?php esc_html_e( 'Activated within 15 minutes', 'instapass' ); ?></strong><span class="ip-trust-bar__label"><?php esc_html_e( 'licence key or activation link emailed right after payment', 'instapass' ); ?></span></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:paragraph -->
<p><strong><?php esc_html_e( 'No account needed', 'instapass' ); ?></strong><span class="ip-trust-bar__label"><?php esc_html_e( '1-click checkout, nothing to register', 'instapass' ); ?></span></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:paragraph -->
<p><strong><?php esc_html_e( 'Available at checkout', 'instapass' ); ?></strong><span class="ip-trust-bar__label"><?php esc_html_e( 'Payment methods shown before you pay', 'instapass' ); ?></span></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:paragraph -->
<p><strong><?php esc_html_e( 'Full refund, no questions asked', 'instapass' ); ?></strong><span class="ip-trust-bar__label"><?php esc_html_e( 'if there is any issue with your order', 'instapass' ); ?></span></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
