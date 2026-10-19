<?php
if(!function_exists('instapass_promo_options')){throw new RuntimeException('Bundle code not loaded');}
foreach(array(0,1) as $slot){$c=instapass_promo_config($slot);$d=instapass_promo_defaults($slot);$existing=wc_get_coupon_id_by_code($d['code']);if($existing&&$existing!==(int)$c['coupon_id']){throw new RuntimeException('Coupon code collision. No offer has been changed.');}}
$rows=array();foreach(array(0,1) as $slot){$c=instapass_promo_config($slot);$d=instapass_promo_defaults($slot);$d['coupon_id']=$c['coupon_id'];$r=instapass_write_promo($d,$slot);if(is_wp_error($r)){throw new RuntimeException($r->get_error_message());}$coupon=new WC_Coupon($r['coupon_id']);$rows[]=array('code'=>$coupon->get_code(),'percent'=>$coupon->get_amount(),'products'=>$r['tools'],'individual_use'=>$coupon->get_individual_use(),'per_customer_limit'=>$coupon->get_usage_limit_per_user(),'first_order'=>$coupon->get_meta('_instapass_first_order_email'));}
echo wp_json_encode($rows,JSON_PRETTY_PRINT);
