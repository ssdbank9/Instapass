<?php
if(!defined('WP_CLI')||!WP_CLI)throw new RuntimeException('CLI only');
$checks=0;foreach([32=>20000,149=>20000,150=>20000,31=>10000,30=>2000,29=>2000] as $id=>$expected){
 $p=wc_get_product($id);$plan=ip_offer_official_plan($p);if(!$plan||$plan['monthly']!==$expected)throw new RuntimeException('Wrong benchmark '.$id);$checks++;
 $html=ip_offer_official_savings_html($p,$plan);
 foreach(['data-official-usd-monthly="'.$expected.'"','Official US price:','We absorb applicable taxes','Additional 5% off with TOOLKIT5','2 eligible items required.'] as $needle){if(!str_contains($html,$needle))throw new RuntimeException('Missing banner fragment '.$id.' '.$needle);$checks++;}
}
foreach([32=>[8500,43],149=>[54000,45],150=>[114000,48]] as $id=>[$saving,$percent]){
 $p=wc_get_product($id);$plan=ip_offer_official_plan($p);$months=ip_tenor_months($p);$current=(int)round((float)$p->get_price('edit')*100);if($plan['monthly']*$months-$current!==$saving||ip_offer_saving_percent($saving,$plan['monthly']*$months)!==(string)$percent)throw new RuntimeException('Savings mismatch '.$id);$checks++;
}
echo wp_json_encode(['checks'=>$checks,'result'=>'pass','writes'=>false]);
