<?php
if(getenv('INSTAPASS_ISOLATED_TEST')!=='yes')throw new RuntimeException('Isolated database only');
add_filter('pre_wp_mail','__return_true',999);add_filter('pre_http_request',static fn()=>new WP_Error('isolated','No external HTTP'),999);
$GLOBALS['ip_cc_test_front']=false;
putenv('IP_MAX70_MODE=apply');ob_start();require '/tmp/max70.php';$deltaResult=json_decode(ob_get_clean(),true);
if(($deltaResult['protected']??null)!==($deltaResult['protected_after']??null)||(float)wc_get_product(140)->get_price('edit')!==840.0)throw new RuntimeException('Scoped price apply/protection failed');
fwrite(STDERR,"PASS scoped annual840 apply and protected financial state\n");
$copy=wc_get_product(140)->get_short_description();if(strpos($copy,'$840')===false||strpos($copy,'$756')===false||strpos($copy,'$63')===false||strpos($copy,'$833.33')!==false)throw new RuntimeException('Stale public price copy');
fwrite(STDERR,"PASS public annual copy matches840/756/63\n");
require '/tmp/monthly.php';
$extra=2;$GLOBALS['ip_cc_test_front']=true;$GLOBALS['ip_cc_test_currency']='USD';
foreach([['code'=>'toolkit5','ids'=>[140,31],'target'=>798.0],['code'=>'toolkit75','ids'=>[140,31,139],'target'=>777.0],['code'=>'toolkit10','ids'=>[140,31,139,138],'target'=>756.0]] as $case){
 WC()->cart->empty_cart();wc_clear_notices();foreach($case['ids'] as $pid)WC()->cart->add_to_cart($pid);if(!WC()->cart->apply_coupon($case['code']))throw new RuntimeException('Coupon rejected:'.$case['code']);WC()->cart->calculate_totals();
 $line=null;foreach(WC()->cart->get_cart() as $row)if($row['product_id']===140)$line=$row;
 if(!$line||abs((float)$line['line_total']-$case['target'])>.011)throw new RuntimeException('Annual native coupon mismatch:'.$case['code']);
 fwrite(STDERR,'PASS annual native '.$case['code'].' reaches '.$case['target']."\n");$extra++;
}
echo "\n".wp_json_encode(['max70_total_checks'=>count($checks)+$extra,'scope'=>'isolated clone only, no real order/payment/email'],JSON_PRETTY_PRINT);
