<?php
if(!defined('ABSPATH'))exit;
function instapass_control_validate_rate($raw){
 if(!is_string($raw)||!preg_match('/^(0|[1-9][0-9]{0,5})(?:\.([0-9]{1,2}))?$/D',trim($raw),$m))throw new InvalidArgumentException('Enter a positive rate with at most two decimal places.');
 $minor=(int)$m[1]*100+(int)str_pad($m[2]??'',2,'0');if($minor<=0)throw new InvalidArgumentException('The rate must be greater than zero.');
 return intdiv($minor,100).'.'.str_pad((string)($minor%100),2,'0',STR_PAD_LEFT);
}
function instapass_control_rate(){try{return instapass_control_validate_rate((string)get_option('instapass_pkr_rate','280'));}catch(InvalidArgumentException $e){return '280.00';}}
function instapass_control_rate_fingerprint(){return hash('sha256',(string)get_option('instapass_pkr_rate',''));}
function instapass_control_rate_panel(){
 $notice=get_transient('instapass_rate_notice_'.get_current_user_id());if($notice)delete_transient('instapass_rate_notice_'.get_current_user_id());
 echo '<section class="ipc-card" id="currency-settings"><h2>USD to PKR rate</h2><p>Set the number of rupees per US dollar. Automatic PKR product prices round to the nearest Rs 100 per unit. Fixed PKR prices stay as you entered them and do not change with the rate. Existing orders keep their original amounts and rate.</p>';
 if($notice)echo '<div class="notice notice-success inline"><p>'.esc_html($notice).'</p></div>';
 echo '<form class="ipc-preview" method="post" action="'.esc_url(admin_url('admin-post.php')).'">';
 wp_nonce_field('instapass_save_rate');echo '<input type="hidden" name="action" value="instapass_save_rate"><input type="hidden" name="rate_fingerprint" value="'.esc_attr(instapass_control_rate_fingerprint()).'">';
 echo '<label for="ipc-pkr-rate">PKR per $1 USD<input id="ipc-pkr-rate" name="pkr_rate" type="number" min="0.01" max="999999.99" step="0.01" value="'.esc_attr(instapass_control_rate()).'" required></label><button class="button button-primary">Save exchange rate</button></form><p class="ipc-muted">GBP uses the separate prices you enter on each product. It is never converted from USD.</p></section>';
}
function instapass_control_save_rate(){
 if(!current_user_can('manage_woocommerce'))wp_die('Permission denied.','',['response'=>403]);check_admin_referer('instapass_save_rate');
 try{$rate=instapass_control_validate_rate(isset($_POST['pkr_rate'])&&is_string($_POST['pkr_rate'])?wp_unslash($_POST['pkr_rate']):'');}catch(InvalidArgumentException $e){wp_die(esc_html($e->getMessage()),'Invalid exchange rate',['response'=>400]);}
 $fp=$_POST['rate_fingerprint']??null;if(!is_string($fp)||!hash_equals(instapass_control_rate_fingerprint(),$fp))wp_die('The exchange rate changed in another session. Reload the dashboard before saving.','Rate changed',['response'=>409]);
 $previous=instapass_control_rate();update_option('instapass_pkr_rate',$rate);
 $history=get_option('instapass_rate_history',[]);if(!is_array($history))$history=[];$history[]=['previous'=>$previous,'next'=>$rate,'changed_by'=>get_current_user_id(),'at_utc'=>gmdate('c')];update_option('instapass_rate_history',array_slice($history,-30),false);
 set_transient('instapass_rate_notice_'.get_current_user_id(),'Exchange rate saved. Automatic PKR prices now use Rs '.$rate.' per USD.',60);wp_safe_redirect(admin_url('admin.php?page=instapass-control#currency-settings'));exit;
}
add_action('admin_post_instapass_save_rate','instapass_control_save_rate');
// One owner control, rather than retaining a second legacy estimate switch.
add_filter('woocommerce_general_settings',function($rows){return array_values(array_filter($rows,function($r){return !in_array($r['id']??'', ['instapass_pkr_options','instapass_pkr_rate','instapass_pkr_show'],true);}));},100);
