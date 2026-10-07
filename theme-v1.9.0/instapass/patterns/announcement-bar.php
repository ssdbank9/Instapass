<?php
/**
 * Title: Instapass Announcement Bar
 * Slug: instapass/announcement-bar
 * Categories: instapass, header
 * Description: Thin bar for an offer or a notice, not shown by default. Insert it at the top of the Header template part and edit the text; remove the block to hide it again.
 * Viewport Width: 1400
 */
?>
<!-- wp:group {"tagName":"div","className":"ip-announce","style":{"spacing":{"padding":{"top":"var:preset|spacing|10","bottom":"var:preset|spacing|10"}}},"backgroundColor":"accent-dark","textColor":"on-accent","layout":{"type":"constrained"}} -->
<div class="wp-block-group ip-announce has-on-accent-color has-accent-dark-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--10);padding-bottom:var(--wp--preset--spacing--10)"><!-- wp:paragraph {"align":"center","fontSize":"small"} -->
<p class="has-text-align-center has-small-font-size"><?php esc_html_e( 'Every licence key or activation link is emailed right after payment and activated within 15 minutes.', 'instapass' ); ?> <a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>"><?php esc_html_e( 'Browse products', 'instapass' ); ?></a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
