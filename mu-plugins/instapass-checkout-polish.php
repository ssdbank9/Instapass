<?php
/**
 * Plugin Name: Instapass Checkout Polish
 * Description: Accessible manual-payment cards and honest GBP/EUR checkout recovery.
 * Version: 1.0.0
 */
defined('ABSPATH') || exit;
add_action('wp_enqueue_scripts', static function () {
 if (!function_exists('is_checkout') || !is_checkout() || is_wc_endpoint_url()) return;
 $file=WPMU_PLUGIN_DIR.'/instapass-checkout-polish.css';
 wp_enqueue_style('instapass-checkout-polish',content_url('mu-plugins/instapass-checkout-polish.css'),[],(string)filemtime($file));
},99);
add_filter('woocommerce_no_available_payment_methods_message',static function ($message) {
 if (!function_exists('instapass_cc_front') || !instapass_cc_front() || !function_exists('is_checkout') || !is_checkout() || is_wc_endpoint_url()) return $message;
 $currency=get_woocommerce_currency();
 if (!in_array($currency,['GBP','EUR'],true)) return $message;
 $url=add_query_arg('ip_currency','USD',wc_get_checkout_url());
 return '<strong>Payment in '.esc_html($currency).' is not available yet.</strong> Switch to USD to see available payment methods. Your basket stays with you; prices and discounts are recalculated in USD. Review the new total before ordering. <a class="ip-ux-currency-switch" href="'.esc_url($url).'">Switch to USD →</a> <a class="ip-checkout-help" href="'.esc_url(home_url('/contact/')).'">Need help?</a>';
},310);
add_filter('woocommerce_gateway_icon',static function ($icon,$gateway_id) {
 $marks=['instapass_binance'=>['binance','BN'],'instapass_crypto'=>['crypto','◈'],'instapass_easypaisa'=>['easypaisa','e'],'instapass_transfer'=>['transfer','↔'],'instapass_nayapay'=>['nayapay','N'],'instapass_wise'=>['wise','₩'],'instapass_paypal'=>['paypal','P']];
 if (!isset($marks[$gateway_id])) return $icon;
 [$brand,$mark]=$marks[$gateway_id];
 return '<span class="ip-payment-brand ip-payment-brand--'.esc_attr($brand).'" aria-hidden="true">'.esc_html($mark).'</span>';
},20,2);
