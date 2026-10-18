<?php
global $wpdb;
$product_rows=$wpdb->get_results("SELECT ID,post_status,post_title FROM {$wpdb->posts} WHERE post_type IN ('product','product_variation') ORDER BY ID",ARRAY_A);
$meta=$wpdb->get_results("SELECT post_id,meta_key,meta_value FROM {$wpdb->postmeta} WHERE meta_key IN ('_regular_price','_sale_price','_price','_stock','_stock_status','_instapass_gbp_regular_price','_instapass_gbp_sale_price') ORDER BY post_id,meta_key,meta_id",ARRAY_A);
$orders=wc_get_orders(['limit'=>-1,'orderby'=>'ID','order'=>'ASC']);
$order_data=[];foreach($orders as $o)$order_data[]=$o->get_data();
$s=get_option('instapass_notifications',[]);
$installed=class_exists('Instapass_Notifications');
$result=['product_count'=>count($product_rows),'product_fingerprint'=>hash('sha256',serialize([$product_rows,$meta])),'order_count'=>count($orders),'order_fingerprint'=>hash('sha256',serialize($order_data)),'installed'=>$installed,'enabled'=>$installed?Instapass_Notifications::ready():false,'settings_enabled'=>!empty($s['enabled']),'verified'=>!empty($s['verified'])];
if($installed){$result['subscriptions']=(int)$wpdb->get_var('SELECT COUNT(*) FROM '.Instapass_Notifications::table());$result['action_scheduler']=function_exists('as_schedule_single_action');}
echo wp_json_encode($result,JSON_PRETTY_PRINT)."\n";
