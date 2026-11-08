<?php
/** Exact contact replacement also covers saved Site Editor templates and pages. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
function instapass_current_contacts( $html ) {
    return str_replace( array( 'https://t.me/AJNF48', 'http://t.me/AJNF48', '@AJNF48', 'ajnf0408@gmail.com', 'instapass0408@gmail.com' ), array( 'https://t.me/Instapass0408', 'https://t.me/Instapass0408', '@Instapass0408', 'support@instapass.online', 'support@instapass.online' ), $html );
}
add_filter( 'render_block', 'instapass_current_contacts', 100 );
add_filter( 'the_content', 'instapass_current_contacts', 100 );
/** Historical descriptions must not contradict WooCommerce's current price. */
function instapass_current_price_copy( $content ) {
    return str_replace(
        array( 'ChatGPT Plus at $13.', 'Top up $50 worth of Claude Code API credits , yours for $13 (74% off).', 'Top up $100 worth of Claude Code API credits , yours for $15 (85% off).', 'Saves $70/mo vs official.', 'Four months of CapCut Pro for the price of one official month.' ),
        array( 'ChatGPT Plus.', 'Top up $50 worth of Claude Code API credits.', 'Top up $100 worth of Claude Code API credits.', '', 'Four months of CapCut Pro.' ),
        $content
    );
}
function instapass_product_price_copy( $content ) {
    if ( function_exists( 'get_post_type' ) && 'product' === get_post_type() ) { return instapass_current_price_copy( $content ); }
    return $content;
}
add_filter( 'the_content', 'instapass_product_price_copy', 99 );
add_filter( 'woocommerce_short_description', 'instapass_current_price_copy', 99 );
add_filter( 'woocommerce_product_get_description', 'instapass_current_price_copy', 99 );
add_filter( 'woocommerce_product_get_short_description', 'instapass_current_price_copy', 99 );
add_filter( 'get_the_excerpt', 'instapass_product_price_copy', 99 );
