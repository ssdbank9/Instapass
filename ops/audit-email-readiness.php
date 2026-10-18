<?php
if(!defined('WP_CLI')||!WP_CLI)throw new RuntimeException('Read-only CLI audit');
require_once ABSPATH.'wp-admin/includes/plugin.php';
$plugins=[];foreach(get_plugins() as $file=>$data)if(stripos($file,'smtp')!==false||strpos($file,'woocommerce/woocommerce.php')!==false)$plugins[]=['file'=>$file,'version'=>$data['Version'],'active'=>is_plugin_active($file)];
$settings=get_option('fluentmail-settings',[]);if(is_string($settings))$settings=json_decode($settings,true)?:[];
$connections=is_array($settings)?($settings['connections']??[]):[];
$providers=[];foreach((array)$connections as $connection){if(!is_array($connection))continue;$providers[]=(string)($connection['provider']??$connection['settings']['provider']??'unknown');}
$out=['time'=>gmdate('c'),'scope'=>'configuration booleans only; no email send or settings save','plugins'=>$plugins,'fluent_settings_present'=>!empty($settings),'connection_count'=>count((array)$connections),'provider_ids'=>$providers,'sendmail_binary_present'=>file_exists('/usr/sbin/sendmail'),'wp_cron_disabled'=>defined('DISABLE_WP_CRON')&&DISABLE_WP_CRON,'action_scheduler_available'=>function_exists('as_schedule_single_action'),'store_timezone'=>wp_timezone_string(),'out_of_stock_products'=>count(wc_get_products(['limit'=>-1,'stock_status'=>'outofstock','status'=>'publish']))];
echo wp_json_encode($out,JSON_PRETTY_PRINT);
