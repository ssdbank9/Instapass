<?php
/**
 * Title: Aurora Studio Categories
 * Slug: instapass/categories
 * Categories: instapass, woocommerce
 * Description: Four generous, accessible links to real product categories.
 * Viewport Width: 1400
 */
$items = array(
 array( 'licences', __( 'Licences', 'instapass' ), __( 'Your software, unlocked.', 'instapass' ), '<circle cx="8" cy="8" r="5"/><path d="m12 12 9 9m-4-4 3-3m-7 7 3-3"/>' ),
 array( 'subscriptions', __( 'Subscriptions', 'instapass' ), __( 'More room to create.', 'instapass' ), '<path d="m12 3 3 6 6 3-6 3-3 6-3-6-6-3 6-3z"/>' ),
 array( 'invite-keys', __( 'Invite keys', 'instapass' ), __( 'Open a new door.', 'instapass' ), '<path d="M4 6h16v4a2 2 0 0 0 0 4v4H4v-4a2 2 0 0 0 0-4V6zM14 6v12"/>' ),
 array( 'bundles', __( 'Bundles', 'instapass' ), __( 'Find your perfect stack.', 'instapass' ), '<path d="m12 3 9 5-9 5-9-5 9-5zm-9 9 9 5 9-5m-18 5 9 5 9-5"/>' ),
);
?>
<!-- wp:group {"tagName":"section","className":"ip-studio-categories","layout":{"type":"constrained"}} -->
<section class="wp-block-group ip-studio-categories">
<!-- wp:group {"align":"wide","className":"ip-studio-section-head","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group alignwide ip-studio-section-head">
<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Find your kind of upgrade.', 'instapass' ); ?></h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><?php esc_html_e( 'A little access goes a long way.', 'instapass' ); ?></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<!-- wp:html -->
<div class="ip-studio-category-grid alignwide">
<?php foreach ( $items as $item ) : ?>
<a class="ip-studio-category ip-studio-category--<?php echo esc_attr( $item[0] ); ?>" href="<?php echo esc_url( instapass_category_url( $item[0] ) ); ?>">
<span class="ip-studio-category-icon"><svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?php echo $item[3]; // Static theme-authored SVG paths only. ?></svg></span>
<span class="ip-studio-category-title"><?php echo esc_html( $item[1] ); ?></span>
<span class="ip-studio-category-desc"><?php echo esc_html( $item[2] ); ?></span>
<span class="ip-studio-category-arrow" aria-hidden="true">↗</span>
</a>
<?php endforeach; ?>
</div>
<!-- /wp:html -->
</section>
<!-- /wp:group -->
