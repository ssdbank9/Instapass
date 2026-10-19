<?php
defined('ABSPATH')||exit;
$catalog=[];foreach(wc_get_products(['limit'=>-1,'status'=>['publish','draft','pending','private','future'],'orderby'=>'ID','order'=>'ASC']) as $p)$catalog[]=$p->get_data();
$orders=[];foreach(wc_get_orders(['limit'=>-1,'orderby'=>'ID','order'=>'ASC']) as $o)$orders[]=$o->get_data();
$options=[];foreach(['instapass_pkr_rate','instapass_promotion','instapass_promotion_three','instapass_promotion_four','woocommerce_calc_taxes','woocommerce_prices_include_tax','woocommerce_tax_display_shop','woocommerce_tax_display_cart','woocommerce_enable_guest_checkout','woocommerce_enable_myaccount_registration'] as $key)$options[$key]=get_option($key);
$gate=[];foreach(WC()->payment_gateways()->payment_gateways() as $id=>$g)$gate[$id]=$g->settings;
$coupons=[];foreach(get_posts(['post_type'=>'shop_coupon','numberposts'=>-1,'orderby'=>'ID','order'=>'ASC']) as $c)$coupons[]=(new WC_Coupon($c->ID))->get_data();
echo wp_json_encode(['catalog_count'=>count($catalog),'catalog_hash'=>hash('sha256',wp_json_encode($catalog)),'orders_count'=>count($orders),'orders_hash'=>hash('sha256',wp_json_encode($orders)),'settings_hash'=>hash('sha256',wp_json_encode($options)),'gateways_hash'=>hash('sha256',wp_json_encode($gate)),'coupons_hash'=>hash('sha256',wp_json_encode($coupons))],JSON_PRETTY_PRINT);
