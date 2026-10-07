<?php
/** Private, order-key protected manual payment reporting and administrator verification. */
if (!defined('ABSPATH')) { exit; }

function ip_payment_methods() {
 $result = array();
 foreach (WC()->payment_gateways()->payment_gateways() as $id => $gateway) {
  if (strpos($id, 'instapass_') === 0 && $gateway->enabled === 'yes' && !empty($gateway->instructions)) { $result[$id] = $gateway; }
 }
 return $result;
}
function ip_payment_url($order) { return $order->get_checkout_order_received_url(); }
function ip_payment_error($message) { wp_die(esc_html($message), 'Payment submission', array('response'=>400,'back_link'=>true)); }
function ip_payment_reference_key($method, $reference) {
 // Use a global claim so changing the dropdown cannot reuse a transaction.
 return 'ip_payment_ref_' . hash('sha256', strtolower(preg_replace('/[\s\-]+/', '', $reference)));
}
add_action('woocommerce_thankyou', 'ip_payment_customer_panel', 30);
function ip_payment_customer_panel($order_id) {
 $order = wc_get_order($order_id);
 $key = isset($_GET['key']) ? wc_clean(wp_unslash($_GET['key'])) : '';
 if (!$order || !$key || !hash_equals($order->get_order_key(), $key) || strpos($order->get_payment_method(), 'instapass_') !== 0) { return; }
 echo '<section class="ip-payment-report" style="max-width:680px;margin:24px auto;padding:24px;border:1px solid #ddd;border-radius:16px">';
 if ($order->get_meta('_ip_payment_verified_at')) {
  echo '<h2>'.($order->has_status('completed') ? 'Order completed' : 'Payment verified — activation in progress').'</h2><p>Your payment has been verified. Activation updates will be sent to your email.</p>';
 } elseif ($order->get_meta('_ip_payment_reported_at')) {
  echo '<h2>Payment submitted — awaiting verification</h2><p>We have recorded your payment details for order #'.esc_html($order->get_order_number()).'. You will receive an email when payment is verified.</p><a href="'.esc_url(ip_payment_url($order)).'">Refresh order status</a>';
 } elseif ($order->has_status(array('on-hold','pending'))) {
  $methods = ip_payment_methods();
  echo '<h2>Confirm your transfer</h2><p>After sending payment, enter its reference below. A screenshot is optional.</p><form method="post" enctype="multipart/form-data" action="'.esc_url(admin_url('admin-post.php')).'">';
  echo '<input type="hidden" name="action" value="ip_payment_report"><input type="hidden" name="order_id" value="'.absint($order_id).'"><input type="hidden" name="order_key" value="'.esc_attr($key).'">';
  wp_nonce_field('ip_payment_report_'.$order_id);
  echo '<p><label>Payment method<br><select name="payment_method" required style="width:100%;padding:12px">';
  foreach ($methods as $id=>$gateway) { echo '<option value="'.esc_attr($id).'" '.selected($id,$order->get_payment_method(),false).'>'.esc_html($gateway->title).'</option>'; }
  echo '</select></label></p><p><label>Payment date<br><input type="date" name="payment_date" required value="'.esc_attr(wp_date('Y-m-d',null,new DateTimeZone('Asia/Karachi'))).'" style="width:100%;padding:12px"></label></p><p><label>Transaction reference / transaction hash<br><input name="payment_reference" required maxlength="150" autocomplete="off" style="width:100%;padding:12px"></label></p><p><label>Payment screenshot (optional, JPG or PNG, up to 2 MB)<br><input type="file" name="payment_screenshot" accept="image/jpeg,image/png"></label></p><button type="submit" class="button">I have sent the payment</button></form>';
 }
 echo '</section>';
}
add_action('admin_post_ip_payment_report','ip_payment_report');
add_action('admin_post_nopriv_ip_payment_report','ip_payment_report');
function ip_payment_report() {
 $id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
 $order = wc_get_order($id);
 $key = isset($_POST['order_key']) ? wc_clean(wp_unslash($_POST['order_key'])) : '';
 if (!$order || !$key || !hash_equals($order->get_order_key(),$key) || !wp_verify_nonce(isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '', 'ip_payment_report_'.$id)) { ip_payment_error('This order link is invalid or expired. Reopen the link in your order email.'); }
 if (strpos($order->get_payment_method(),'instapass_') !== 0 || !$order->has_status(array('on-hold','pending'))) { ip_payment_error('This order is not awaiting manual payment.'); }
 $method = isset($_POST['payment_method']) ? sanitize_key($_POST['payment_method']) : '';
 $methods = ip_payment_methods();
 $reference = isset($_POST['payment_reference']) ? sanitize_text_field(wp_unslash($_POST['payment_reference'])) : '';
 $date = isset($_POST['payment_date']) ? sanitize_text_field(wp_unslash($_POST['payment_date'])) : '';
 $parsed = DateTimeImmutable::createFromFormat('!Y-m-d',$date,new DateTimeZone('Asia/Karachi'));
 if (!isset($methods[$method]) || !preg_match('/^[A-Za-z0-9][A-Za-z0-9\s\-]{3,149}$/',$reference) || !$parsed || $parsed->format('Y-m-d') !== $date || $date > wp_date('Y-m-d',null,new DateTimeZone('Asia/Karachi'))) { ip_payment_error('Choose an available payment method, a valid payment date, and a transaction reference of 4–150 letters or numbers.'); }
 $screenshot = '';
 $mime = '';
 if (!empty($_FILES['payment_screenshot']['name'])) {
  $file = $_FILES['payment_screenshot'];
  if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 2*1024*1024 || !is_uploaded_file($file['tmp_name'])) { ip_payment_error('Upload a JPG or PNG screenshot up to 2 MB.'); }
  $info = wp_getimagesize($file['tmp_name']);
  if (!$info || !in_array($info['mime'],array('image/jpeg','image/png'),true)) { ip_payment_error('Only a valid JPG or PNG image is accepted.'); }
  $mime = $info['mime'];
  $screenshot = base64_encode(file_get_contents($file['tmp_name']));
 }
 // A database unique option name serializes claims, including concurrent requests.
 $claim = ip_payment_reference_key($method,$reference);
 if (!add_option($claim,(string)$id,'','no') && (string)get_option($claim) !== (string)$id) { ip_payment_error('This transaction reference is already linked to another order. Check your reference or contact support.'); }
 // A per-order claim also prevents concurrent submissions from overwriting evidence.
 if (!add_option('ip_payment_submitted_'.$id,$claim,'','no')) {
  if (get_option('ip_payment_submitted_'.$id) !== $claim) { ip_payment_error('Payment details have already been submitted for this order. Contact support if a correction is needed.'); }
  wp_safe_redirect(ip_payment_url($order)); exit;
 }
 $order->update_meta_data('_ip_payment_reference',$reference);
 $order->update_meta_data('_ip_payment_method',$method);
 $order->update_meta_data('_ip_payment_date',$date);
 $order->update_meta_data('_ip_payment_reported_at',gmdate('c'));
 if ($screenshot) { $order->update_meta_data('_ip_payment_screenshot',$screenshot); $order->update_meta_data('_ip_payment_screenshot_mime',$mime); }
 $order->save();
 $order->add_order_note('Customer reports payment sent via '.$methods[$method]->title.'. Reference: '.$reference.'. Payment date: '.$date.'. Awaiting receipt verification.');
 $recipient = get_option('woocommerce_new_order_settings',array());
 $recipient = !empty($recipient['recipient']) ? $recipient['recipient'] : get_option('admin_email');
 $sent = wc_mail($recipient,'Payment reported — Order #'.$order->get_order_number(), '<p>Please verify receipt in your receiving account.</p><p>Customer: '.esc_html($order->get_billing_email()).'<br>Amount: '.wp_kses_post($order->get_formatted_order_total()).'<br>Method: '.esc_html($methods[$method]->title).'<br>Payment date: '.esc_html($date).'<br>Reference: '.esc_html($reference).'</p><p><a href="'.esc_url($order->get_edit_order_url()).'">Review order</a></p>');
 if (!$sent) { $order->add_order_note('Payment-report notification could not be sent. Check email configuration.'); }
 wp_safe_redirect(ip_payment_url($order)); exit;
}
add_action('woocommerce_admin_order_data_after_order_details','ip_payment_admin_panel');
function ip_payment_admin_panel($order) {
 if (strpos($order->get_payment_method(),'instapass_') === 0) { echo '<p style="clear:both"><a target="_blank" rel="noopener" href="'.esc_url(ip_payment_url($order)).'">Open private customer payment/status page</a></p>'; }
 if (!$order->get_meta('_ip_payment_reported_at')) { return; }
 $methods=WC()->payment_gateways()->payment_gateways(); $method=$order->get_meta('_ip_payment_method');
 echo '<div style="clear:both;padding-top:16px"><h3>Reported payment</h3><p>Method: '.esc_html(isset($methods[$method]) ? $methods[$method]->title : $method).'<br>Checkout method: '.esc_html($order->get_payment_method_title()).'<br>Payment date: '.esc_html($order->get_meta('_ip_payment_date')).'<br>Reference: <strong>'.esc_html($order->get_meta('_ip_payment_reference')).'</strong><br>Submitted: '.esc_html($order->get_meta('_ip_payment_reported_at')).'</p>';
 if ($order->get_meta('_ip_payment_screenshot')) { echo '<p><a target="_blank" rel="noopener" href="'.esc_url(wp_nonce_url(admin_url('admin-post.php?action=ip_payment_screenshot&order_id='.$order->get_id()),'ip_payment_screenshot_'.$order->get_id())).'">View private payment screenshot</a></p>'; }
 if ($order->get_meta('_ip_payment_verified_at')) { echo '<p>Verified: '.esc_html($order->get_meta('_ip_payment_verified_at')).'</p>'; }
 elseif ($order->has_status(array('on-hold','pending'))) { echo '<p>Check the reference, amount and destination in your receiving account before confirming.</p><a class="button button-primary" href="'.esc_url(wp_nonce_url(admin_url('admin-post.php?action=ip_payment_verify&order_id='.$order->get_id()),'ip_payment_verify_'.$order->get_id())).'">Confirm payment received</a>'; }
 echo '</div>';
}
add_action('admin_post_ip_payment_screenshot',function(){
 $id=isset($_GET['order_id']) ? absint($_GET['order_id']) : 0;
 if (!current_user_can('manage_woocommerce')) { wp_die('Access denied.',403); }
 check_admin_referer('ip_payment_screenshot_'.$id);
 $order=wc_get_order($id);
 if (!$order || !$order->get_meta('_ip_payment_screenshot')) { wp_die('Screenshot unavailable.'); }
 nocache_headers(); header('Content-Type: '.$order->get_meta('_ip_payment_screenshot_mime')); header('X-Content-Type-Options: nosniff'); echo base64_decode($order->get_meta('_ip_payment_screenshot')); exit;
});
add_action('admin_post_ip_payment_verify',function(){
 $id=isset($_GET['order_id']) ? absint($_GET['order_id']) : 0;
 if (!current_user_can('manage_woocommerce')) { wp_die('Access denied.',403); }
 check_admin_referer('ip_payment_verify_'.$id);
 $order=wc_get_order($id);
 if (!$order || !$order->get_meta('_ip_payment_reported_at') || !$order->has_status(array('on-hold','pending'))) { wp_die('Order is not awaiting verification.'); }
 if (!add_option('ip_payment_verified_'.$id,(string)get_current_user_id(),'','no')) { wp_safe_redirect($order->get_edit_order_url()); exit; }
 $order->update_meta_data('_ip_payment_verified_at',gmdate('c'));
 $order->update_meta_data('_ip_payment_verified_by',get_current_user_id());
 $order->save();
 $processing = function($status,$payment_order_id) use ($id) { return (int)$payment_order_id === $id ? 'processing' : $status; };
 add_filter('woocommerce_payment_complete_order_status',$processing,100,2);
 $order->payment_complete($order->get_meta('_ip_payment_reference'));
 remove_filter('woocommerce_payment_complete_order_status',$processing,100);
 $order->add_order_note('Payment receipt manually verified by administrator.');
 $sent=wc_mail($order->get_billing_email(),'Payment verified — Order #'.$order->get_order_number(),'<p>We have received and verified your payment. Your activation link or instructions will be sent to this email address.</p><p><a href="'.esc_url(ip_payment_url($order)).'">View your order</a></p>');
 if (!$sent) { $order->add_order_note('Payment-verification email could not be sent. Check email configuration.'); }
 wp_safe_redirect($order->get_edit_order_url()); exit;
});
