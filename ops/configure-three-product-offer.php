<?php
defined('ABSPATH') || exit;
$before=array();foreach(array(0,1) as $slot){$c=instapass_promo_config($slot);$before[$slot]=hash('sha256',wp_json_encode(array($c,(new WC_Coupon($c['coupon_id']))->get_data())));}
$key='instapass_promotion_three';$existing=get_option($key,false);$id=wc_get_coupon_id_by_code('TOOLKIT75');
if($id&&(!$existing||$id!==(int)$existing['coupon_id'])){throw new RuntimeException('TOOLKIT75 already belongs to another coupon. No offer changed.');}
if(getenv('IP_PROMO_THREE_CAPTURE')==='yes'){echo wp_json_encode(array('new_option_before'=>$existing,'existing_offer_hashes'=>$before),JSON_PRETTY_PRINT);return;}
if(!in_array($key,instapass_promo_options(),true)){throw new RuntimeException('Three-offer code is not loaded');}
if($existing){if(strtolower($existing['code'])!=='toolkit75'||(float)$existing['percent']!==7.5||(int)$existing['tools']!==3||$existing['mode']!=='quantity'){throw new RuntimeException('New offer has custom settings; preserve and review before replacing');}}
else{$saved=instapass_write_promo(instapass_promo_defaults(2),2);if(is_wp_error($saved)){throw new RuntimeException($saved->get_error_message());}}
foreach(array(0,1) as $slot){$c=instapass_promo_config($slot);$after=hash('sha256',wp_json_encode(array($c,(new WC_Coupon($c['coupon_id']))->get_data())));if($after!==$before[$slot]){throw new RuntimeException('An existing offer changed');}}
$rows=array();foreach(instapass_promo_options() as $slot=>$key){$c=instapass_promo_config($slot);$coupon=new WC_Coupon($c['coupon_id']);$rows[]=array('slot'=>$slot,'code'=>$coupon->get_code(),'percent'=>$coupon->get_amount(),'minimum_selections'=>$c['tools'],'enabled'=>(bool)$c['enabled'],'individual_use'=>$coupon->get_individual_use(),'per_customer_limit'=>$coupon->get_usage_limit_per_user());}
echo wp_json_encode(array('offers'=>$rows,'existing_offers_preserved'=>true),JSON_PRETTY_PRINT);
