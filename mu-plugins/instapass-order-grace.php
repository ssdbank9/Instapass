<?php
/**
 * Plugin Name: Instapass Order Details Grace
 * Description: Extends the guest order-details grace period on order-received pages from the 10-minute default to 1 hour, so customers can re-read their order and payment details throughout the store's 45-minute activation window without an email-verification step. After one hour, the standard WooCommerce email verification protects the order details.
 * Version: 1.0.0
 */
if ( ! defined('ABSPATH') ) { exit; }
add_filter( 'woocommerce_order_email_verification_grace_period', function ( $seconds ) {
	return 60 * MINUTE_IN_SECONDS;
} );
