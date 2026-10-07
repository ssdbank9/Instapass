<?php
/**
 * Title: Instapass Stats Strip
 * Slug: instapass/stats
 * Categories: instapass, featured
 * Description: Three social-proof stats. The delivered total is live (completed orders) and hides itself until the first sale.
 * Viewport Width: 1400
 */
?>
<!-- wp:group {"tagName":"section","className":"ip-stats","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}},"backgroundColor":"charcoal","textColor":"on-header","layout":{"type":"constrained"}} -->
<section class="wp-block-group ip-stats has-on-header-color has-charcoal-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)"><!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|30","left":"var:preset|spacing|30"}}}} -->
<div class="wp-block-columns alignwide"><!-- wp:column {"className":"ip-stat ip-stat--delivered"} -->
<div class="wp-block-column ip-stat ip-stat--delivered"><!-- wp:instapass/delivered-count /-->

<!-- wp:paragraph {"className":"ip-stat__label"} -->
<p class="ip-stat__label"><?php esc_html_e( 'licence keys and activation links delivered', 'instapass' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"className":"ip-stat"} -->
<div class="wp-block-column ip-stat"><!-- wp:paragraph {"className":"ip-stat__num"} -->
<p class="ip-stat__num"><?php esc_html_e( 'Under 15 min', 'instapass' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"ip-stat__label"} -->
<p class="ip-stat__label"><?php esc_html_e( 'from payment to completed activation', 'instapass' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"className":"ip-stat"} -->
<div class="wp-block-column ip-stat"><!-- wp:paragraph {"className":"ip-stat__num"} -->
<p class="ip-stat__num"><?php esc_html_e( '100% refund', 'instapass' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"ip-stat__label"} -->
<p class="ip-stat__label"><?php esc_html_e( 'if there is any issue, no questions asked', 'instapass' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></section>
<!-- /wp:group -->
