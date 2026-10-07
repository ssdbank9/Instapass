<?php
/**
 * Title: Aurora Studio Footer
 * Slug: instapass/studio-footer
 * Categories: instapass, footer
 * Inserter: no
 */
?>
<!-- wp:group {"className":"ip-studio-footer","layout":{"type":"constrained"}} -->
<div class="wp-block-group ip-studio-footer">
<!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide">
<!-- wp:column {"width":"40%"} -->
<div class="wp-block-column" style="flex-basis:40%">
<!-- wp:html -->
<a class="ip-studio-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/logo/instapass-mark.svg' ); ?>" width="42" height="42" alt=""><span>instapass<span class="ip-studio-brand-dot">.</span></span></a>
<!-- /wp:html -->
<!-- wp:paragraph -->
<p><?php esc_html_e( 'Better tools for whatever comes next.', 'instapass' ); ?><br><?php esc_html_e( 'Digital licences, subscriptions and invite keys.', 'instapass' ); ?></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php esc_html_e( 'Explore', 'instapass' ); ?></h3>
<!-- /wp:heading -->
<!-- wp:html -->
<ul class="ip-studio-footer-links"><li><a href="<?php echo esc_url( instapass_shop_url() ); ?>"><?php esc_html_e( 'All products', 'instapass' ); ?></a></li><li><a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>"><?php esc_html_e( 'FAQ', 'instapass' ); ?></a></li><li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact us', 'instapass' ); ?></a></li></ul>
<!-- /wp:html -->
</div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php esc_html_e( 'The details', 'instapass' ); ?></h3>
<!-- /wp:heading -->
<!-- wp:html -->
<ul class="ip-studio-footer-links"><li><a href="<?php echo esc_url( home_url( '/refund-policy/' ) ); ?>"><?php esc_html_e( 'Refund policy', 'instapass' ); ?></a></li><li><a href="<?php echo esc_url( home_url( '/terms-of-service/' ) ); ?>"><?php esc_html_e( 'Terms of service', 'instapass' ); ?></a></li><li><a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'Privacy policy', 'instapass' ); ?></a></li></ul>
<!-- /wp:html -->
</div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php esc_html_e( 'A little help?', 'instapass' ); ?></h3>
<!-- /wp:heading -->
<!-- wp:html -->
<ul class="ip-studio-footer-links"><li><a href="https://t.me/AJNF48" target="_blank" rel="noopener"><?php esc_html_e( 'Telegram @AJNF48', 'instapass' ); ?> <span aria-hidden="true">↗</span></a></li><li><a href="mailto:ajnf0408@gmail.com"><?php esc_html_e( 'ajnf0408@gmail.com', 'instapass' ); ?></a></li></ul>
<!-- /wp:html -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
<!-- wp:paragraph {"align":"wide","className":"ip-studio-footer-credit"} -->
<p class="alignwide ip-studio-footer-credit"><?php esc_html_e( 'Instapass. Built with Claude, ChatGPT, Cursor and Canva.', 'instapass' ); ?></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
