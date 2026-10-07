<?php
/**
 * Title: Aurora Studio Header
 * Slug: instapass/studio-header
 * Categories: instapass, header
 * Inserter: no
 */
?>
<!-- wp:html -->
<div class="ip-studio-announcement"><?php esc_html_e( 'Digital access. Delivered to your inbox.', 'instapass' ); ?> <span aria-hidden="true">✦</span> <a href="<?php echo esc_url( home_url( '/refund-policy/' ) ); ?>"><?php esc_html_e( 'Our refund promise', 'instapass' ); ?> <span aria-hidden="true">↗</span></a></div>
<!-- /wp:html -->
<!-- wp:group {"className":"ip-studio-header","layout":{"type":"constrained"}} -->
<div class="wp-block-group ip-studio-header">
<!-- wp:group {"align":"wide","className":"ip-studio-header__row","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group alignwide ip-studio-header__row">
<!-- wp:html -->
<a class="ip-studio-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Instapass home', 'instapass' ); ?>"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/logo/instapass-mark.svg' ); ?>" width="42" height="42" alt=""><span>instapass<span class="ip-studio-brand-dot">.</span></span></a>
<!-- /wp:html -->
<!-- wp:navigation {"overlayMenu":"mobile","className":"ip-studio-nav","metadata":{"ignoredHookedBlocks":["woocommerce/customer-account","woocommerce/mini-cart"]},"layout":{"type":"flex","justifyContent":"center"}} -->
<!-- wp:navigation-link {"label":"Shop","url":"<?php echo esc_url( instapass_shop_url() ); ?>","kind":"custom"} /-->
<!-- wp:navigation-link {"label":"How it works","url":"<?php echo esc_url( home_url( '/#how-it-works' ) ); ?>","kind":"custom"} /-->
<!-- wp:navigation-link {"label":"FAQ","url":"<?php echo esc_url( home_url( '/faq/' ) ); ?>","kind":"custom"} /-->
<!-- wp:navigation-link {"label":"Contact","url":"<?php echo esc_url( home_url( '/contact/' ) ); ?>","kind":"custom"} /-->
<!-- /wp:navigation -->
<!-- wp:group {"className":"ip-studio-header__actions","layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group ip-studio-header__actions">
<!-- wp:search {"label":"Search products","showLabel":false,"placeholder":"Find your next upgrade…","query":{"post_type":"product"},"buttonText":"Search","buttonUseIcon":true,"className":"ip-studio-search"} /-->
<!-- wp:woocommerce/mini-cart {"hasHiddenPrice":true} /-->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
