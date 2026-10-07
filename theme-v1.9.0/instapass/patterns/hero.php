<?php
/**
 * Title: Aurora Studio Hero
 * Slug: instapass/hero
 * Categories: instapass, banner
 * Description: Airy editorial hero with original generated glass-key artwork and clear shopping actions.
 * Viewport Width: 1400
 */
?>
<!-- wp:group {"tagName":"section","className":"ip-studio-hero","layout":{"type":"constrained"}} -->
<section class="wp-block-group ip-studio-hero">
<!-- wp:columns {"align":"wide","verticalAlignment":"center","className":"ip-studio-hero__columns"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center ip-studio-hero__columns">
<!-- wp:column {"verticalAlignment":"center","className":"ip-studio-hero__copy"} -->
<div class="wp-block-column is-vertically-aligned-center ip-studio-hero__copy">
<!-- wp:paragraph {"className":"ip-studio-kicker"} -->
<p class="ip-studio-kicker"><?php esc_html_e( 'YOUR NEXT DIGITAL UPGRADE', 'instapass' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading"><?php esc_html_e( 'Better tools.', 'instapass' ); ?><br><span><?php esc_html_e( 'Bigger ideas.', 'instapass' ); ?></span></h1>
<!-- /wp:heading -->
<!-- wp:paragraph {"className":"ip-studio-lead"} -->
<p class="ip-studio-lead"><?php esc_html_e( 'Your shortcut to software licences, subscriptions and invite keys. Delivered by email, with activation within 15 minutes of confirmed payment.', 'instapass' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"className":"ip-studio-primary"} -->
<div class="wp-block-button ip-studio-primary"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( instapass_shop_url() ); ?>"><?php esc_html_e( 'Explore the shop', 'instapass' ); ?> <span aria-hidden="true">↗</span></a></div>
<!-- /wp:button -->
<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#how-it-works"><?php esc_html_e( 'How it works', 'instapass' ); ?></a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
<!-- wp:paragraph {"className":"ip-studio-reassurance"} -->
<p class="ip-studio-reassurance"><?php esc_html_e( '1-click checkout', 'instapass' ); ?> <span aria-hidden="true">·</span> <?php esc_html_e( 'Any issue? Full refund, no questions asked.', 'instapass' ); ?></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<!-- wp:column {"verticalAlignment":"center","className":"ip-studio-hero__visual"} -->
<div class="wp-block-column is-vertically-aligned-center ip-studio-hero__visual">
<!-- wp:html -->
<div class="ip-studio-art-wrap">
<img class="ip-studio-art" src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/aurora-key.webp' ); ?>" width="1536" height="1024" alt="<?php esc_attr_e( 'A glass key and lightning bolt floating through an iridescent ring with digital access passes.', 'instapass' ); ?>" fetchpriority="high" decoding="async">
<div class="ip-studio-art-label"><span class="ip-studio-label-dot" aria-hidden="true"></span><span><?php esc_html_e( 'One key. More possibilities.', 'instapass' ); ?></span></div>
</div>
<!-- /wp:html -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
</section>
<!-- /wp:group -->
