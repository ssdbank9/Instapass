<?php
/** Original artwork and distinct colours for product cards. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function instapass_art_choices() {
	return array( 'auto' => 'Automatic', 'electric-chat' => 'Electric blue chat', 'midnight-orbit' => 'Black chrome orbit', 'purple-chat' => 'Lilac glass chat', 'purple-prism' => 'Purple glass prism', 'purple-orbit' => 'Purple orbital rings', 'purple-key' => 'Purple digital key', 'rust-prism' => 'Rust prism', 'pink-key' => 'Warm pink key', 'cyan-orbit' => 'Cyan orbit' );
}
function instapass_palette_choices() {
	return array( 'auto' => 'Automatic', 'electric' => 'Electric blue', 'midnight' => 'Midnight black', 'violet' => 'Violet', 'rust' => 'Rust', 'lilac' => 'Lilac', 'pink' => 'Warm pink', 'cyan' => 'Cyan', 'coral' => 'Coral', 'teal' => 'Teal', 'amber' => 'Golden amber' );
}
function instapass_product_art_fields() {
	global $product_object;
	if ( ! $product_object ) { return; }
	echo '<div class="options_group">';
	woocommerce_wp_select( array( 'id' => '_instapass_tile_art', 'label' => 'Tile artwork', 'options' => instapass_art_choices(), 'value' => $product_object->get_meta( '_instapass_tile_art' ) ?: 'auto', 'description' => 'Used when no product image is uploaded. Your uploaded image takes priority.', 'desc_tip' => true ) );
	woocommerce_wp_select( array( 'id' => '_instapass_tile_palette', 'label' => 'Tile colour', 'options' => instapass_palette_choices(), 'value' => $product_object->get_meta( '_instapass_tile_palette' ) ?: 'auto', 'description' => 'Automatic assigns a stable vibrant colour. Product photos and real logos are never recoloured.', 'desc_tip' => true ) );
	echo '</div>';
}
// Legacy artwork preferences retained in metadata for rollback; storefront uses logos.

function instapass_save_product_art( $product ) {
	foreach ( array( '_instapass_tile_art' => instapass_art_choices(), '_instapass_tile_palette' => instapass_palette_choices() ) as $field => $choices ) {
		if ( ! isset( $_POST[$field] ) || ! is_scalar( $_POST[$field] ) ) { continue; }
		$value = sanitize_key( wp_unslash( $_POST[$field] ) );
		if ( isset( $choices[$value] ) ) { $product->update_meta_data( $field, $value ); }
	}
}
add_action( 'woocommerce_admin_process_product_object', 'instapass_save_product_art' );

function instapass_product_visual( $product ) {
	$id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
	$source = $product->is_type( 'variation' ) ? wc_get_product( $id ) : $product;
	if ( ! $source ) { $source = $product; }
	$seed = abs( crc32( $source->get_sku() ?: (string) $id ) );
	$palettes = array( 'electric', 'midnight', 'violet', 'rust', 'lilac', 'pink', 'cyan', 'coral', 'teal', 'amber' );
	$art_map = array( 'electric' => 'electric-chat', 'midnight' => 'midnight-orbit', 'violet' => 'purple-prism', 'rust' => 'rust-prism', 'lilac' => 'purple-chat', 'pink' => 'pink-key', 'cyan' => 'cyan-orbit', 'coral' => 'purple-key', 'teal' => 'purple-orbit', 'amber' => 'purple-prism' );
	$art = $source->get_meta( '_instapass_tile_art' ); $palette = $source->get_meta( '_instapass_tile_palette' );
	$palette = isset( instapass_palette_choices()[$palette] ) && 'auto' !== $palette ? $palette : $palettes[$seed % count( $palettes )];
	return array( 'art' => isset( instapass_art_choices()[$art] ) && 'auto' !== $art ? $art : $art_map[$palette], 'palette' => $palette );
}

function instapass_render_product_art( $html, $parsed, $block ) {
	if ( ! function_exists( 'wc_get_product' ) ) { return $html; }
	$id = isset( $block->context['postId'] ) ? absint( $block->context['postId'] ) : get_the_ID();
	$product = $id ? wc_get_product( $id ) : null;
	if ( ! $product ) { return $html; }
	$file = instapass_product_logo( $product );
	$brand = $product->get_meta( '_instapass_brand' ) ?: explode( ' — ', $product->get_name() )[0];
	$brand_class = $file ? ' ip-brand-' . sanitize_html_class( pathinfo( $file, PATHINFO_FILENAME ) ) : '';
	$visual = $file ? '<img class="ip-tile-logo' . esc_attr( $brand_class ) . '" src="' . esc_url( get_template_directory_uri() . '/assets/logos/' . $file ) . '" alt="' . esc_attr( explode( ' — ', $product->get_name() )[0] . ' logo' ) . '" width="120" height="120" loading="lazy">' : '<span class="ip-logo-wordmark">' . esc_html( ucwords( str_replace( '-', ' ', $brand ) ) ) . '</span>';
	$secondary = $product->get_meta( '_instapass_brand_secondary' );
	if ( is_string($secondary) && preg_match('/^[a-z0-9-]+$/',$secondary) && file_exists(get_template_directory().'/assets/logos/'.$secondary.'.svg') ) { $visual .= '<span class="ip-logo-plus" aria-hidden="true">+</span><img class="ip-tile-logo" src="'.esc_url(get_template_directory_uri().'/assets/logos/'.$secondary.'.svg').'" alt="'.esc_attr(ucfirst($secondary).' logo').'" width="90" height="90" loading="lazy">'; }
	return '<div class="wc-block-components-product-image ip-logo-tile"><a href="' . esc_url($product->get_permalink()) . '" aria-label="' . esc_attr($product->get_name()) . '">' . $visual . '</a></div>';
}
add_filter( 'render_block_woocommerce/product-image', 'instapass_render_product_art', 10, 3 );
add_filter( 'render_block_woocommerce/product-image-gallery', 'instapass_render_product_art', 10, 3 );
// Native cart and product widgets use the same recognised brand asset.
add_filter('woocommerce_product_get_image',function($html,$product,$size,$attr){$file=instapass_product_logo($product);if(!$file){return $html;}$brand_class=' ip-brand-'.sanitize_html_class(pathinfo($file,PATHINFO_FILENAME));return '<img src="'.esc_url(get_template_directory_uri().'/assets/logos/'.$file).'" alt="'.esc_attr($product->get_name()).'" class="ip-cart-brand-logo'.esc_attr($brand_class).'" width="80" height="80" loading="lazy">';},20,4);

function instapass_product_logo( $product ) {
	$brand = $product->get_meta( '_instapass_brand' );
	if ( is_string( $brand ) && preg_match( '/^[a-z0-9-]+$/', $brand ) ) {
		foreach ( array( 'svg', 'png' ) as $ext ) { if ( file_exists( get_template_directory() . '/assets/logos/' . $brand . '.' . $ext ) ) { return $brand . '.' . $ext; } }
	}
	$sku = strtolower( $product->get_sku() );
	$map = array( 'chatgpt' => 'openai.svg', 'claude' => 'claude.svg', 'cursor' => 'cursor.svg', 'gemini' => 'gemini.svg', 'super-grok' => 'grok.svg', 'grok' => 'grok.svg', 'suno' => 'suno.svg', 'copilot' => 'copilot.svg', 'capcut' => 'capcut.png', 'elevenlabs' => 'elevenlabs.svg', 'midjourney' => 'midjourney.png', 'notion' => 'notion.svg', 'perplexity' => 'perplexity.svg' );
	foreach ( $map as $prefix => $file ) {
		if ( 0 === strpos( $sku, $prefix ) && file_exists( get_template_directory() . '/assets/logos/' . $file ) ) { return $file; }
	}
	return '';
}
function instapass_render_product_logo( $html, $parsed, $block ) {
	if ( ! function_exists( 'wc_get_product' ) ) { return $html; }
	$id = isset( $block->context['postId'] ) ? absint( $block->context['postId'] ) : get_the_ID();
	if ( 'product' !== get_post_type( $id ) ) { return $html; }
	$product = wc_get_product( $id ); $file = $product ? instapass_product_logo( $product ) : '';
	if ( ! $file ) { return $html; }
	$brand_class = ' ip-brand-' . sanitize_html_class( pathinfo( $file, PATHINFO_FILENAME ) );
	$logo = '<span class="ip-product-brand"><img class="ip-product-logo' . esc_attr( $brand_class ) . '" src="' . esc_url( get_template_directory_uri() . '/assets/logos/' . $file ) . '" alt="" aria-hidden="true" width="28" height="28"></span>';
	$secondary = $product->get_meta( '_instapass_brand_secondary' );
	if ( is_string( $secondary ) && preg_match( '/^[a-z0-9-]+$/', $secondary ) && file_exists( get_template_directory() . '/assets/logos/' . $secondary . '.svg' ) ) {
		$logo .= '<span class="ip-product-brand"><img class="ip-product-logo" src="' . esc_url( get_template_directory_uri() . '/assets/logos/' . $secondary . '.svg' ) . '" alt="" aria-hidden="true" width="28" height="28"></span>';
	}
	return preg_replace( '/(<h[1-6]\b[^>]*>)/i', '$1' . $logo, $html, 1 );
}
add_filter( 'render_block_core/post-title', 'instapass_render_product_logo', 10, 3 );

function instapass_openai_reference( $product ) {
	return $product && ( 'chatgpt-plus' === $product->get_sku() || false !== stripos( $product->get_name(), 'ChatGPT' ) );
}
function instapass_openai_disclosure( $html, $parsed, $block ) {
	if ( ! function_exists( 'wc_get_product' ) ) { return $html; }
	$id = isset( $block->context['postId'] ) ? absint( $block->context['postId'] ) : get_the_ID();
	if ( instapass_openai_reference( wc_get_product( $id ) ) ) {
		$html .= '<p class="ip-openai-notice">OpenAI and ChatGPT are trademarks of OpenAI.<br>Instapass is not affiliated with OpenAI.</p>';
	}
	return $html;
}
add_filter( 'render_block_woocommerce/product-details', 'instapass_openai_disclosure', 10, 3 );
