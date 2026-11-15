<?php
defined('ABSPATH')||exit;
$gs=WC()->payment_gateways()->payment_gateways();
$wanted=array('instapass_wise','instapass_paypal','instapass_easypaisa','instapass_binance','instapass_crypto','instapass_transfer');
$out=array('registered'=>true,'methods'=>array(),'available_at_checkout'=>array());
foreach($wanted as $id){
  $out['methods'][$id]=isset($gs[$id])?array('title'=>$gs[$id]->title,'has_fields'=>$gs[$id]->has_fields??null):'MISSING';
}
echo wp_json_encode($out);
// check the stored settings keys after code deployment
foreach(array('instapass_wise','instapass_paypal') as $id){
  $s=get_option('woocommerce_'.$id.'_settings',array());
  echo wp_json_encode(array('id'=>$id,'settings'=>$s===false?'missing':$s));
}
