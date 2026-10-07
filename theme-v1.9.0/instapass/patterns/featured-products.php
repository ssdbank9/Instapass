<?php
/**
 * Title: Instapass Featured Products
 * Slug: instapass/featured-products
 * Categories: instapass, woocommerce
 * Description: Eight newest products in a responsive 4-column grid with Buy now buttons.
 * Viewport Width: 1400
 */
?>
<!-- wp:group {"tagName":"section","anchor":"products","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"backgroundColor":"cream","layout":{"type":"constrained"}} -->
<section class="wp-block-group has-cream-background-color has-background" id="products" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|10"}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"bottom"}} -->
<div class="wp-block-group"><!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Latest products', 'instapass' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"ip-section-note"} -->
<p class="ip-section-note"><a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>"><?php esc_html_e( 'See all products', 'instapass' ); ?></a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:woocommerce/product-collection {"queryId":1,"query":{"perPage":8,"pages":1,"offset":0,"postType":"product","order":"desc","orderBy":"date","search":"","exclude":[],"inherit":false,"taxQuery":{},"isProductCollectionBlock":true,"featured":false,"woocommerceOnSale":false,"woocommerceStockStatus":["instock","outofstock","onbackorder"],"woocommerceAttributes":[],"woocommerceHandPickedProducts":[],"filterable":false},"tagName":"div","displayLayout":{"type":"flex","columns":4,"shrinkColumns":true},"dimensions":{"widthType":"fill"},"queryContextIncludes":["collection"]} -->
<div class="wp-block-woocommerce-product-collection"><!-- wp:woocommerce/product-template -->
<!-- wp:woocommerce/product-image {"imageSizing":"thumbnail","showSaleBadge":false,"isDescendentOfQueryLoop":true} /-->

<!-- wp:instapass/discount-badge /-->

<!-- wp:paragraph {"className":"ip-card-badge"} -->
<p class="ip-card-badge"><?php esc_html_e( 'Digital access', 'instapass' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:post-title {"level":3,"isLink":true,"__woocommerceNamespace":"woocommerce/product-collection/product-title"} /-->

<!-- wp:instapass/product-signals {"showSold":false} /-->

<!-- wp:group {"className":"ip-price-row","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
<div class="wp-block-group ip-price-row"><!-- wp:woocommerce/product-price {"isDescendentOfQueryLoop":true} /-->

<!-- wp:instapass/product-signals {"showStock":false} /--></div>
<!-- /wp:group -->

<!-- wp:woocommerce/product-button {"isDescendentOfQueryLoop":true} /-->
<!-- /wp:woocommerce/product-template -->

<!-- wp:query-no-results -->
<!-- wp:paragraph {"align":"center","textColor":"muted"} -->
<p class="has-text-align-center has-muted-color has-text-color"><?php esc_html_e( 'New products are being added. Check back soon or', 'instapass' ); ?> <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'contact us', 'instapass' ); ?></a>.</p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results --></div>
<!-- /wp:woocommerce/product-collection -->

<!-- wp:paragraph {"align":"center","className":"ip-empty-note","textColor":"muted"} -->
<p class="has-text-align-center ip-empty-note has-muted-color has-text-color"><?php esc_html_e( 'New products are being added. Check back soon or', 'instapass' ); ?> <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'contact us', 'instapass' ); ?></a>.</p>
<!-- /wp:paragraph --></section>
<!-- /wp:group -->
