<?php
/**
 * Title: Aurora Studio Catalogue
 * Slug: instapass/spotlight
 * Categories: instapass, woocommerce
 * Description: A real WooCommerce product collection, with no invented ratings or time-window claims.
 * Viewport Width: 1400
 */
?>
<!-- wp:group {"tagName":"section","anchor":"products","className":"ip-studio-products","layout":{"type":"constrained"}} -->
<section class="wp-block-group ip-studio-products" id="products">
<!-- wp:group {"align":"wide","className":"ip-studio-section-head","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group alignwide ip-studio-section-head">
<!-- wp:group {"layout":{"type":"default"}} -->
<div class="wp-block-group">
<!-- wp:paragraph {"className":"ip-studio-kicker"} -->
<p class="ip-studio-kicker"><?php esc_html_e( 'MAKE YOUR NEXT MOVE', 'instapass' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Small keys. Big possibilities.', 'instapass' ); ?></h2>
<!-- /wp:heading -->
</div>
<!-- /wp:group -->
<!-- wp:paragraph {"className":"ip-studio-text-link"} -->
<p class="ip-studio-text-link"><a href="<?php echo esc_url( instapass_shop_url() ); ?>"><?php esc_html_e( 'Browse all products', 'instapass' ); ?> <span aria-hidden="true">↗</span></a></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<!-- wp:woocommerce/product-collection {"queryId":3,"query":{"perPage":4,"pages":1,"offset":0,"postType":"product","order":"desc","orderBy":"date","search":"","exclude":[],"inherit":false,"taxQuery":{},"isProductCollectionBlock":true,"featured":false,"woocommerceOnSale":false,"woocommerceStockStatus":["instock","outofstock","onbackorder"],"woocommerceAttributes":[],"woocommerceHandPickedProducts":[],"filterable":false},"tagName":"div","align":"wide","className":"ip-studio-product-grid","displayLayout":{"type":"flex","columns":4,"shrinkColumns":true},"dimensions":{"widthType":"fill"},"queryContextIncludes":["collection"]} -->
<div class="wp-block-woocommerce-product-collection alignwide ip-studio-product-grid">
<!-- wp:woocommerce/product-template -->
<!-- wp:woocommerce/product-image {"imageSizing":"thumbnail","showSaleBadge":false,"isDescendentOfQueryLoop":true} /-->
<!-- wp:instapass/discount-badge /-->
<!-- wp:paragraph {"className":"ip-card-badge"} -->
<p class="ip-card-badge"><?php esc_html_e( 'Digital access', 'instapass' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:post-title {"level":3,"isLink":true,"__woocommerceNamespace":"woocommerce/product-collection/product-title"} /-->
<!-- wp:instapass/product-signals {"showSold":false} /-->
<!-- wp:group {"className":"ip-price-row","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
<div class="wp-block-group ip-price-row">
<!-- wp:woocommerce/product-price {"isDescendentOfQueryLoop":true} /-->
<!-- wp:instapass/product-signals {"showStock":false} /-->
</div>
<!-- /wp:group -->
<!-- wp:woocommerce/product-button {"isDescendentOfQueryLoop":true} /-->
<!-- /wp:woocommerce/product-template -->
</div>
<!-- /wp:woocommerce/product-collection -->
<!-- wp:group {"className":"ip-studio-empty","layout":{"type":"default"}} -->
<div class="wp-block-group ip-studio-empty">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php esc_html_e( 'Your next upgrade is on its way.', 'instapass' ); ?></h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><?php esc_html_e( 'Products will appear here as they are added. Looking for something specific?', 'instapass' ); ?> <a href="https://t.me/AJNF48" target="_blank" rel="noopener"><?php esc_html_e( 'Ask us on Telegram', 'instapass' ); ?></a>.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</section>
<!-- /wp:group -->
