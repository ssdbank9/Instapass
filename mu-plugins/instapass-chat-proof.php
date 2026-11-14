<?php
/**
 * Plugin Name: Instapass Chat Proof
 * Description: Adds WhatsApp/Telegram click-to-send proof buttons to the order payment page, and pushes a Telegram notification to the owner when a customer submits payment proof on the website. Chat buttons need no setup; the Telegram push requires a free bot token in the settings.
 * Version: 1.0.0
 */
if ( ! defined('ABSPATH') ) { exit; }

function ipcp_settings() {
	$defaults = array(
		'whatsapp'        => '18322503059',
		'tg_token'        => '',
		'tg_chat'         => '',
		'enabled_buttons' => 'yes',
		'enabled_push'    => 'yes',
		'binance_pay_link'=> '',
	);
	$saved = get_option('instapass_chat_proof', array());
	return array_merge($defaults, is_array($saved) ? $saved : array());
}

add_action('admin_menu', function () {
	add_submenu_page('instapass-control', 'Chat payment proof', 'Chat Proof', 'manage_woocommerce', 'instapass-chat-proof', 'ipcp_admin_page');
}, 41);

function ipcp_admin_page() {
	if ( ! current_user_can('manage_woocommerce') ) { return; }
	$s = ipcp_settings();
	echo '<div class="wrap"><h1>Chat payment proof</h1>';
	if ( isset($_GET['saved']) ) { echo '<div class="notice notice-success"><p>Settings saved.</p></div>'; }
	if ( isset($_GET['tested']) ) { echo $_GET['tested'] === 'ok' ? '<div class="notice notice-success"><p>Test message delivered to Telegram.</p></div>' : '<div class="notice notice-error"><p>Test message could not be delivered. Check the bot token and chat ID.</p></div>'; }
	echo '<p>Customers can send their payment proof by WhatsApp or Telegram from the order page. When they submit proof on the website instead, the site sends you a Telegram message with the order details.</p>';
	echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'">';
	echo '<input type="hidden" name="action" value="ipcp_save">';
	wp_nonce_field('ipcp_save');
	echo '<table class="form-table"><tbody>';
	echo '<tr><th scope="row"><label for="ipcp-whatsapp">WhatsApp number for customer button</label></th><td><input id="ipcp-whatsapp" name="whatsapp" type="text" class="regular-text" value="'.esc_attr($s['whatsapp']).'" required><p class="description">Digits only, with country code (for example 18322503059). Used to open the customer\'s chat with you.</p></td></tr>';
	echo '<tr><th scope="row"><label for="ipcp-token">Telegram bot token</label></th><td><input id="ipcp-token" name="tg_token" type="password" class="regular-text" value="'.esc_attr($s['tg_token']).'" autocomplete="new-password"><p class="description">Create a free bot with @BotFather (/newbot), paste the token here, then message your bot once and open <code>https://api.telegram.org/bot&lt;TOKEN&gt;/getUpdates</code> to find your chat ID.</p></td></tr>';
	echo '<tr><th scope="row"><label for="ipcp-chat">Telegram chat ID</label></th><td><input id="ipcp-chat" name="tg_chat" type="text" class="regular-text" value="'.esc_attr($s['tg_chat']).'"></td></tr>';
	echo '<tr><th scope="row"><label for="ipcp-binance">Binance app payment link (optional)</label></th><td><input id="ipcp-binance" name="binance_pay_link" type="url" class="regular-text" value="'.esc_attr($s['binance_pay_link']).'" placeholder="https://pay.binance.com/..."><p class="description">In your Binance app: Pay → Receive/Request → create a receive link (no fixed amount), copy it here. Shows a "Pay with Binance app" button on Binance order pages. Leave empty to hide the button.</p></td></tr>';
	echo '<tr><th scope="row">Show chat buttons to customers</label></th><td><label><input type="checkbox" name="enabled_buttons" value="yes" '.checked($s['enabled_buttons'], 'yes', false).'> Show WhatsApp and Telegram proof buttons on the order payment page</label></td></tr>';
	echo '<tr><th scope="row">Notify me on Telegram</th><td><label><input type="checkbox" name="enabled_push" value="yes" '.checked($s['enabled_push'], 'yes', false).'> Send me a Telegram message when a customer submits payment proof on the website</label></td></tr>';
	echo '</tbody></table>';
	submit_button('Save settings');
	echo '</form>';
	if ( $s['tg_token'] !== '' && $s['tg_chat'] !== '' ) {
		echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="ipcp_test">';
		wp_nonce_field('ipcp_test');
		submit_button('Send test message to my Telegram', 'secondary');
		echo '</form>';
	}
	echo '</div>';
}

add_action('admin_post_ipcp_save', function () {
	if ( ! current_user_can('manage_woocommerce') ) { wp_die('Permission denied', 403); }
	check_admin_referer('ipcp_save');
	$prev = ipcp_settings();
	$binance = isset($_POST['binance_pay_link']) ? esc_url_raw(trim(wp_unslash($_POST['binance_pay_link']))) : '';
	if ( $binance !== '' ) {
		$host = wp_parse_url($binance, PHP_URL_HOST);
		if ( !preg_match('/(^|\.)binance\.com$/i', (string) $host) ) { $binance = ''; }
	}
	$next = array(
		'whatsapp'        => preg_replace('/\D/', '', isset($_POST['whatsapp']) ? wp_unslash($_POST['whatsapp']) : ''),
		'tg_token'        => trim(sanitize_text_field(wp_unslash($_POST['tg_token'] ?? ''))),
		'tg_chat'         => preg_replace('/\D/', '', isset($_POST['tg_chat']) ? wp_unslash($_POST['tg_chat']) : ''),
		'enabled_buttons' => empty($_POST['enabled_buttons']) ? 'no' : 'yes',
		'enabled_push'    => empty($_POST['enabled_push']) ? 'no' : 'yes',
		'binance_pay_link'=> $binance,
	);
	if ( $next['tg_token'] === '' ) { $next['tg_token'] = $prev['tg_token']; }
	update_option('instapass_chat_proof', $next, false);
	wp_safe_redirect(admin_url('admin.php?page=instapass-chat-proof&saved=1'));
	exit;
});

function ipcp_send_telegram($text) {
	$s = ipcp_settings();
	if ( $s['tg_token'] === '' || $s['tg_chat'] === '' ) { return false; }
	$response = wp_remote_post('https://api.telegram.org/bot'.rawurlencode($s['tg_token']).'/sendMessage', array(
		'timeout' => 8,
		'body'    => array('chat_id' => $s['tg_chat'], 'text' => $text, 'disable_web_page_preview' => 'true'),
	));
	return !is_wp_error($response) && (int) wp_remote_retrieve_response_code($response) === 200;
}

add_action('admin_post_ipcp_test', function () {
	if ( ! current_user_can('manage_woocommerce') ) { wp_die('Permission denied', 403); }
	check_admin_referer('ipcp_test');
	$ok = ipcp_send_telegram('Test notification from Instapass — chat proof is working.');
	wp_safe_redirect(admin_url('admin.php?page=instapass-chat-proof&tested='.($ok ? 'ok' : 'fail')));
	exit;
});

/* Owner push when a customer submits proof through the website form */
add_action('instapass_payment_reported', function ($order, $method_title, $reference, $date) {
	$s = ipcp_settings();
	if ( $s['enabled_push'] !== 'yes' || $s['tg_token'] === '' || $s['tg_chat'] === '' ) { return; }
	$message = "Payment reported — Order #".$order->get_order_number()."\n"
		."Total: ".wp_strip_all_tags($order->get_formatted_order_total())."\n"
		."Method: ".wp_strip_all_tags((string) $method_title)."\n"
		."Reference: ".wp_strip_all_tags((string) $reference)."\n"
		."Payment date: ".(string) $date."\n"
		.admin_url('post.php?post='.$order->get_id().'&action=edit');
	if ( ! ipcp_send_telegram($message) ) {
		$order->add_order_note('Telegram notification could not be sent. Check the Chat Proof settings.');
	}
}, 10, 4);

/* Binance app pay button on the private order payment page */
add_action('woocommerce_thankyou', function ($order_id) {
	$order = wc_get_order($order_id);
	$key = isset($_GET['key']) ? wc_clean(wp_unslash($_GET['key'])) : '';
	if ( !$order || !$key || !hash_equals($order->get_order_key(), $key) || $order->get_payment_method() !== 'instapass_binance' || !$order->has_status(array('on-hold', 'pending')) ) { return; }
	$s = ipcp_settings();
	if ( $s['binance_pay_link'] === '' ) { return; }
	echo '<section class="ip-chat-proof ip-binance-pay-section" aria-label="Pay in the Binance app">';
	echo '<p class="ip-chat-proof__lead">Fastest option: open your order payment page directly in the Binance app, enter the exact total shown above, and send. (On desktop the link shows a QR you can scan from your phone.)</p>';
	echo '<div class="ip-chat-proof__buttons">';
	echo '<a class="ip-binance-pay" href="'.esc_url($s['binance_pay_link']).'" target="_blank" rel="noopener noreferrer">Pay with Binance app <span aria-hidden="true">↗</span></a>';
	echo '</div></section>';
}, 34);

/* Customer chat buttons on the private order payment page */
add_action('woocommerce_thankyou', function ($order_id) {
	$order = wc_get_order($order_id);
	$key = isset($_GET['key']) ? wc_clean(wp_unslash($_GET['key'])) : '';
	if ( !$order || !$key || !hash_equals($order->get_order_key(), $key) || strpos($order->get_payment_method(), 'instapass_') !== 0 || !$order->has_status(array('on-hold', 'pending')) ) { return; }
	$s = ipcp_settings();
	if ( $s['enabled_buttons'] !== 'yes' ) { return; }
	$number = preg_replace('/\D/', '', $s['whatsapp']);
	if ( !$number ) { return; }
	$text = 'Payment proof for Order #'.$order->get_order_number().' ('.wp_strip_all_tags($order->get_formatted_order_total()).', '.wp_strip_all_tags($order->get_payment_method_title()).'). My transfer reference:';
	$encoded = rawurlencode($text);
	echo '<section class="ip-chat-proof" aria-label="Send proof by chat">';
	echo '<p class="ip-chat-proof__lead">Prefer chat? Send your payment proof on WhatsApp or Telegram — include your transfer reference and a screenshot of the payment.</p>';
	echo '<div class="ip-chat-proof__buttons">';
	echo '<a class="ip-chat-proof__wa" href="https://wa.me/'.esc_attr($number).'?text='.$encoded.'" target="_blank" rel="noopener noreferrer">Send proof on WhatsApp <span aria-hidden="true">↗</span></a>';
	echo '<a class="ip-chat-proof__tg" href="https://t.me/Instapass0408?text='.$encoded.'" target="_blank" rel="noopener noreferrer">Send proof on Telegram <span aria-hidden="true">↗</span></a>';
	echo '</div></section>';
}, 35);

add_action('wp_enqueue_scripts', function () {
	$css = WPMU_PLUGIN_DIR . '/instapass-chat-proof.css';
	if ( is_readable($css) ) { wp_enqueue_style('instapass-chat-proof', content_url('mu-plugins/instapass-chat-proof.css'), array(), (string) filemtime($css)); }
});
