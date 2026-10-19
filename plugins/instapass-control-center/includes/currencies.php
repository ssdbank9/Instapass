<?php
if(!defined('ABSPATH'))exit;
function instapass_cc_front(){
 if(!class_exists('WooCommerce'))return false;
 if(defined('WP_CLI')&&WP_CLI)return getenv('INSTAPASS_ISOLATED_TEST')==='yes'&&!empty($GLOBALS['ip_cc_test_front']);
 if(defined('REST_REQUEST')&&REST_REQUEST)return (bool)preg_match('#/wc/store(?:/|$)#',$_SERVER['REQUEST_URI']??'');
 return !is_admin()||(defined('WC_DOING_AJAX')&&WC_DOING_AJAX);
}
function instapass_cc_country_currency($country){return $country==='GB'?'GBP':($country==='PK'?'PKR':'USD');}
function instapass_cc_country(){
 // Caddy supplies X-Forwarded-For; do not use visitor-supplied CF-country headers.
 $ip=trim(explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']??($_SERVER['REMOTE_ADDR']??''))[0]);
 if(!filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE))return '';
 $key='ip_cc_country_'.substr(hash('sha256',$ip),0,32);$cached=get_transient($key);if(is_string($cached))return $cached;
 $response=wp_remote_get('https://api.country.is/'.rawurlencode($ip),['timeout'=>2,'redirection'=>0]);$country='';
 if(!is_wp_error($response)&&wp_remote_retrieve_response_code($response)===200){$data=json_decode(wp_remote_retrieve_body($response),true);if(is_array($data)&&isset($data['country'])&&is_string($data['country'])&&preg_match('/^[A-Z]{2}$/D',$data['country']))$country=$data['country'];}
 set_transient($key,$country,12*HOUR_IN_SECONDS);return $country;
}
function instapass_cc_order_currency(){
 if(!did_action('wp')||!function_exists('is_wc_endpoint_url')||(!is_wc_endpoint_url('order-received')&&!is_wc_endpoint_url('order-pay')))return null;
 $id=absint(get_query_var('order-received')?:get_query_var('order-pay'));$o=$id?wc_get_order($id):false;$key=$_GET['key']??null;
 if($o&&((is_string($key)&&hash_equals($o->get_order_key(),wp_unslash($key)))||($o->get_customer_id()>0&&$o->get_customer_id()===get_current_user_id())||current_user_can('manage_woocommerce')))return $o->get_currency();return null;
}
function instapass_cc_currency(){
 if(!instapass_cc_front()||!class_exists('WooCommerce'))return 'USD';
 if(getenv('INSTAPASS_ISOLATED_TEST')==='yes'&&isset($GLOBALS['ip_cc_test_currency']))return $GLOBALS['ip_cc_test_currency'];
 $order=instapass_cc_order_currency();if($order)return $order;
 $cookie=$_COOKIE['instapass_currency']??'';if(is_string($cookie)&&in_array($cookie,['USD','GBP','PKR'],true))return $cookie;
 if(!empty($_COOKIE['woocommerce_items_in_cart']))return 'USD';
 static $auto=null;if($auto===null)$auto=instapass_cc_country_currency(instapass_cc_country());return $auto;
}
add_action('init',function(){if(!instapass_cc_front())return;if(!defined('DONOTCACHEPAGE'))define('DONOTCACHEPAGE',true);if(!headers_sent())nocache_headers();if(empty($_COOKIE['instapass_currency'])&&!headers_sent()){$c=instapass_cc_currency();wc_setcookie('instapass_currency',$c,time()+30*DAY_IN_SECONDS,true,true);$_COOKIE['instapass_currency']=$c;}},20);
function instapass_cc_amount($raw,$product,$field,$currency){
 if($currency==='USD')return $raw;
 if(!$product->is_type('simple'))return '';
 if($currency==='GBP'){
  try{$v=Instapass_Control_GBP::values((string)$product->get_meta('_instapass_gbp_regular_price'),(string)$product->get_meta('_instapass_gbp_sale_price'));}catch(InvalidArgumentException $e){return '';}
  return $field==='regular'?$v['regular']:($field==='sale'?$v['sale']:($v['regular']===''?'':($v['sale']!==''?$v['sale']:$v['regular'])));
 }
 if($currency==='PKR'&&$product->get_meta('_instapass_pkr_regular_price')!==''){
  try{$v=instapass_control_pkr_values((string)$product->get_meta('_instapass_pkr_regular_price'),(string)$product->get_meta('_instapass_pkr_sale_price'));}catch(InvalidArgumentException $e){return '';}
  return $field==='regular'?$v['regular']:($field==='sale'?$v['sale']:($v['sale']!==''?$v['sale']:$v['regular']));
 }
 if($raw==='')return '';
 try{$cents=Instapass_Control_Money::usd_cents(wc_format_decimal($raw,2));$pkr=Instapass_Control_Money::pkr_unit($cents,instapass_control_rate());return $cents>0&&$pkr===0?'':(string)$pkr;}catch(InvalidArgumentException $e){return '';}
}
foreach(['price'=>'price','regular_price'=>'regular','sale_price'=>'sale'] as $hook=>$field)add_filter('woocommerce_product_get_'.$hook,function($raw,$p)use($field){return instapass_cc_amount($raw,$p,$field,instapass_cc_currency());},100,2);
add_filter('woocommerce_currency',function($base){return instapass_cc_front()?instapass_cc_currency():$base;},100);
// Preserve ISO minor units for discounts/refunds; product-unit rounding is separate.
add_filter('wc_price_args',function($a){if(in_array($a['currency']??'', ['USD','GBP','PKR'],true))$a['decimals']=2;return $a;},100);
add_filter('woocommerce_product_is_on_sale',function($sale,$p){if(instapass_cc_currency()!=='GBP'&&!(instapass_cc_currency()==='PKR'&&$p->get_meta('_instapass_pkr_regular_price')!==''))return $sale;$r=instapass_cc_amount('',$p,'regular',instapass_cc_currency());$s=instapass_cc_amount('',$p,'sale',instapass_cc_currency());return $r!==''&&$s!==''&&(float)$s<(float)$r;},100,2);
add_filter('instapass_pkr_allow_context','__return_false',1000);
function instapass_cc_return(){return home_url(wp_parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH)?:'/');}
add_action('template_redirect',function(){
 $target=$_GET['ip_currency']??null;if(!is_string($target)||!in_array($target,['USD','GBP','PKR'],true))return;
 $invalid=false;if(WC()->cart)foreach(WC()->cart->get_cart() as $item){$p=$item['data'];if(instapass_cc_amount($p->get_price('edit'),$p,'price',$target)===''){$invalid=true;break;}}
 $url=instapass_cc_return();if($invalid){wp_safe_redirect(add_query_arg('ip_currency_unavailable','1',$url));exit;}
 wc_setcookie('instapass_currency',$target,time()+30*DAY_IN_SECONDS,true,true);wp_safe_redirect($url);exit;
},1);
function instapass_cc_usd_url(){return add_query_arg('ip_currency','USD',instapass_cc_return());}
function instapass_cc_selector(){
 if(!instapass_cc_front()||instapass_cc_order_currency())return;
 echo '<div class="ip-currency-bar"><div><strong>Prices in '.esc_html(instapass_cc_currency()).'</strong><nav aria-label="Choose currency">';
 foreach(['USD'=>'USD $','GBP'=>'GBP £','PKR'=>'PKR Rs'] as $c=>$label)echo '<a href="'.esc_url(add_query_arg('ip_currency',$c,instapass_cc_return())).'"'.(instapass_cc_currency()===$c?' aria-current="true"':'').'>'.esc_html($label).'</a>';
 echo '</nav></div>';
 if(isset($_GET['ip_currency_unavailable']))echo '<p role="status">Some basket items are not priced in that currency yet. Your basket and currency have been kept.</p>';
 echo '</div>';
}
add_action('wp_body_open','instapass_cc_selector',5);
add_action('wp_enqueue_scripts',function(){wp_enqueue_style('instapass-currency',plugins_url('../assets/currency.css',__FILE__),[],INSTAPASS_CONTROL_VERSION);});
function instapass_cc_missing_html($html,$p){if(instapass_cc_currency()==='GBP'&&$p->get_price()==='')return '<span class="ip-gbp-unpriced">GBP price coming soon. <a href="'.esc_url(instapass_cc_usd_url()).'">View USD prices</a></span>';return $html;}
add_filter('woocommerce_get_price_html','instapass_cc_missing_html',100,2);
add_filter('render_block_woocommerce/product-price',function($html,$parsed,$block){if(function_exists('instapass_block_product')){$p=instapass_block_product($block);if($p)$html=instapass_cc_missing_html($html,$p);}return $html;},100,3);
add_filter('woocommerce_cart_hash',function($hash){return hash('sha256',$hash.'|'.instapass_cc_currency().'|'.(instapass_cc_currency()==='PKR'?instapass_cc_rate():'independent'));},100);
add_filter('woocommerce_coupon_is_valid',function($valid,$coupon){if(instapass_cc_currency()!=='USD'&&!$coupon->is_type('percent'))throw new Exception('This fixed-amount coupon uses USD. Select USD to use it.');return $valid;},100,2);
add_action('woocommerce_check_cart_items',function(){if(instapass_cc_currency()==='USD'||!WC()->cart)return;foreach(WC()->cart->get_cart() as $i)if(!$i['data']->is_virtual())wc_add_notice('This item currently supports USD checkout only. Select USD to continue.','error');if(WC()->cart->get_fees())wc_add_notice('Additional fees currently require USD checkout. Select USD to continue.','error');});
// Payment availability follows currencies actually configured by the owner.
function instapass_cc_gateway_currencies($id){if(in_array($id,['instapass_crypto','instapass_binance'],true))return ['USD'];if(in_array($id,['instapass_easypaisa','instapass_nayapay'],true))return ['PKR'];return ['USD','GBP','PKR'];}
foreach(['instapass_transfer','instapass_crypto','instapass_binance','instapass_easypaisa','instapass_nayapay'] as $id)add_filter('woocommerce_settings_api_form_fields_'.$id,function($fields)use($id){$currencies=instapass_cc_gateway_currencies($id);$options=array_combine($currencies,$currencies);$fields['instapass_currencies']=['title'=>'Accepted currencies','type'=>'multiselect','options'=>$options,'default'=>[$currencies[0]],'description'=>'Enable a currency only when these receiving instructions support payment in that currency. GBP prices are independent; crypto methods currently support USD/USDT only.'];return $fields;});
add_filter('woocommerce_available_payment_gateways',function($methods){if(!instapass_cc_front())return $methods;$c=instapass_cc_currency();foreach($methods as $id=>$g){$allowed=instapass_cc_gateway_currencies($id);$selected=$g->get_option('instapass_currencies',[$allowed[0]]);if(!is_array($selected)||!in_array($c,array_intersect($allowed,$selected),true))unset($methods[$id]);}return $methods;},200);
add_filter('woocommerce_no_available_payment_methods_message',function($text){return instapass_cc_currency()==='USD'?$text:'Payments in '.esc_html(instapass_cc_currency()).' are not configured yet. <a href="'.esc_url(instapass_cc_usd_url()).'">Switch to USD</a> to see USD payment options.';},200);
function instapass_cc_snapshot($order){
 if(!$order->get_meta('_instapass_currency_snapshot')){$c=instapass_cc_currency();$order->set_currency($c);$order->update_meta_data('_instapass_currency_snapshot',['currency'=>$c,'pkr_per_usd'=>$c==='PKR'?instapass_cc_rate():null,'policy'=>Instapass_Control_Money::POLICY,'at_utc'=>gmdate('c')]);}
 $gateways=WC()->payment_gateways()->payment_gateways();$id=$order->get_payment_method();if(!$order->get_meta('_instapass_payment_instructions_snapshot')&&isset($gateways[$id])&&property_exists($gateways[$id],'instructions'))$order->update_meta_data('_instapass_payment_instructions_snapshot',['title'=>$gateways[$id]->title,'instructions'=>$gateways[$id]->instructions]);
}
add_action('woocommerce_checkout_create_order','instapass_cc_snapshot',90);
add_action('woocommerce_store_api_checkout_update_order_from_request','instapass_cc_snapshot',90);
// Render saved instructions for new orders; historical orders retain legacy behavior.
function instapass_cc_use_saved_instructions($order){if(!$order)return;$s=$order->get_meta('_instapass_payment_instructions_snapshot');$id=$order->get_payment_method();$g=WC()->payment_gateways()->payment_gateways()[$id]??null;if($g&&is_array($s)&&isset($s['instructions'],$s['title'])){$GLOBALS['ip_cc_gateway_restore']=[$g,$g->instructions,$g->title];$g->instructions=$s['instructions'];$g->title=$s['title'];}}
function instapass_cc_restore_instructions(){if(isset($GLOBALS['ip_cc_gateway_restore'])){[$g,$i,$t]=$GLOBALS['ip_cc_gateway_restore'];$g->instructions=$i;$g->title=$t;unset($GLOBALS['ip_cc_gateway_restore']);}}
foreach(['instapass_transfer','instapass_crypto','instapass_binance','instapass_easypaisa','instapass_nayapay'] as $gateway_id){add_action('woocommerce_thankyou_'.$gateway_id,function($id){instapass_cc_use_saved_instructions(wc_get_order($id));},1);add_action('woocommerce_thankyou_'.$gateway_id,'instapass_cc_restore_instructions',999);}
add_action('woocommerce_email_before_order_table',function($o){instapass_cc_use_saved_instructions($o);},1);
add_action('woocommerce_email_before_order_table','instapass_cc_restore_instructions',999);

add_filter('woocommerce_catalog_orderby',function($options){if(instapass_cc_currency()!=='USD')unset($options['price'],$options['price-desc']);return $options;});

/** Keep every calculation in one request on the same rate. */
function instapass_cc_rate(){if(!instapass_cc_front())return instapass_control_rate();if(!isset($GLOBALS['ip_cc_rate_snapshot']))$GLOBALS['ip_cc_rate_snapshot']=instapass_control_rate();return $GLOBALS['ip_cc_rate_snapshot'];}
function instapass_cc_quote_signature(){return hash_hmac('sha256',(WC()->cart?WC()->cart->get_cart_hash():'').'|'.instapass_cc_currency().'|'.(instapass_cc_currency()==='PKR'?instapass_cc_rate():'independent'),wp_salt('nonce'));}
add_action('woocommerce_review_order_before_submit',function(){echo '<input type="hidden" name="instapass_quote_signature" value="'.esc_attr(instapass_cc_quote_signature()).'">';});
add_action('woocommerce_after_checkout_validation',function($data,$errors){$sent=$_POST['instapass_quote_signature']??null;if(!is_string($sent)||!hash_equals(instapass_cc_quote_signature(),$sent)){$errors->add('instapass_quote_changed','Prices or currency changed. Please review the refreshed total and place your order again.');if(WC()->session)WC()->session->set('refresh_totals',true);}},1000,2);
// The current website uses classic checkout. Do not silently accept an unreviewed direct API purchase.
add_action('woocommerce_store_api_checkout_update_order_from_request',function($order,$request){if($request->get_method()==='POST'&&instapass_cc_currency()!=='USD')throw new \Automattic\WooCommerce\StoreApi\Exceptions\RouteException('instapass_checkout_review','Use the website checkout to review this currency and payment amount.',400);},1,2);

add_filter('wc_price',function($html,$formatted,$args,$amount){$currency=($args['currency']??'')?:get_woocommerce_currency();if($currency==='PKR'&&is_numeric($amount)&&(float)$amount===floor((float)$amount))$html=str_replace(($args['decimal_separator']??wc_get_price_decimal_separator()).'00','',$html);return $html;},100,4);