<?php
defined('ABSPATH')||exit;
global $wpdb;
echo wp_json_encode(['taxes'=>['enabled'=>get_option('woocommerce_calc_taxes'),'prices_include_tax'=>get_option('woocommerce_prices_include_tax'),'shop_display'=>get_option('woocommerce_tax_display_shop'),'cart_display'=>get_option('woocommerce_tax_display_cart'),'rate_count'=>(int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}woocommerce_tax_rates")],'max_annual'=>['id'=>140,'sku'=>wc_get_product(140)->get_sku(),'usd'=>wc_get_product(140)->get_price('edit')],'plugins'=>['tenor'=>is_plugin_active('instapass-tenor-offers/instapass-tenor-offers.php')]],JSON_PRETTY_PRINT);
