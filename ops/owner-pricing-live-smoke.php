<?php
defined('ABSPATH')||exit;
wp_set_current_user(1);$checks=[];$c=function($name,$ok)use(&$checks){if(!$ok)throw new RuntimeException($name);$checks[]=$name;};
$c('Control Center0.6.0 loaded',INSTAPASS_CONTROL_VERSION==='0.6.0');$catalog=Instapass_Control_Bulk::catalog();$c('catalog read succeeds',!is_wp_error($catalog)&&count($catalog['rows'])===88);$rows=array_column($catalog['rows'],null,'id');
$c('annual Max USD840 and automatic PKR235200',$rows[140]['usd']['regular']==='840.00'&&$rows[140]['pkr']['regular']==='235200'&&$rows[140]['pkr_mode']==='Auto');
$c('Plus fixed PKR47500 preserved',$rows[138]['pkr_mode']==='Fixed'&&(float)$rows[138]['pkr']['regular']===47500.0);
$c('GBP remains independently unpriced',$rows[140]['gbp']['regular']==='');
ob_start();Instapass_Control_Bulk::page();$html=ob_get_clean();
$c('owner page exposes USD PKR GBP and restore auto',strpos($html,'value="PKR"')!==false&&strpos($html,'value="USD"')!==false&&strpos($html,'value="GBP"')!==false&&strpos($html,'ipcb-pkr-auto')!==false);
$c('USD generated99 option remains optional',strpos($html,'id="ipcb-round99" type="checkbox">')!==false&&strpos($html,'checked disabled')===false);
$c('owner Excel instructions include PKR mode',strpos($html,'PKR mode auto')!==false);
require_once ABSPATH.'wp-admin/includes/template.php';require_once WC_ABSPATH.'includes/admin/wc-meta-box-functions.php';$GLOBALS['product_object']=wc_get_product(140);ob_start();instapass_control_pkr_fields();$fields=ob_get_clean();
$c('native editor has fixed PKR controls without obsolete USD restriction',strpos($fields,'_instapass_pkr_regular_price')!==false&&strpos($fields,'Apply PKR discount')!==false&&strpos($fields,'Allow exact USD prices')===false);
ob_start();instapass_control_rate_panel();$rate=ob_get_clean();$c('rate control explains fixed PKR independence',strpos($rate,'Fixed PKR prices stay as you entered them')!==false&&instapass_control_rate()==='280.00');
echo wp_json_encode(['count'=>count($checks),'checks'=>$checks,'scope'=>'live owner rendering/catalog readback; no pricing batch/order/payment changes'],JSON_PRETTY_PRINT);
