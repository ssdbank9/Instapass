<?php
/** Three independently editable bundle offers, calculated by native WooCommerce coupons. */
if(!defined('ABSPATH')){exit;}
function instapass_promo_options(){return array(0=>'instapass_promotion',2=>'instapass_promotion_three',1=>'instapass_promotion_four');}
function instapass_promo_defaults($slot=0){$offers=array(0=>array('TOOLKIT5',5,2),1=>array('TOOLKIT10',10,4),2=>array('TOOLKIT75',7.5,3));$o=$offers[$slot]??$offers[0];return array('enabled'=>true,'code'=>$o[0],'percent'=>$o[1],'mode'=>'quantity','tools'=>$o[2],'subtotal'=>50,'coupon_id'=>0,'starts_at'=>0,'ends_at'=>0,'product_ids'=>array());}
function instapass_promo_config($slot=0){$keys=instapass_promo_options();return wp_parse_args(get_option($keys[$slot],array()),instapass_promo_defaults($slot));}
function instapass_promo_fingerprint($slot){return hash('sha256',wp_json_encode(instapass_promo_config($slot)));}
function instapass_promo_qualifies($config,$count,$subtotal){
 $quantity=$count>=(int)$config['tools'];$amount=$subtotal+0.000001>=(float)$config['subtotal'];
 switch($config['mode']){case 'quantity':return $quantity;case 'subtotal':return $amount;case 'both':return $quantity&&$amount;case 'either':return $quantity||$amount;default:return false;}
}
function instapass_promo_active($c,$at=null){$at=$at??time();return !empty($c['enabled'])&&(empty($c['starts_at'])||(int)$c['starts_at']<=$at)&&(empty($c['ends_at'])||(int)$c['ends_at']>=$at);}
function instapass_promo_product_allowed($p,$c){return $p&&(!function_exists('ip_offer_coupon_allowed')||ip_offer_coupon_allowed($p))&&(empty($c['product_ids'])||in_array($p->get_id(),array_map('intval',$c['product_ids']),true)||in_array($p->get_parent_id(),array_map('intval',$c['product_ids']),true));}
function instapass_cart_tool_count($cart,$config=array()){$count=0;foreach($cart->get_cart() as $item){$p=$item['data']??null;if($p&&$p->is_purchasable()&&$p->is_in_stock()&&instapass_promo_product_allowed($p,$config))$count+=max(0,(int)$item['quantity']);}return $count;}
function instapass_promo_requirement($config){
 $quantity=sprintf('%d eligible items',(int)$config['tools']);$amount=wp_strip_all_tags(wc_price((float)$config['subtotal']));
 switch($config['mode']){case 'subtotal':return 'a basket subtotal of '.$amount.' or more';case 'both':return $quantity.' and a subtotal of '.$amount.' or more';case 'either':return $quantity.' or a subtotal of '.$amount.' or more';default:return $quantity;}
}
function instapass_write_promo($config,$slot=0){
 if(!in_array($slot,array(0,1,2),true)){return new WP_Error('slot','Unknown bundle offer.');}
 $id=absint($config['coupon_id']);$existing=wc_get_coupon_id_by_code($config['code']);
 if($existing&&$existing!==$id){return new WP_Error('collision','That code belongs to another coupon. Choose another code.');}
 $coupon=$id?new WC_Coupon($id):new WC_Coupon();
 if($id&&$coupon->get_meta('_instapass_managed_promotion')!=='yes'){return new WP_Error('not_owned','This coupon is not managed by Instapass Promotions.');}
 try{
  $coupon->set_code($config['code']);$coupon->set_discount_type('percent');$coupon->set_amount($config['percent']);
  $coupon->set_individual_use(true);$coupon->set_exclude_sale_items(false);
  $coupon->set_usage_limit(0);$coupon->set_usage_limit_per_user(0);$coupon->set_email_restrictions(array());
  $coupon->set_product_ids(array_map('absint',$config['product_ids']??array()));$coupon->set_date_expires(!empty($config['ends_at'])?(int)$config['ends_at']:null);
  $coupon->delete_meta_data('_instapass_first_order_email');
  $coupon->set_minimum_amount(in_array($config['mode'],array('subtotal','both'),true)?$config['subtotal']:0);
  $coupon->set_description('Instapass bundle offer for all customers. Eligibility: '.instapass_promo_requirement($config));
  $coupon->update_meta_data('_instapass_managed_promotion','yes');
  $id=$coupon->save();$config['coupon_id']=$id;$keys=instapass_promo_options();
  if(!update_option($keys[$slot],$config,false)&&get_option($keys[$slot])!==$config){return new WP_Error('save_failed','The coupon saved but its offer settings did not. Refresh and review before retrying.');}
  return $config;
 }catch(Exception $e){return new WP_Error('save_failed',$e->getMessage());}
}
function instapass_init_promotion(){if(current_user_can('manage_woocommerce')&&class_exists('WC_Coupon')){foreach(instapass_promo_options() as $slot=>$key){if(false===get_option($key,false)){instapass_write_promo(instapass_promo_defaults($slot),$slot);}}}}
add_action('admin_init','instapass_init_promotion',45);
function instapass_promo_menu(){if(class_exists('WooCommerce')){add_submenu_page('edit.php?post_type=product','Bundle promotions','Promotions','manage_woocommerce','instapass-promotions','instapass_promo_page');}}
add_action('admin_menu','instapass_promo_menu',31);
function instapass_promo_page(){
 if(!current_user_can('manage_woocommerce')){wp_die('Permission denied.','',array('response'=>403));}
 $notice=get_transient('instapass_promo_notice_'.get_current_user_id());if($notice){delete_transient('instapass_promo_notice_'.get_current_user_id());}
 echo '<div class="wrap"><h1>Bundle promotions</h1><p>Three offers for guests and returning customers. Each code applies to the current basket prices. Use one coupon per order; the codes do not stack.</p>';
 if($notice){echo '<div class="notice notice-'.($notice['error']?'error':'success').'"><p>'.esc_html($notice['text']).'</p></div>';}
 foreach(instapass_promo_options() as $slot=>$key){
  $c=instapass_promo_config($slot);
  echo '<section style="background:#fff;border:1px solid #ddd;padding:20px;margin:20px 0;max-width:900px"><h2>Bundle offer: '.esc_html(strtoupper($c['code'])).'</h2><form method="post" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="instapass_save_promotion"><input type="hidden" name="offer_slot" value="'.absint($slot).'"><input type="hidden" name="offer_fingerprint" value="'.esc_attr(instapass_promo_fingerprint($slot)).'">';
  wp_nonce_field('instapass_save_promotion_'.$slot);
  echo '<table class="form-table"><tr><th>Offer status</th><td><label><input type="checkbox" name="enabled" value="1" '.checked($c['enabled'],true,false).'> Enabled</label></td></tr>';
  foreach(array('code'=>'Promo code','percent'=>'Extra discount %','tools'=>'Minimum items (quantities count)','subtotal'=>'Minimum basket subtotal') as $field=>$label){
   $type=$field==='code'?'text':'number';$extra=$field==='percent'?' min="0.01" max="100" step="0.01"':($field==='tools'?' min="1" max="1000" step="1"':($field==='subtotal'?' min="0" step="0.01"':''));
   echo '<tr><th><label for="ip-promo-'.$slot.'-'.$field.'">'.esc_html($label).'</label></th><td><input id="ip-promo-'.$slot.'-'.$field.'" name="'.$field.'" type="'.$type.'" value="'.esc_attr($c[$field]).'"'.$extra.' required></td></tr>';
  }
  echo '<tr><th><label for="ip-promo-'.$slot.'-mode">Eligibility rule</label></th><td><select id="ip-promo-'.$slot.'-mode" name="mode">';
  foreach(array('quantity'=>'Number of items (including repeated quantities)','subtotal'=>'Basket subtotal','both'=>'Both requirements','either'=>'Either requirement') as $value=>$label){echo '<option value="'.esc_attr($value).'" '.selected($c['mode'],$value,false).'>'.esc_html($label).'</option>';}
  echo '</select><p class="description">Two units of the same monthly product count as two items. Only eligible, coupon-enabled items count. Subtotal means current basket prices before coupons.</p></td></tr>';
  foreach(array('starts_at'=>'Starts at','ends_at'=>'Ends at') as $field=>$label)echo '<tr><th><label for="ip-promo-'.$slot.'-'.$field.'">'.esc_html($label).'</label></th><td><input type="datetime-local" id="ip-promo-'.$slot.'-'.$field.'" name="'.$field.'" value="'.esc_attr($c[$field]?wp_date('Y-m-d\TH:i',(int)$c[$field],wp_timezone()):'').'"><p class="description">Optional; uses '.esc_html(wp_timezone_string()).'. Blank means no boundary.</p></td></tr>';
  echo '<tr><th><label for="ip-promo-'.$slot.'-products">Eligible offers</label></th><td><select multiple size="8" id="ip-promo-'.$slot.'-products" name="product_ids[]" style="width:100%;max-width:540px">';
  foreach(wc_get_products(array('limit'=>500,'status'=>array('publish','draft','private'),'orderby'=>'name','order'=>'ASC')) as $p)echo '<option value="'.absint($p->get_id()).'" '.selected(in_array($p->get_id(),array_map('intval',$c['product_ids']),true),true,false).'>'.esc_html($p->get_name()).'</option>';
  echo '</select><p class="description">Leave all unselected for any offer. Select specific offers to restrict this code; hold Ctrl/Cmd to select several. An offer with coupons disabled is always excluded.</p></td></tr></table><p>Current offer: '.esc_html($c['percent'].'% extra off with '.strtoupper($c['code']).' when buying '.instapass_promo_requirement($c)).'.</p>';
  submit_button('Save '.strtoupper($c['code']));echo '</form></section>';
 }
 echo '</div>';
}
function instapass_save_promotion(){
 if(!current_user_can('manage_woocommerce')){wp_die('Permission denied.','',array('response'=>403));}
 $slot=isset($_POST['offer_slot'])&&is_scalar($_POST['offer_slot'])?(string)$_POST['offer_slot']:'';
 if(!in_array($slot,array('0','1','2'),true)){wp_die('Unknown bundle offer.','',array('response'=>400));}
 $slot=(int)$slot;check_admin_referer('instapass_save_promotion_'.$slot);$c=instapass_promo_config($slot);$error='';
 $read=function($key){return isset($_POST[$key])&&is_scalar($_POST[$key])?wp_unslash($_POST[$key]):'';};
 $code=wc_format_coupon_code(sanitize_text_field($read('code')));$percent=$read('percent');$tools=$read('tools');$subtotal=$read('subtotal');$mode=$read('mode');
 if(!hash_equals(instapass_promo_fingerprint($slot),$read('offer_fingerprint'))){$error='This offer changed since you opened it. Refresh before saving.';}
 elseif(!preg_match('/^[a-zA-Z0-9_-]{2,32}$/',$code)){$error='Use 2–32 letters, numbers, hyphens or underscores for the code.';}
 elseif(!is_numeric($percent)||!is_finite((float)$percent)||(float)$percent<=0||(float)$percent>100){$error='Enter a discount above 0 and no greater than 100%.';}
 elseif(!ctype_digit((string)$tools)||(int)$tools<1||(int)$tools>1000){$error='Enter a product count from 1 to 1000.';}
 elseif(!is_numeric($subtotal)||!is_finite((float)$subtotal)||(float)$subtotal<0){$error='Enter a non-negative basket subtotal.';}
 elseif(!in_array($mode,array('quantity','subtotal','both','either'),true)){$error='Choose a valid eligibility rule.';}
 elseif($mode!=='quantity'&&(float)$subtotal<=0){$error='Enter a positive subtotal for a value-based rule.';}
 $dates=array();try{foreach(array('starts_at','ends_at') as $field){$raw=$read($field);$d=$raw===''?false:DateTimeImmutable::createFromFormat('!Y-m-d\TH:i',$raw,wp_timezone());if($raw!==''&&(!$d||$d->format('Y-m-d\TH:i')!==$raw))throw new Exception('Enter valid coupon start/end dates.');$dates[$field]=$d?$d->getTimestamp():0;}if($dates['starts_at']&&$dates['ends_at']&&$dates['starts_at']>$dates['ends_at'])throw new Exception('Coupon end must be after its start.');}catch(Exception $e){$error=$e->getMessage();}
 $ids=array();if(isset($_POST['product_ids'])&&!is_array($_POST['product_ids']))$error='Choose valid eligible offers.';foreach((array)($_POST['product_ids']??array()) as $pid){if(!is_scalar($pid)||!ctype_digit((string)$pid)||!wc_get_product((int)$pid)){$error='Choose valid eligible offers.';break;}$ids[]=absint($pid);}
 if(!$error){$c=array_merge($c,$dates,array('enabled'=>!empty($_POST['enabled']),'code'=>$code,'percent'=>(float)$percent,'tools'=>(int)$tools,'subtotal'=>(float)$subtotal,'mode'=>$mode,'product_ids'=>array_values(array_unique($ids))));$saved=instapass_write_promo($c,$slot);if(is_wp_error($saved)){$error=$saved->get_error_message();}}
 set_transient('instapass_promo_notice_'.get_current_user_id(),array('error'=>(bool)$error,'text'=>$error?:'Bundle offer saved.'),MINUTE_IN_SECONDS);
 wp_safe_redirect(admin_url('edit.php?post_type=product&page=instapass-promotions'));exit;
}
add_action('admin_post_instapass_save_promotion','instapass_save_promotion');
function instapass_validate_promotion($valid,$coupon){
 if(!$valid||$coupon->get_meta('_instapass_managed_promotion')!=='yes'){return $valid;}
 $config=null;foreach(instapass_promo_options() as $slot=>$key){$c=instapass_promo_config($slot);if($coupon->get_id()===(int)$c['coupon_id']){$config=$c;break;}}
 if(!$config||!instapass_promo_active($config)){throw new Exception('This promotion is not currently available.');}
 if(!WC()->cart||!instapass_promo_qualifies($config,instapass_cart_tool_count(WC()->cart,$config),(float)WC()->cart->get_subtotal())){throw new Exception('This code requires '.instapass_promo_requirement($config).' eligible for this code.');}
 return $valid;
}
add_filter('woocommerce_coupon_is_valid','instapass_validate_promotion',20,2);
add_filter('woocommerce_coupon_is_valid_for_product',function($valid,$p,$coupon){if(!$valid||$coupon->get_meta('_instapass_managed_promotion')!=='yes')return $valid;foreach(instapass_promo_options() as $slot=>$key){$c=instapass_promo_config($slot);if($coupon->get_id()===(int)$c['coupon_id'])return instapass_promo_product_allowed($p,$c);}return false;},30,3);
function instapass_promo_banner(){
 if(!function_exists('WC')||!wc_coupons_enabled()){return '';}
 $html='';foreach(instapass_promo_options() as $slot=>$key){$c=instapass_promo_config($slot);if(!instapass_promo_active($c)||!$c['coupon_id']){continue;}
  $html.='<aside class="ip-promo-note" aria-label="Basket promotion"><strong>'.esc_html($c['percent']).'% extra off</strong><span>Buy '.esc_html(instapass_promo_requirement($c)).' and use <code>'.esc_html(strtoupper($c['code'])).'</code> at checkout.'.(!empty($c['product_ids'])?' Selected offers only.':'').'</span></aside>';
 }
 if($html){$html.='<p class="ip-promo-terms">Available to guests and returning customers. Use one coupon per order.</p>';}
 return $html;
}
function instapass_archive_promo($html){return function_exists('is_shop')&&(is_shop()||is_product_category())?$html.instapass_promo_banner():$html;}
add_filter('render_block_core/query-title','instapass_archive_promo',25);
function instapass_checkout_promo(){echo instapass_promo_banner();}
add_action('woocommerce_before_checkout_form','instapass_checkout_promo',8);
add_action('woocommerce_before_cart','instapass_checkout_promo',8);
