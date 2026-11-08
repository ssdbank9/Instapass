<?php
/**
 * Plugin Name: Instapass Access Safety
 * Description: VPN safety notice and activation-country collection for ChatGPT and Claude orders.
 * Version: 1.0.0
 */
defined('ABSPATH') || exit;

function ip_access_safety_product_needs_usage_country($product) {
	if (!$product instanceof WC_Product) {
		return false;
	}
	$products = array($product);
	if ($product->is_type('variation') && $product->get_parent_id()) {
		$parent = wc_get_product($product->get_parent_id());
		if ($parent) {
			$products[] = $parent;
		}
	}
	foreach ($products as $candidate) {
		$brand = strtolower(trim((string) $candidate->get_meta('_instapass_brand', true)));
		$name = (string) $candidate->get_name();
		$family = (string) $candidate->get_meta('_instapass_tenor_family', true);
		if (in_array($brand, array('openai', 'claude'), true) || preg_match('/\b(?:chatgpt|claude)\b/i', $name . ' ' . $family)) {
			return true;
		}
	}
	return false;
}

function ip_access_safety_cart_needs_usage_country() {
	if (!function_exists('WC') || !WC()->cart) {
		return false;
	}
	foreach (WC()->cart->get_cart() as $item) {
		$product = $item['data'] ?? null;
		if (ip_access_safety_product_needs_usage_country($product)) {
			return true;
		}
	}
	return false;
}

add_filter('woocommerce_checkout_fields', function ($fields) {
	if (!ip_access_safety_cart_needs_usage_country() || !function_exists('WC') || !WC()->countries) {
		return $fields;
	}
	$options = array('' => __('Select the country where you will use these products', 'instapass')) + WC()->countries->get_countries();
	$fields['billing']['instapass_usage_country'] = array(
		'type' => 'select',
		'label' => __('Country where you will use ChatGPT or Claude', 'instapass'),
		'required' => true,
		'class' => array('form-row-wide'),
		'priority' => 125,
		'options' => $options,
		'description' => __('We use this to provide region-specific activation guidance. Provider availability and terms vary by country.', 'instapass'),
		'autocomplete' => 'country',
	);
	return $fields;
}, 999);

add_action('woocommerce_after_checkout_validation', function ($data, $errors) {
	if (!ip_access_safety_cart_needs_usage_country()) {
		return;
	}
	$code = strtoupper(trim(sanitize_text_field($data['instapass_usage_country'] ?? '')));
	$countries = function_exists('WC') && WC()->countries ? WC()->countries->get_countries() : array();
	if ($code === '' || !isset($countries[$code])) {
		$errors->add('usage_country', __('Please select the country where you will use ChatGPT or Claude.', 'instapass'));
	}
}, 10, 2);

add_action('woocommerce_checkout_create_order', function ($order, $data) {
	if (!ip_access_safety_cart_needs_usage_country()) {
		return;
	}
	$code = strtoupper(trim(sanitize_text_field($data['instapass_usage_country'] ?? '')));
	$countries = function_exists('WC') && WC()->countries ? WC()->countries->get_countries() : array();
	if (isset($countries[$code])) {
		$order->update_meta_data('_instapass_usage_country', $code);
	}
}, 30, 2);

function ip_access_safety_usage_country_label($order) {
	$code = strtoupper((string) $order->get_meta('_instapass_usage_country', true));
	$countries = function_exists('WC') && WC()->countries ? WC()->countries->get_countries() : array();
	if ($code === '' || !isset($countries[$code])) {
		return '';
	}
	return $countries[$code];
}

add_action('woocommerce_admin_order_data_after_billing_address', function ($order) {
	$value = ip_access_safety_usage_country_label($order);
	if ($value !== '') {
		echo '<p><strong>' . esc_html__('Product use country:', 'instapass') . '</strong> ' . esc_html($value) . '</p>';
	}
});

add_filter('woocommerce_email_order_meta_fields', function ($fields, $sent_to_admin, $order) {
	$value = ip_access_safety_usage_country_label($order);
	if ($value !== '') {
		$fields['ip_usage_country'] = array('label' => __('Country of use', 'instapass'), 'value' => $value);
	}
	return $fields;
}, 20, 3);

add_action('wp_enqueue_scripts', function () {
	$base = content_url('mu-plugins/instapass-access-safety');
	$css_path = WPMU_PLUGIN_DIR . '/instapass-access-safety.css';
	$js_path = WPMU_PLUGIN_DIR . '/instapass-access-safety.js';
	if (is_readable($css_path)) {
		wp_enqueue_style('instapass-access-safety', $base . '.css', array(), (string) filemtime($css_path));
	}
	if (is_readable($js_path)) {
		wp_enqueue_script('instapass-access-safety', $base . '.js', array(), (string) filemtime($js_path), true);
	}
});

add_action('wp_footer', function () {
	if (is_admin()) {
		return;
	}
	?>
	<aside id="ip-vpn-safety" class="ip-vpn-safety" role="region" aria-label="Important account access notice">
		<div class="ip-vpn-safety__copy">
			<strong><?php esc_html_e('Protect your ChatGPT and Claude access: do not use a VPN.', 'instapass'); ?></strong>
			<p><?php esc_html_e('A VPN can mask your location and trigger regional checks. Using a service in ways that violate its terms may lead to restricted, suspended or terminated access. Use it from a supported country and follow the provider’s current rules.', 'instapass'); ?></p>
			<nav aria-label="Provider country guidance">
				<a href="https://help.openai.com/en/articles/7947663-chatgpt-supported-countries" target="_blank" rel="noopener noreferrer">OpenAI supported countries</a>
				<span aria-hidden="true"> · </span>
			<a href="https://www.anthropic.com/legal/supported-regions" target="_blank" rel="noopener noreferrer">Claude supported regions</a>
			</nav>
		</div>
		<button class="ip-vpn-safety__close" type="button" data-ip-vpn-dismiss aria-label="Dismiss this notice">×</button>
	</aside>
	<?php
}, 1);
