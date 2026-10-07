<?php
/** Manual payment instructions. Orders are never marked paid automatically. */
if ( ! defined('ABSPATH') ) { exit; }
function instapass_render_payment_instructions($instructions) {
 $lines = preg_split('/\r\n|\r|\n/', (string)$instructions);
 $rendered = array();
 foreach ($lines as $line) {
  if (preg_match('/^\s*QR\s*code\s*:\s*(https?:\/\/\S+)\s*$/i', $line, $matches)) {
   $url = esc_url_raw($matches[1], array('http','https'));
   if ($url && wp_http_validate_url($url) && preg_match('/\.(?:jpe?g|png|gif|webp)(?:[?#].*)?$/i', $url)) {
    $rendered[] = '<div class="ip-payment-qr"><span class="ip-payment-qr__label">Scan this QR code to pay</span><img class="ip-payment-qr__image" src="'.esc_url($url).'" alt="Payment QR code" loading="lazy" decoding="async"></div>';
    continue;
   }
  }
  $rendered[] = make_clickable(esc_html($line));
 }
 return wp_kses_post(implode('<br>', $rendered));
}
function instapass_manual_gateways($gateways) {
 if ( !class_exists('WC_Payment_Gateway') ) { return $gateways; }
 if ( !class_exists('Instapass_Manual_Gateway') ) {
  class Instapass_Manual_Gateway extends WC_Payment_Gateway {
   public $instructions='';
   public function __construct($id,$name) {
    $this->id=$id; $this->has_fields=false; $this->method_title=$name; $this->method_description='Manual payment. Add receiving instructions before enabling this method. No card or saved-payment details are collected.';
    $this->supports=array('products');
    $this->form_fields=array(
     'enabled'=>array('title'=>'Enable','type'=>'checkbox','label'=>'Enable this manual payment method','default'=>'no'),
     'title'=>array('title'=>'Checkout title','type'=>'text','default'=>$name),
     'description'=>array('title'=>'Checkout description','type'=>'textarea','default'=>'Confirm your order to see payment instructions. Access is activated after we verify your payment.'),
     'instructions'=>array('title'=>'Receiving instructions','type'=>'textarea','description'=>'Enter your actual receiving details. For Binance or crypto, specify the payment route, currency and exact network where applicable. Never enter a password, API key or recovery phrase. These instructions are shown to the customer after ordering.','default'=>'')
    );
    $this->init_settings(); $this->enabled=$this->get_option('enabled','no'); $this->title=$this->get_option('title',$name); $this->description=$this->get_option('description'); $this->instructions=$this->get_option('instructions');
    add_action('woocommerce_update_options_payment_gateways_'.$this->id,array($this,'process_admin_options'));
    add_action('woocommerce_thankyou_'.$this->id,array($this,'thankyou_instructions'));
    add_action('woocommerce_email_before_order_table',array($this,'email_instructions'),10,4);
   }
   public function is_available() { return trim($this->instructions)!=='' && parent::is_available(); }
   public function process_payment($order_id) {
    $order=wc_get_order($order_id); if(!$order || !$this->is_available()){wc_add_notice('This payment method is not configured. Please contact support.','error');return array('result'=>'failure');}
    if($order->get_total()>0){$order->update_status('on-hold','Awaiting manual '.$this->title.' payment confirmation.');}else{$order->payment_complete();}
    if(WC()->cart && $order->has_cart_hash(WC()->cart->get_cart_hash())){WC()->cart->empty_cart();}
    return array('result'=>'success','redirect'=>$this->get_return_url($order));
   }
   public function thankyou_instructions($order_id){$order=wc_get_order($order_id);if($order && $order->get_payment_method()===$this->id && $order->has_status(array('on-hold','pending'))){$instructions=instapass_render_payment_instructions($this->instructions);echo '<section class="ip-payment-instructions"><h2>'.esc_html($this->title).' instructions</h2><div class="ip-payment-instructions__body">'.$instructions.'</div><p><strong>Payment reference: order #'.esc_html($order->get_order_number()).'</strong><br>Your order is awaiting payment verification. Include this order number with your transfer reference.</p></section>';}}
   public function email_instructions($order,$sent_to_admin,$plain_text,$email){if($sent_to_admin || $order->get_payment_method()!==$this->id || !$order->has_status(array('on-hold','pending'))){return;}if($plain_text){echo "\n".wp_strip_all_tags($this->title)." instructions\n".wp_strip_all_tags($this->instructions)."\nPayment reference: order #".$order->get_order_number()."\nYour order is awaiting payment verification.\n";}else{$this->thankyou_instructions($order->get_id());}}
  }
  // Keep the original transfer gateway ID so existing settings remain intact.
  class Instapass_Transfer_Gateway extends Instapass_Manual_Gateway {public function __construct(){parent::__construct('instapass_transfer','Bank transfer');}}
  class Instapass_Crypto_Gateway extends Instapass_Manual_Gateway {public function __construct(){parent::__construct('instapass_crypto','Direct crypto wallet');}}
  class Instapass_Binance_Gateway extends Instapass_Manual_Gateway {public function __construct(){parent::__construct('instapass_binance','Binance user-to-user');}}
  class Instapass_Easypaisa_Gateway extends Instapass_Manual_Gateway {public function __construct(){parent::__construct('instapass_easypaisa','EasyPaisa');}}
  class Instapass_NayaPay_Gateway extends Instapass_Manual_Gateway {public function __construct(){parent::__construct('instapass_nayapay','NayaPay');}}
 }
 $gateways[]='Instapass_Transfer_Gateway'; $gateways[]='Instapass_Crypto_Gateway'; $gateways[]='Instapass_Binance_Gateway'; $gateways[]='Instapass_Easypaisa_Gateway'; $gateways[]='Instapass_NayaPay_Gateway'; return $gateways;
}
add_filter('woocommerce_payment_gateways','instapass_manual_gateways');
// Restrict checkout to the owner's manual methods; each stays hidden until configured.
add_filter('woocommerce_available_payment_gateways',function($methods){return array_intersect_key($methods,array_flip(array('instapass_transfer','instapass_crypto','instapass_binance','instapass_easypaisa','instapass_nayapay')));},100);
add_filter('woocommerce_no_available_payment_methods_message',function($text){return 'Bank transfer, direct crypto wallet, Binance user-to-user, EasyPaisa and NayaPay are not available yet. <a href="'.esc_url(home_url('/contact/')).'">Contact us</a> to confirm payment instructions.';});
