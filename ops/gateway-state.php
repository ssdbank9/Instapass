<?php
defined('ABSPATH')||exit;
$out=array();
foreach(array('instapass_easypaisa','instapass_transfer','instapass_binance') as $id){
  $s=get_option('woocommerce_'.$id.'_settings',array());
  $out[$id]=array(
    'enabled'=>isset($s['enabled'])?$s['enabled']:null,
    'title'=>isset($s['title'])?$s['title']:null,
    'instructions_len'=>isset($s['instructions'])?strlen($s['instructions']):0,
    'instructions_set'=>!empty($s['instructions']),
    'currencies'=>isset($s['instapass_currencies'])?$s['instapass_currencies']:null
  );
}
echo wp_json_encode($out,JSON_PRETTY_PRINT)."\n";
