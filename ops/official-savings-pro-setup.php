<?php
/** Narrow, idempotent owner-approved Pro terms and Plus-matched annual pricing. */
defined('ABSPATH')||exit;
$pro=wc_get_product(30);$plus=wc_get_product(138);
if(!$pro||$pro->get_sku('edit')!=='claude-pro'||!$plus||$plus->get_meta('_instapass_validity')!=='1 year')throw new RuntimeException('Confirmed source offers not found');
if(Instapass_Control_Money::usd_cents((string)$plus->get_regular_price('edit'))!==17333||(string)$plus->get_sale_price('edit')!==''||Instapass_Control_Money::usd_cents((string)$plus->get_meta('_instapass_pkr_regular_price'))!==4750000)throw new RuntimeException('Plus annual price changed. Review before copying.');
$existing=wc_get_product_id_by_sku('claude-pro-annual');$annual=$existing?wc_get_product($existing):null;
if($annual&&$annual->get_meta('_instapass_official_savings_release')!=='20261019-pro-annual-v1')throw new RuntimeException('Annual Pro SKU belongs to another offer');
$duration=(string)$pro->get_meta('_instapass_validity');if(!in_array($duration,['','1 month'],true))throw new RuntimeException('Monthly Pro duration changed');
$family=(string)$pro->get_meta('_instapass_tenor_family');if($family!==''&&$family!=='Claude Pro')throw new RuntimeException('Pro family changed');
$monthly_prices=[$pro->get_regular_price('edit'),$pro->get_sale_price('edit'),$pro->get_price('edit')];
if(getenv('INSTAPASS_ISOLATED_TEST')!=='yes'){
 $recovery='/tmp/pro-recovery/before.json';
 if(file_exists($recovery))throw new RuntimeException('Recovery receipt already exists; inspect before repeating');
 file_put_contents($recovery,wp_json_encode(['pro_id'=>30,'duration_present'=>$pro->meta_exists('_instapass_validity'),'duration'=>$duration,'family_present'=>$pro->meta_exists('_instapass_tenor_family'),'family'=>$family,'group_present'=>$pro->meta_exists('_instapass_tenor_group'),'group'=>$pro->get_meta('_instapass_tenor_group'),'monthly_prices'=>$monthly_prices,'annual_preexisting'=>$existing],JSON_PRETTY_PRINT));
}
$pro->update_meta_data('_instapass_validity','1 month');$pro->update_meta_data('_instapass_tenor_family','Claude Pro');$pro->update_meta_data('_instapass_tenor_group',hash('sha256','claude pro'));$pro->save();
if(!$annual){
 $annual=new WC_Product_Simple();$annual->set_name('Claude Pro — 1 Year');$annual->set_sku('claude-pro-annual');$annual->set_status('draft');$annual->set_virtual(true);$annual->set_sold_individually(false);$annual->set_reviews_allowed(true);$annual->set_regular_price($plus->get_regular_price('edit'));$annual->set_price($plus->get_price('edit'));$annual->set_stock_status($pro->get_stock_status('edit'));$annual->set_category_ids($pro->get_category_ids());
 $annual->set_description('<p>Claude Pro private access for one year, paid upfront. The monthly figure is an equivalent; no automatic monthly charge is taken. Selected Claude Pro offers include the additional usage stated in the listing. Applicable taxes are absorbed by Instapass.</p>');
 $annual->set_short_description('<p>One year of Claude Pro private access, paid upfront. Activation instructions are sent by email after payment verification.</p>');
 foreach(['_instapass_plan','_instapass_access_type','_instapass_activation','_instapass_renewable'] as $key){$value=instapass_product_features($pro)[str_replace('_instapass_','',$key)]??'';$annual->update_meta_data($key,is_bool($value)?($value?'yes':'no'):$value);}
 foreach(['_instapass_validity'=>'1 year','_instapass_tenor_family'=>'Claude Pro','_instapass_tenor_group'=>hash('sha256','claude pro'),'_instapass_brand'=>'claude','_instapass_brief'=>'Private Claude Pro access · 1 year · 25% additional usage','_ip_offer_coupons'=>'yes','_ip_account_allocation'=>'no','_ip_offer_managed'=>'yes','_instapass_official_savings_release'=>'20261019-pro-annual-v1'] as $k=>$value)$annual->update_meta_data($k,$value);
 foreach(['_instapass_pkr_regular_price','_instapass_pkr_sale_price'] as $key){$value=$plus->get_meta($key);if($value!=='')$annual->update_meta_data($key,$value);}
 $annual->save();$annual->set_status('publish');$annual->save();
}
$fresh=wc_get_product(30);if([$fresh->get_regular_price('edit'),$fresh->get_sale_price('edit'),$fresh->get_price('edit')]!==$monthly_prices)throw new RuntimeException('Monthly Pro prices changed');
echo wp_json_encode(['monthly_id'=>30,'monthly_duration'=>ip_tenor_duration($fresh),'monthly_prices_unchanged'=>true,'annual_id'=>$annual->get_id(),'annual_status'=>$annual->get_status(),'annual_usd'=>$annual->get_regular_price('edit'),'annual_pkr'=>$annual->get_meta('_instapass_pkr_regular_price'),'annual_gbp'=>$annual->get_meta('_instapass_gbp_regular_price')],JSON_PRETTY_PRINT)."\n";
