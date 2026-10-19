<?php
defined('ABSPATH')||exit;
$excluded=['instapass-chatgpt-plus-annual','instapass-claude-max-5x-6months','instapass-claude-max-5x-annual'];$protected=[];
foreach(wc_get_products(['limit'=>-1,'status'=>['publish','draft','pending','private'],'orderby'=>'ID','order'=>'ASC']) as $p){if(in_array($p->get_id(),[29,31],true)||in_array($p->get_sku(),$excluded,true))continue;$protected[]=$p->get_data();}
$orders=[];foreach(wc_get_orders(['limit'=>-1,'orderby'=>'ID','order'=>'ASC']) as $o)$orders[]=[$o->get_id(),$o->get_status(),$o->get_currency(),$o->get_total(),$o->get_customer_id(),$o->get_transaction_id()];
$protectedOptions=[];foreach(['instapass_pkr_rate','instapass_promotion','instapass_promotion_three','instapass_promotion_four'] as $k)$protectedOptions[$k]=get_option($k);
$gate=[];foreach(WC()->payment_gateways()->payment_gateways() as $id=>$g)$gate[$id]=$g->settings;
$coupons=[];foreach(get_posts(['post_type'=>'shop_coupon','numberposts'=>-1,'orderby'=>'ID','order'=>'ASC']) as $c)$coupons[]=(new WC_Coupon($c->ID))->get_data();
echo wp_json_encode(['protected_catalog_hash'=>hash('sha256',wp_json_encode($protected)),'protected_count'=>count($protected),'orders_hash'=>hash('sha256',wp_json_encode($orders)),'order_count'=>count($orders),'options_hash'=>hash('sha256',wp_json_encode($protectedOptions)),'gateways_hash'=>hash('sha256',wp_json_encode($gate)),'coupons_hash'=>hash('sha256',wp_json_encode($coupons))],JSON_PRETTY_PRINT);
