<?php
defined('ABSPATH')||exit;
$ipMax=wc_get_product(140);
if(!$ipMax||$ipMax->get_sku()!=='instapass-claude-max-5x-annual'||!$ipMax->is_type('simple')||$ipMax->get_status()!=='publish'||$ipMax->get_meta('_instapass_price_release')!=='20261019-approved-terms-v1'||!in_array((float)$ipMax->get_regular_price('edit'),[833.33,840.0],true)||$ipMax->get_sale_price('edit')!=='')throw new RuntimeException('Annual Max identity/price changed; stop before writing');
$ipState=static function(){
 $catalog=[];foreach(wc_get_products(['limit'=>-1,'status'=>['publish','draft','pending','private'],'orderby'=>'ID','order'=>'ASC']) as $p)if($p->get_id()!==140)$catalog[]=$p->get_data();
 $orders=[];foreach(wc_get_orders(['limit'=>-1,'orderby'=>'ID','order'=>'ASC']) as $o)$orders[]=[$o->get_id(),$o->get_status(),$o->get_currency(),$o->get_total(),$o->get_customer_id(),$o->get_transaction_id()];
 $options=[];foreach(['instapass_pkr_rate','instapass_promotion','instapass_promotion_three','instapass_promotion_four','woocommerce_calc_taxes','woocommerce_prices_include_tax','woocommerce_tax_display_shop','woocommerce_tax_display_cart','woocommerce_enable_guest_checkout','woocommerce_enable_myaccount_registration'] as $k)$options[$k]=get_option($k);
 $gate=[];foreach(WC()->payment_gateways()->payment_gateways() as $id=>$g)$gate[$id]=$g->settings;
 $coupons=[];foreach(get_posts(['post_type'=>'shop_coupon','numberposts'=>-1,'orderby'=>'ID','order'=>'ASC']) as $c)$coupons[]=(new WC_Coupon($c->ID))->get_data();
 return ['other_catalog_count'=>count($catalog),'other_catalog_hash'=>hash('sha256',wp_json_encode($catalog)),'orders_count'=>count($orders),'orders_hash'=>hash('sha256',wp_json_encode($orders)),'options_hash'=>hash('sha256',wp_json_encode($options)),'gateways_hash'=>hash('sha256',wp_json_encode($gate)),'coupons_hash'=>hash('sha256',wp_json_encode($coupons))];
};
$ipBefore=['regular'=>$ipMax->get_regular_price('edit'),'sale'=>$ipMax->get_sale_price('edit'),'price'=>$ipMax->get_price('edit'),'description'=>$ipMax->get_description(),'short_description'=>$ipMax->get_short_description(),'pkr_override'=>$ipMax->get_meta('_instapass_pkr_regular_price'),'gbp_regular'=>$ipMax->get_meta('_instapass_gbp_regular_price'),'gbp_sale'=>$ipMax->get_meta('_instapass_gbp_sale_price'),'stock'=>$ipMax->get_stock_status()];
$ipProtected=$ipState();$ipApply=getenv('IP_MAX70_MODE')==='apply';
if($ipApply){
 $replace=static fn($text)=>str_replace(['$833.33','$750','$62.50'],['$840','$756','$63'],$text);
 $ipDesc=$replace($ipBefore['description']);$ipShort=$replace($ipBefore['short_description']);
 if((float)$ipBefore['price']!==840.0||$ipDesc!==$ipBefore['description']||$ipShort!==$ipBefore['short_description']){
  $ipMax->set_regular_price('840.00');$ipMax->set_price('840.00');$ipMax->set_description($ipDesc);$ipMax->set_short_description($ipShort);$ipMax->save();
 }
 $ipFresh=wc_get_product(140);if((float)$ipFresh->get_price('edit')!==840.0||$ipFresh->get_meta('_instapass_pkr_regular_price')!==$ipBefore['pkr_override']||$ipFresh->get_meta('_instapass_gbp_regular_price')!==$ipBefore['gbp_regular']||$ipFresh->get_meta('_instapass_gbp_sale_price')!==$ipBefore['gbp_sale']||$ipFresh->get_stock_status()!==$ipBefore['stock'])throw new RuntimeException('Scoped save readback failed');
 if($ipState()!==$ipProtected)throw new RuntimeException('Unrelated catalog/orders/settings changed during operation; inspect audit');
}
echo wp_json_encode(['mode'=>$ipApply?'apply':'capture','product_id'=>140,'before'=>$ipBefore,'after_usd'=>wc_get_product(140)->get_price('edit'),'protected'=>$ipProtected,'protected_after'=>$ipApply?$ipState():null,'scope'=>'Only annual Max USD price and its public price copy; no stock/GBP/PKR override/historical order changes'],JSON_PRETTY_PRINT);
