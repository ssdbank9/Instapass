<?php
/**
 * Plugin Name: Instapass UX Enhancements
 * Description: Additive presentation-only UX improvements: checkout receipt-email autofocus, one-tap coupon apply, collapsed optional fields, tap-to-copy payment details, primary payment CTA, payment step strip, tile from-price and Claude tier badges, accessibility touch-target and notice text sizes. No checkout processing, pricing, order or settings logic is changed.
 * Version: 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
add_action( 'wp_enqueue_scripts', function () {
	$base = content_url( 'mu-plugins/instapass-ux' );
	$css  = WPMU_PLUGIN_DIR . '/instapass-ux.css';
	$js   = WPMU_PLUGIN_DIR . '/instapass-ux.js';
	if ( is_readable( $css ) ) { wp_enqueue_style( 'instapass-ux', $base . '.css', array(), (string) filemtime( $css ) ); }
	if ( is_readable( $js ) ) { wp_enqueue_script( 'instapass-ux', $base . '.js', array(), (string) filemtime( $js ), true ); }
} );
