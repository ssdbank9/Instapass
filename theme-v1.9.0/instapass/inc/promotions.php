<?php
/** One editable offer, using WooCommerce's native coupon calculation. */
if(!defined('ABSPATH')){exit;}
function instapass_promo_defaults(){return array('enabled'=>true,'code'=>'TOOLKIT5','percent'=>5,'mode'=>'quantity','tools'=>2,'subtotal'=>50,'coupon_id'=>0);}
function instapass_promo_config(){return wp_parse_args(get_option('instapass_promotion',array()),instapass_promo_defaults());}
function instapass_promo_qualifies($config,$count,$subtotal){
 $quantity=$count>=(int)$config['tools'];$amount=$subtotal+0.000001>=(float)$config['subtotal'];
 switch($config['mode']){case 'quantity':return $quantity;case 'subtotal':return $amount;case 'both':return $quantity&&$amount;case 'either':return $quantity||$amount;default:return false;}
}
function instapass_cart_tool_count($cart){$ids=array();foreach($cart->get_cart() as $item){$p=$item['data']??null;if($p&&$p->is_purchasable()&&$p->is_in_stock()&&(int)$item['quantity']>0){$ids[$item['product_id']]=true;}}return count($ids);}
function instapass_promo_requirement($config){
 $quantity=sprintf('%d different products',(int)$config['tools']);$amount=wp_strip_all_tags(wc_price((float)$config['subtotal']));
 switch($config['mode']){case 'subtotal':return 'a basket subtotal of '.$amount.' or more';case 'both':return $quantity.' and a subtotal of '.$amount.' or more';case 'either':return $quantity.' or a subtotal of '.$amount.' or more';default:return $quantity;}
}
function instapass_write_promo($config){
 $id=absint($config['coupon_id']);$existing=wc_get_coupon_id_by_code($config['code']);
 if($existing&&$existing!==$id){return new WP_Error('collision','That code belongs to another coupon. Choose another code.');}
 $coupon=$id?new WC_Coupon($id):new WC_Coupon();
 if($id&&$coupon->get_meta('_instapass_managed_promotion')!=='yes'){return new WP_Error('not_owned','This coupon is not managed by Instapass Promotions.');}
 $coupon->set_code($config['code']);$coupon->set_discount_type('percent');$coupon->set_amount($config['percent']);$coupon->set_individual_use(true);$coupon->set_exclude_sale_items(false);
 $coupon->set_minimum_amount(in_array($config['mode'],array('subtotal','both'),true)?$config['subtotal']:0);
 $coupon->set_description('Instapass configurable offer. Eligibility: '.instapass_promo_requirement($config));$coupon->update_meta_data('_instapass_managed_promotion','yes');
 try{$id=$coupon->save();$config['coupon_id']=$id;update_option('instapass_promotion',$config,false);return $config;}catch(Exception $e){return new WP_Error('save_failed',$e->getMessage());}
}
function instapass_init_promotion(){if(current_user_can('manage_woocommerce')&&class_exists('WC_Coupon')&&false===get_option('instapass_promotion',false)){instapass_write_promo(instapass_promo_defaults());}}
add_action('admin_init','instapass_init_promotion',45);
function instapass_promo_menu(){if(class_exists('WooCommerce')){add_submenu_page('edit.php?post_type=product','Promotions','Promotions','manage_woocommerce','instapass-promotions','instapass_promo_page');}}
add_action('admin_menu','instapass_promo_menu',31);
function instapass_promo_page(){
 if(!current_user_can('manage_woocommerce')){wp_die('Permission denied.');}$c=instapass_promo_config();$notice=get_transient('instapass_promo_notice_'.get_current_user_id());if($notice){delete_transient('instapass_promo_notice_'.get_current_user_id());}
 echo '<div class="wrap"><h1>Promotions</h1><p>Create one extra percentage discount with a promo code. Choose the eligibility rule; WooCommerce calculates the discount from current basket prices. This code cannot combine with other coupons.</p>';
 if($notice){echo '<div class="notice notice-'.($notice['error']?'error':'success').'"><p>'.esc_html($notice['text']).'</p></div>';}
 echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="instapass_save_promotion">';wp_nonce_field('instapass_save_promotion');
 echo '<table class="form-table"><tr><th>Offer status</th><td><label><input type="checkbox" name="enabled" value="1" '.checked($c['enabled'],true,false).'> Enabled</label></td></tr>';
 foreach(array('code'=>'Promo code','percent'=>'Extra discount %','tools'=>'Minimum different products','subtotal'=>'Minimum basket subtotal') as $key=>$label){$type=$key==='code'?'text':'number';$extra=$key==='percent'?' min="0.01" max="100" step="0.01"':($key==='tools'?' min="1" max="1000" step="1"':($key==='subtotal'?' min="0" step="0.01" maxlength="20"':''));echo '<tr><th><label for="ip-promo-'.$key.'">'.esc_html($label).'</label></th><td><input id="ip-promo-'.$key.'" name="'.$key.'" type="'.$type.'" value="'.esc_attr($c[$key]).'"'.$extra.' required></td></tr>';}
 echo '<tr><th><label for="ip-promo-mode">Eligibility rule</label></th><td><select id="ip-promo-mode" name="mode">';foreach(array('quantity'=>'Number of different products','subtotal'=>'Basket subtotal','both'=>'Both requirements','either'=>'Either requirement') as $key=>$label){echo '<option value="'.esc_attr($key).'" '.selected($c['mode'],$key,false).'>'.esc_html($label).'</option>';}echo '</select><p class="description">Subtotal is before tax and coupon discounts, using current regular or sale prices. Multiple copies of the same product count once. Two variations of one parent also count once.</p></td></tr></table><p>Current offer: '.esc_html($c['percent'].'% extra off with '.$c['code'].' when buying '.instapass_promo_requirement($c)).'.</p>';
 submit_button('Save promotion');echo '</form></div>';
}
function instapass_save_promotion(){
 if(!current_user_can('manage_woocommerce')){wp_die('Permission denied.','',array('response'=>403));}check_admin_referer('instapass_save_promotion');$c=instapass_promo_config();$error='';
 $read=function($key){return isset($_POST[$key])&&is_scalar($_POST[$key])?wp_unslash($_POST[$key]):'';};
 $code=wc_format_coupon_code(sanitize_text_field($read('code')));$percent=$read('percent');$tools=$read('tools');$subtotal=$read('subtotal');$mode=$read('mode');
 if(!preg_match('/^[a-zA-Z0-9_-]{2,32}$/',$code)){$error='Use 2–32 letters, numbers, hyphens or underscores for the code.';}
 elseif(!is_numeric($percent)||!is_finite((float)$percent)||(float)$percent<=0||(float)$percent>100){$error='Enter a discount above 0 and no greater than 100%.';}
 elseif(!ctype_digit((string)$tools)||(int)$tools<1||(int)$tools>1000){$error='Enter a product count from 1 to 1000.';}
 elseif(!is_numeric($subtotal)||!is_finite((float)$subtotal)||(float)$subtotal<0){$error='Enter a non-negative basket subtotal.';}
 elseif(!in_array($mode,array('quantity','subtotal','both','either'),true)){$error='Choose a valid eligibility rule.';}
 elseif($mode!=='quantity'&&(float)$subtotal<=0){$error='Enter a positive subtotal for a value-based rule.';}
 if(!$error){$c=array_merge($c,array('enabled'=>!empty($_POST['enabled']),'code'=>$code,'percent'=>(float)$percent,'tools'=>(int)$tools,'subtotal'=>(float)$subtotal,'mode'=>$mode));$saved=instapass_write_promo($c);if(is_wp_error($saved)){$error=$saved->get_error_message();}}
 set_transient('instapass_promo_notice_'.get_current_user_id(),array('error'=>(bool)$error,'text'=>$error?:'Promotion saved.'),MINUTE_IN_SECONDS);wp_safe_redirect(admin_url('edit.php?post_type=product&page=instapass-promotions'));exit;
}
add_action('admin_post_instapass_save_promotion','instapass_save_promotion');
function instapass_validate_promotion($valid,$coupon){
 if($coupon->get_meta('_instapass_managed_promotion')!=='yes'){return $valid;}$c=instapass_promo_config();
 if(!$c['enabled']||$coupon->get_id()!==(int)$c['coupon_id']){throw new Exception('This promotion is not currently available.');}
 if(!WC()->cart||!instapass_promo_qualifies($c,instapass_cart_tool_count(WC()->cart),(float)WC()->cart->get_subtotal())){throw new Exception('This code requires '.instapass_promo_requirement($c).'.');}return $valid;
}
add_filter('woocommerce_coupon_is_valid','instapass_validate_promotion',20,2);
function instapass_promo_banner(){
 if(!function_exists('WC')||!wc_coupons_enabled()){return '';}$c=instapass_promo_config();if(!$c['enabled']||!$c['coupon_id']){return '';}
 return '<aside class="ip-promo-note" aria-label="Basket promotion"><strong>'.esc_html($c['percent']).'% extra off</strong><span>Buy '.esc_html(instapass_promo_requirement($c)).' and use <code>'.esc_html(strtoupper($c['code'])).'</code> at checkout.</span></aside>';
}
function instapass_archive_promo($html){return function_exists('is_shop')&&(is_shop()||is_product_category())?$html.instapass_promo_banner():$html;}
add_filter('render_block_core/query-title','instapass_archive_promo',25);
function instapass_checkout_promo(){echo instapass_promo_banner();}
add_action('woocommerce_before_checkout_form','instapass_checkout_promo',8);
add_action('woocommerce_before_cart','instapass_checkout_promo',8);
