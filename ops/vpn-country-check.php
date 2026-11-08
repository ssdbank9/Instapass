<?php
if (!function_exists('ip_access_safety_cart_needs_usage_country')) {
    throw new RuntimeException('Access-safety MU plugin is not loaded.');
}

function ip_vpn_country_assert($condition, $message) {
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$ai_ids = array();
$other_id = 0;
foreach (wc_get_products(array('status' => 'publish', 'limit' => 250, 'return' => 'objects')) as $product) {
    if (!$product->is_purchasable() || !$product->is_in_stock()) {
        continue;
    }
    $brand = strtolower((string) $product->get_meta('_instapass_brand', true));
    $text = $product->get_name() . ' ' . $product->get_meta('_instapass_tenor_family', true);
    if (!$other_id && !ip_access_safety_product_needs_usage_country($product)) {
        $other_id = $product->get_id();
    }
    if (ip_access_safety_product_needs_usage_country($product)) {
        $ai_ids[] = $product->get_id();
    }
}
ip_vpn_country_assert(count($ai_ids) > 0, 'No purchasable ChatGPT/Claude fixture product found.');
ip_vpn_country_assert($other_id > 0, 'No purchasable non-ChatGPT/Claude fixture product found.');

$cart = WC()->cart;
$cart->empty_cart(true);
ip_vpn_country_assert($cart->add_to_cart($ai_ids[0], 1), 'Could not add AI fixture product to isolated cart.');
$fields = apply_filters('woocommerce_checkout_fields', array('billing' => array()));
$field = $fields['billing']['instapass_usage_country'] ?? array();
ip_vpn_country_assert(($field['type'] ?? '') === 'select' && !empty($field['required']), 'AI checkout must require the usage-country select. ' . wp_json_encode(array('cart_requires_country' => ip_access_safety_cart_needs_usage_country(), 'countries_ready' => (bool) (WC()->countries ?? null), 'cart_product_ids' => array_map(function ($item) { return $item['product_id'] ?? 0; }, $cart->get_cart()), 'filtered_field_keys' => array_keys($fields['billing'] ?? array()))));
ip_vpn_country_assert(isset($field['options']['US'], $field['options']['PK']) && ($field['options'][''] ?? '') !== '', 'Country select must include valid country choices and a blank prompt.');

$errors = new WP_Error();
do_action('woocommerce_after_checkout_validation', array(), $errors);
ip_vpn_country_assert($errors->get_error_message('usage_country') !== '', 'Missing country must fail checkout validation.');
$errors = new WP_Error();
do_action('woocommerce_after_checkout_validation', array('instapass_usage_country' => 'ZZ'), $errors);
ip_vpn_country_assert($errors->get_error_message('usage_country') !== '', 'Unknown country code must fail checkout validation.');
$errors = new WP_Error();
do_action('woocommerce_after_checkout_validation', array('instapass_usage_country' => 'US'), $errors);
ip_vpn_country_assert($errors->get_error_message('usage_country') === '', 'Valid country must pass checkout validation.');

$order = new WC_Order();
do_action('woocommerce_checkout_create_order', $order, array('billing_email' => 'qa@example.test', 'instapass_usage_country' => 'US'));
ip_vpn_country_assert($order->get_meta('_instapass_usage_country') === 'US', 'Country must be stored as private order metadata.');
$email_fields = apply_filters('woocommerce_email_order_meta_fields', array(), false, $order);
ip_vpn_country_assert(($email_fields['ip_usage_country']['value'] ?? '') === 'United States (US)', 'Order email should show the selected country. ' . wp_json_encode(array('saved_meta' => $order->get_meta('_instapass_usage_country'), 'label_helper' => ip_access_safety_usage_country_label($order), 'email_field_keys' => array_keys($email_fields))));

$cart->empty_cart(true);
ip_vpn_country_assert($cart->add_to_cart($other_id, 1), 'Could not add non-AI fixture product to isolated cart.');
$non_ai_fields = apply_filters('woocommerce_checkout_fields', array('billing' => array()));
ip_vpn_country_assert(!isset($non_ai_fields['billing']['instapass_usage_country']), 'Non-AI products must not request a usage country.');
$errors = new WP_Error();
do_action('woocommerce_after_checkout_validation', array(), $errors);
ip_vpn_country_assert($errors->get_error_message('usage_country') === '', 'Non-AI checkout must not require a usage country.');
$cart->empty_cart(true);

echo wp_json_encode(array('result' => 'pass', 'country_field' => array('shown_for_ai_products' => true, 'required' => true, 'rejects_missing_and_unknown' => true, 'valid_code' => 'US', 'saved_to_order' => true, 'included_in_order_email' => true), 'non_ai_products' => array('field_hidden' => true, 'not_required' => true), 'products' => array('ai_fixture_count' => count($ai_ids), 'non_ai_fixture_found' => true)));
