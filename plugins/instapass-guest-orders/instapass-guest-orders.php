<?php
/**
 * Plugin Name: Instapass Guest Orders
 * Description: Optional accounts, guest tracking, receipt/activation emails and bundle promotion controls.
 * Version: 0.2.1
 */
defined('ABSPATH') || exit;
define('IP_GUEST_VERSION','0.2.1');
// Custom payment notices use wc_mail directly, outside WC_Email::get_headers().
add_filter('wp_mail',function($args){
 $headers=$args['headers']??array();
 if(!is_array($headers)){$headers=preg_split('/\\r?\\n/',(string)$headers,-1,PREG_SPLIT_NO_EMPTY);}
 foreach($headers as $header){if(preg_match('/^Reply-To\\s*:/i',$header)){return $args;}}
 $headers[]='Reply-To: support@instapass.online';$args['headers']=$headers;return $args;
},90);

function ip_guest_install() {
 if(!class_exists('WooCommerce')) { return; }
 foreach(array('woocommerce_enable_guest_checkout'=>'yes','woocommerce_enable_signup_and_login_from_checkout'=>'no','woocommerce_enable_myaccount_registration'=>'yes','woocommerce_registration_generate_username'=>'yes','woocommerce_registration_generate_password'=>'yes','woocommerce_enable_coupons'=>'yes') as $key=>$value){ update_option($key,$value); }
 $page=get_page_by_path('track-order',OBJECT,'page');
 if(!$page){ $id=wp_insert_post(array('post_type'=>'page','post_status'=>'publish','post_title'=>'Track your order','post_name'=>'track-order','post_content'=>'[instapass_order_tracking]'),true); if(is_wp_error($id)){return $id;} update_option('instapass_tracking_page_id',$id); }
 elseif(has_shortcode($page->post_content,'instapass_order_tracking')){update_option('instapass_tracking_page_id',$page->ID);}
 else { return new WP_Error('tracking_collision','An existing Track Order page needs review before replacement.'); }
 return true;
}
register_activation_hook(__FILE__,'ip_guest_install');
add_action('before_woocommerce_init',function(){if(class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')){\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables',__FILE__,true);}});
add_filter('woocommerce_checkout_registration_enabled','__return_false',120);
add_filter('woocommerce_registration_auth_new_customer','__return_false',120);
add_filter('woocommerce_checkout_registration_required','__return_false',120);
add_filter('woocommerce_create_account_default_checked','__return_false',120);
add_filter('woocommerce_checkout_posted_data',function($data){if(isset($data['billing_email'])){$data['billing_email']=strtolower(trim($data['billing_email']));}return $data;});
add_filter('woocommerce_checkout_fields',function($fields){
 if(isset($fields['billing']['billing_email'])){
  $fields['billing']['billing_email']['label']='Email for receipts and order tracking';
  $fields['billing']['billing_email']['description']='Your order receipt and private payment link go here. No account is needed.';
 }
 $fields['billing']['instapass_activation_email']=array('type'=>'email','label'=>'Different activation email','required'=>false,'class'=>array('form-row-wide'),'priority'=>15,'description'=>'Leave blank to use your receipt email for activation. Enter another email only if the tool should be linked to it.','autocomplete'=>'email');
 return $fields;
},120);
function ip_guest_activation_email($order){$email=$order->get_meta('_instapass_activation_email');return is_email($email)?$email:$order->get_billing_email();}
add_action('woocommerce_checkout_create_order',function($order,$data){
 $activation=isset($data['instapass_activation_email'])?strtolower(trim($data['instapass_activation_email'])):'';
 $order->update_meta_data('_instapass_activation_email',$activation?:$order->get_billing_email());
},30,2);
add_action('woocommerce_admin_order_data_after_billing_address',function($order){echo '<p><strong>Activation email:</strong> '.esc_html(ip_guest_activation_email($order)).'<br><small>Receipts and tracking use the order billing email.</small></p>';});
add_filter('woocommerce_email_order_meta_fields',function($fields,$sent_to_admin,$order){$fields['ip_activation_email']=array('label'=>'Activation email','value'=>ip_guest_activation_email($order));return $fields;},20,3);

add_action('woocommerce_after_checkout_validation',function($data,$errors){
 $activation=$data['instapass_activation_email']??'';
 if($activation!==''&&!is_email($activation)){$errors->add('activation_email','Enter a valid activation email, or leave it blank to use your receipt email.');}
},120,2);

function ip_guest_tracking_url(){return home_url('/track-order/');}
function ip_guest_lookup_order($number,$email){
 $number=ltrim(trim((string)$number),'#');
 if(!preg_match('/^[1-9][0-9]{0,17}$/',$number)||!is_email($email)){return false;}
 $order=wc_get_order((int)$number);
 if(!$order||$order->get_type()!=='shop_order'||$order->has_status(array('trash','auto-draft','checkout-draft'))){return false;}
 return hash_equals(strtolower(trim($order->get_billing_email())),strtolower(trim($email)))?$order:false;
}
function ip_guest_rate($scope,$identity,$limit,$seconds){
 $key='ip_guest_'.hash_hmac('sha256',$scope.'|'.$identity,wp_salt('nonce'));
 $count=(int)get_transient($key);if($count>=$limit){return false;}set_transient($key,$count+1,$seconds);return true;
}
function ip_guest_status($order){
 if($order->has_status(array('cancelled','refunded','failed','completed'))){return wc_get_order_status_name($order->get_status());}
 if($order->get_meta('_ip_payment_verified_at')||$order->is_paid()){return 'Payment received — activation in progress';}
 if($order->get_meta('_ip_payment_reported_at')){return 'Payment submitted — awaiting verification';}
 return 'Awaiting payment';
}
add_action('template_redirect',function(){
 if(is_page('track-order')||is_page((int)get_option('instapass_tracking_page_id'))){nocache_headers();header('X-Robots-Tag: noindex, nofollow',true);header('Referrer-Policy: no-referrer',true);}
},1);
function ip_guest_tracking_shortcode(){
 $message='';$order=false;$number='';$email='';
 if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'&&isset($_POST['ip_track_action'])){
  $number=is_string($_POST['order_number']??null)?trim(wp_unslash($_POST['order_number'])):'';
  $email=is_string($_POST['order_email']??null)?strtolower(trim(wp_unslash($_POST['order_email']))):'';
  $nonce=is_string($_POST['_ip_track_nonce']??null)?sanitize_text_field(wp_unslash($_POST['_ip_track_nonce'])):'';
  $peer=$_SERVER['REMOTE_ADDR']??'';$identity=filter_var($peer,FILTER_VALIDATE_IP,FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE)?$peer:$email;
  if(!wp_verify_nonce($nonce,'ip_guest_track')){$message='Please refresh this page and try again.';}
  elseif(!ip_guest_rate('lookup',$identity,20,10*MINUTE_IN_SECONDS)){$message='Please wait ten minutes before trying again.';}
  else{
   $order=ip_guest_lookup_order($number,$email);
   if(!$order){$message='We could not match that order number and email. Check your order receipt and try again.';}
   elseif($_POST['ip_track_action']==='send_link'){
    if(!ip_guest_rate('private_link',(string)$order->get_id(),3,HOUR_IN_SECONDS)){$message='A private link was recently requested. Check your inbox or try again later.';}
    else{
     $body='<p>Here is the private payment and order page you requested for order #'.esc_html($order->get_order_number()).'.</p><p><a href="'.esc_url($order->get_checkout_order_received_url()).'">Open your private order page</a></p><p>Keep this link private. Payment reports are checked manually before activation.</p>';
     $sent=wc_mail($order->get_billing_email(),'Your Instapass order #'.$order->get_order_number(),$body,array('From: Instapass Orders <orders@instapass.online>','Reply-To: support@instapass.online'));
     $message=$sent?'The private order link has been sent to your receipt email. Check your inbox and spam folder.':'We could not send the link. Please contact support@instapass.online with your order number.';
    }
   }
  }
 }
 ob_start();
 echo '<section class="ip-guest-track"><h2>Track your order</h2><p>Enter the order number from your receipt and the email used at checkout. No account is needed.</p>';
 if($message){echo '<p class="ip-track-message" role="status">'.esc_html($message).'</p>';}
 echo '<form method="post" action="'.esc_url(ip_guest_tracking_url()).'">';
 wp_nonce_field('ip_guest_track','_ip_track_nonce');
 echo '<p><label for="ip-order-number">Order number</label><input id="ip-order-number" name="order_number" inputmode="numeric" required maxlength="18" value="'.esc_attr($number).'" placeholder="For example, 123"></p><p><label for="ip-order-email">Email used at checkout</label><input id="ip-order-email" name="order_email" type="email" autocomplete="email" required value="'.esc_attr($email).'"></p><button class="button" name="ip_track_action" value="track">Track order</button>';
 if($order){
  echo '<div class="ip-track-result" role="status"><h3>Order #'.esc_html($order->get_order_number()).'</h3><p><strong>'.esc_html(ip_guest_status($order)).'</strong></p><p>Total: '.wp_kses_post($order->get_formatted_order_total()).'</p><ul>';
  foreach($order->get_items() as $item){echo '<li>'.esc_html($item->get_name()).' × '.absint($item->get_quantity()).'</li>';}
  echo '</ul><p>To view payment instructions or submit a transfer reference, request your private link below.</p><button class="button" name="ip_track_action" value="send_link">Email my private order link</button></div>';
 }
 echo '</form><p>Need help? <a href="mailto:support@instapass.online">support@instapass.online</a></p></section>';
 return ob_get_clean();
}
add_shortcode('instapass_order_tracking','ip_guest_tracking_shortcode');
add_action('wp_enqueue_scripts',function(){wp_enqueue_style('instapass-guest-orders',plugins_url('guest-orders.css',__FILE__),array(),IP_GUEST_VERSION);});
add_action('wp_footer',function(){if(!is_admin()){echo '<nav class="ip-guest-links" aria-label="Orders and account"><a href="'.esc_url(ip_guest_tracking_url()).'">Track your order</a><span aria-hidden="true"> · </span><a href="'.esc_url(wc_get_page_permalink('myaccount')).'">Optional account</a></nav>';}},15);
add_action('woocommerce_before_checkout_form',function(){echo '<p class="ip-guest-note">Checkout as a guest. Promo codes work without an account. Already ordered? <a href="'.esc_url(ip_guest_tracking_url()).'">Track your order</a>.</p>';},7);
add_action('woocommerce_email_after_order_table',function($order,$admin,$plain){
 if($admin){return;}
 $url=ip_guest_tracking_url();
 if($plain){echo "\nTrack your order: ".$url."\nUse order #".$order->get_order_number()." and your receipt email. No account needed.\n";}
 else{echo '<p><a href="'.esc_url($url).'">Track your order</a> with order #'.esc_html($order->get_order_number()).' and your receipt email. No account needed.</p>';}
},30,3);
add_action('admin_menu',function(){add_submenu_page('instapass-control','Guest orders','Guest Orders','manage_woocommerce','instapass-guest-orders','ip_guest_admin');});
function ip_guest_admin(){
 if(!current_user_can('manage_woocommerce')){return;}
 echo '<div class="wrap"><h1>Guest orders and payments</h1><p>Guest checkout is enabled. Accounts are optional through My account. New customers receive an email to set their password before signing in.</p><p><a class="button" href="'.esc_url(ip_guest_tracking_url()).'" target="_blank" rel="noopener">Open order tracking</a> <a class="button" href="'.esc_url(admin_url('edit.php?post_type=product&page=instapass-promotions')).'">Manage bundle offers</a> <a class="button" href="'.esc_url(admin_url('admin.php?page=wc-settings&tab=checkout')).'">Manage payment methods</a></p><h2>Bundle promo codes</h2><p>Use TOOLKIT5 for 5% off two or more different product/tenor offers, or TOOLKIT10 for 10% off four or more different product/tenor offers in one order. Guests and returning customers qualify. Use one coupon per order; codes do not stack. Manage both offers under Products &rarr; Promotions.</p><h2>Manual payment matching</h2><p>Open the order to view the reported method, transfer reference, payment date and optional private screenshot. Verify the actual funds, amount and receiving account before choosing Confirm payment received. A customer payment report alone never marks an order paid.</p><h2>Receiving methods</h2><ul>';
 foreach(WC()->payment_gateways()->payment_gateways() as $id=>$g){if(strpos($id,'instapass_')!==0){continue;}echo '<li>'.esc_html($g->title).': '.esc_html($g->enabled==='yes'&&!empty($g->instructions)?'Configured — check accepted currencies':'Unavailable — add receiving instructions and enable when ready').'</li>';}
 echo '</ul><p>Receipts and private payment links go to the receipt email. Activation uses the optional activation email, or the receipt email when blank.</p></div>';
}