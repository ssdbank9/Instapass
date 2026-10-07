<?php
/** A short native WooCommerce checkout, with consent-based renewal contact. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
add_filter( 'woocommerce_checkout_registration_enabled', '__return_false', 100 );
add_filter( 'woocommerce_checkout_registration_required', '__return_false', 100 );
add_filter( 'wc_order_attribution_allow_tracking', '__return_false', 100 );
function instapass_digital_cart() {
 if ( ! function_exists('WC') || ! WC()->cart || WC()->cart->is_empty() ) { return false; }
 foreach ( WC()->cart->get_cart() as $item ) { if ( empty($item['data']) || ! $item['data']->is_virtual() ) { return false; } }
 return true;
}
function instapass_renewable_cart() {
 if ( ! function_exists('WC') || ! WC()->cart ) { return false; }
 foreach ( WC()->cart->get_cart() as $item ) { if ( !empty($item['data']) && instapass_product_features($item['data'])['renewable'] ) { return true; } }
 return false;
}
function instapass_checkout_fields( $fields ) {
 if ( ! instapass_digital_cart() ) { return $fields; }
 foreach ( array_keys($fields['billing']) as $key ) {
  if ( ! in_array($key,array('billing_email','billing_phone'),true) && !('billing_country'===$key && wc_tax_enabled()) ) { unset($fields['billing'][$key]); }
 }
 $fields['billing']['billing_email']['label']='Activation email';
 $fields['billing']['billing_email']['description']='Use the email you want linked to your tool. Your activation link or instructions will be sent here.';
 $fields['billing']['billing_email']['class']=array('form-row-wide'); $fields['billing']['billing_email']['priority']=10;
 $fields['billing']['billing_phone']['label']='Phone number'; $fields['billing']['billing_phone']['required']=false;
 $fields['billing']['billing_phone']['description']='Optional. Add it if you would like us to contact you about renewing eligible tools.';
 $fields['billing']['billing_phone']['class']=array('form-row-wide'); $fields['billing']['billing_phone']['priority']=20;
 unset($fields['order']['order_comments']); return $fields;
}
add_filter('woocommerce_checkout_fields','instapass_checkout_fields',100);
function instapass_guest_checkout_content($content) {
 if ( !is_admin() && function_exists('is_checkout') && is_checkout() && !is_wc_endpoint_url() && is_main_query() && in_the_loop() && has_block('woocommerce/checkout',$content) ) {
  return '<div class="ip-checkout-intro"><h2>1-click checkout</h2><p>No account or saved card. Confirm your order, choose an available transfer or Binance method, follow the payment instructions, then send the exact amount and payment reference for verification.</p><div class="ip-payment-steps"><strong>Payment process</strong><ol><li>Choose your payment method and confirm the order.</li><li>Send the exact total using the displayed bank or wallet instructions.</li><li>Reply with your transaction reference if requested; we verify the payment and email activation instructions.</li></ol></div><a href="'.esc_url(instapass_shop_url()).'">← Add another tool</a></div>' . do_shortcode('[woocommerce_checkout]');
 }
 return $content;
}
add_filter('the_content','instapass_guest_checkout_content',8);
function instapass_renewal_checkout_note() {
 if ( !instapass_renewable_cart() ) { return; }
 echo '<div class="ip-renewal-consent">';
 woocommerce_form_field('instapass_renewal_contact',array('type'=>'checkbox','required'=>false,'label'=>'Contact me to confirm renewal of my eligible tools','class'=>array('form-row-wide')),false);
 echo '<p>We will ask before each new term. You decide whether to continue and transfer the funds again using your original payment method. Nothing is automatically charged.</p></div>';
}
add_action('woocommerce_after_checkout_billing_form','instapass_renewal_checkout_note',15);
function instapass_validate_renewal_contact($data,$errors) {
 if ( instapass_renewable_cart() && !empty($_POST['instapass_renewal_contact']) && empty($data['billing_phone']) ) { $errors->add('renewal_phone','Add a phone number for renewal contact, or leave the renewal-contact box unchecked.'); }
}
add_action('woocommerce_after_checkout_validation','instapass_validate_renewal_contact',10,2);
function instapass_save_renewal_contact($order,$data) {
 $consent=instapass_renewable_cart() && !empty($_POST['instapass_renewal_contact']) && !empty($data['billing_phone']);
 $order->update_meta_data('_instapass_renewal_contact_consent',$consent?'yes':'no');
 $order->update_meta_data('_instapass_renewal_policy','Customer confirms and pays each term; no automatic charge.');
}
add_action('woocommerce_checkout_create_order','instapass_save_renewal_contact',10,2);
add_action('woocommerce_admin_order_data_after_billing_address',function($order){echo '<p><strong>Renewal contact:</strong> '.esc_html($order->get_meta('_instapass_renewal_contact_consent')==='yes'?'Customer opted in; confirm and collect payment for each new term.':'No renewal-contact consent recorded.').'</p>';});
add_filter('gettext',function($translated,$text,$domain){return $domain==='woocommerce' && $text==='Billing details' && function_exists('is_checkout') && is_checkout() && instapass_digital_cart() ? 'Activation details':$translated;},20,3);
add_action('woocommerce_before_checkout_billing_form',function(){if(instapass_digital_cart()){echo '<p class="ip-activation-help">Use the email you want linked to your tool. Your activation link or instructions will be emailed after payment verification.</p>';}});
function instapass_order_access_details($item,$key,$values,$order) {
 if ( empty($values['data']) ) { return; } $v=instapass_product_features($values['data']);
 foreach(array('access_type'=>'Access','validity'=>'Duration','activation'=>'Activation') as $field=>$label){if($v[$field]){$item->add_meta_data($label,$v[$field],true);}}
 $item->add_meta_data('_instapass_renewable',$v['renewable']?'yes':'no',true);
}
add_action('woocommerce_checkout_create_order_line_item','instapass_order_access_details',10,4);
function instapass_direct_checkout_redirect($url,$product=null) {
 if ( !empty($_REQUEST['ip_add_more']) ) { return instapass_shop_url(); }
 if ( !empty($_REQUEST['ip_buy_now']) || (function_exists('is_product') && is_product()) ) { return wc_get_checkout_url(); }
 return $url;
}
add_filter('woocommerce_add_to_cart_redirect','instapass_direct_checkout_redirect',9999,2);
add_action('woocommerce_before_add_to_cart_button',function(){echo '<input type="hidden" name="ip_buy_now" value="1">';});
add_action('wp_enqueue_scripts',function(){wp_enqueue_style('instapass-commerce',get_template_directory_uri().'/assets/commerce.css',array('instapass-product-tiles'),INSTAPASS_VERSION);});
