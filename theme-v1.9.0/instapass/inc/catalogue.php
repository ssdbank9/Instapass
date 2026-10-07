<?php
/** Browse products by purpose and retain unpriced listings without checkout. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
function instapass_tool_categories() {
	return array(
		'ai-tools'=>array('AI tools','Write, research and create with AI assistants.'),
		'productivity'=>array('Productivity','Organise projects, notes, communication and everyday work.'),
		'creativity'=>array('Creativity','Design visuals and create audio, video and digital content.'),
		'development'=>array('Development','Build, code, test and improve software.'),
		'automation'=>array('Automation','Connect apps and automate repeated tasks.'),
		'vps-cloud'=>array('VPS & Cloud','Host apps and manage cloud data and infrastructure.'),
		'business'=>array('Business','Support business operations, teams and growth.'),
		'wellbeing'=>array('Wellbeing','Tools for focus, learning and personal wellbeing.')
	);
}
function instapass_tool_category_nav() {
	if ( ! function_exists( 'wc_get_page_permalink' ) ) { return ''; }
	$selected = is_tax( 'product_cat' ) ? get_queried_object()->slug : 'all';
	$description = isset( instapass_tool_categories()[$selected] ) ? instapass_tool_categories()[$selected][1] : 'Find the right tools for work, creation and everyday projects.';
	$html = '<div class="ip-tool-browser"><nav class="ip-tool-tabs" aria-label="Tool categories"><a href="' . esc_url( instapass_shop_url() ) . '"' . ( 'all' === $selected ? ' aria-current="page"' : '' ) . '>All tools</a>';
	foreach ( instapass_tool_categories() as $slug => $category ) {
		$term = get_term_by( 'slug', $slug, 'product_cat' );
		if ( ! $term || is_wp_error( $term ) ) { continue; }
		$url = get_term_link( $term ); if ( is_wp_error( $url ) ) { continue; }
		$html .= '<a href="' . esc_url( $url ) . '"' . ( $selected === $slug ? ' aria-current="page"' : '' ) . '>' . esc_html( $category[0] ) . '</a>';
	}
	return $html . '</nav><p class="ip-category-intro">' . esc_html( $description ) . '</p></div>';
}
function instapass_tool_archive_nav( $html ) {
	return function_exists( 'is_shop' ) && ( is_shop() || is_product_category() ) ? $html . instapass_tool_category_nav() : $html;
}
add_filter( 'render_block_core/query-title', 'instapass_tool_archive_nav', 20 );
function instapass_home_tool_nav( $html, $parsed ) {
	return isset( $parsed['attrs']['className'] ) && 'ip-studio-section-head' === $parsed['attrs']['className'] ? $html . instapass_tool_category_nav() : $html;
}
add_filter( 'render_block_core/group', 'instapass_home_tool_nav', 20, 2 );
function instapass_block_product( $block ) {
	if ( ! function_exists( 'wc_get_product' ) ) { return null; }
	$id = isset( $block->context['postId'] ) ? absint( $block->context['postId'] ) : get_the_ID();
	return $id && 'product' === get_post_type( $id ) ? wc_get_product( $id ) : null;
}
function instapass_tool_brief( $html, $parsed, $block ) {
	if ( empty( $parsed['attrs']['__woocommerceNamespace'] ) || 'woocommerce/product-collection/product-title' !== $parsed['attrs']['__woocommerceNamespace'] ) { return $html; }
	$product = instapass_block_product( $block ); $brief = $product ? $product->get_meta( '_instapass_brief' ) : '';
	return $brief ? $html . '<p class="ip-tool-brief">' . esc_html( $brief ) . '</p>' : $html;
}
add_filter( 'render_block_core/post-title', 'instapass_tool_brief', 20, 3 );
function instapass_unpriced_price( $html, $parsed, $block ) {
	$product = instapass_block_product( $block );
	return $product && '' === $product->get_price() ? '<div class="wc-block-components-product-price ip-price-request">Price on request</div>' : $html;
}
add_filter( 'render_block_woocommerce/product-price', 'instapass_unpriced_price', 20, 3 );
function instapass_unpriced_button( $html, $parsed, $block ) {
	$product = instapass_block_product( $block );
	if ( ! $product ) { return $html; }
	$details = '<div class="wp-block-woocommerce-product-button"><a class="wp-element-button ip-details-button" href="' . esc_url( $product->get_permalink() ) . '">' . ( $product->is_in_stock() ? 'View details':'Out of stock · View details' ) . '</a></div>';
	if ( ! $product->is_in_stock() || '' === $product->get_price() ) { return $details; }
	if ( $product->is_type('simple') && $product->is_purchasable() ) { return '<div class="wp-block-woocommerce-product-button ip-buy-actions"><a class="wp-element-button" href="' . esc_url(add_query_arg(array('add-to-cart'=>$product->get_id(),'ip_buy_now'=>'1'),wc_get_checkout_url())) . '">Buy now</a><a class="ip-add-more" href="' . esc_url(add_query_arg(array('add-to-cart'=>$product->get_id(),'ip_add_more'=>'1'),instapass_shop_url())) . '">Add to basket</a></div>'; }
	return $html;
}
add_filter( 'render_block_woocommerce/product-button', 'instapass_unpriced_button', 20, 3 );
function instapass_unpriced_enquiry( $html, $parsed, $block ) {
	$product = instapass_block_product( $block );
	if ($product && !$product->is_in_stock()) { return '<p class="ip-unavailable-note">This offering is currently out of stock. You can still review its details.</p>'; }
	return $product && '' === $product->get_price() ? '<p class="ip-price-enquiry"><a class="wp-element-button" href="' . esc_url( home_url( '/contact/' ) ) . '">Request a price</a></p>' : $html;
}
add_filter( 'render_block_woocommerce/add-to-cart-form', 'instapass_unpriced_enquiry', 20, 3 );
