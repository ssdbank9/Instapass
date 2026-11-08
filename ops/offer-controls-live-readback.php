<?php
defined('ABSPATH')||exit;
$checks=[];$check=function($n,$ok)use(&$checks){if(!$ok)throw new RuntimeException($n);$checks[]=$n;};
$admins=get_users(['role'=>'administrator','number'=>1,'fields'=>'ID']);if(!$admins)throw new RuntimeException('No owner administrator');wp_set_current_user((int)$admins[0]);
add_filter('pre_transient_ip_offer_notice_'.get_current_user_id(),static fn()=>null);
$_GET=[];ob_start();ip_offer_page();$html=ob_get_clean();
$check('live owner offer page renders',str_contains($html,'Products &amp; Offers')||str_contains($html,'Products & Offers'));
$check('live Control Center version is 0.7.3',INSTAPASS_CONTROL_VERSION==='0.7.3');
foreach(['name','duration','brand','brand_secondary','card_brief','usd_regular','gbp_regular','sale_from','sale_to','restock_date'] as $key)$check('live owner field '.$key,str_contains($html,'id="ipo-'.$key.'"'));
$check('PKR mode, coupons and stock availability use radio groups',substr_count($html,'name="ip_offer[pkr_mode]"')>=2&&substr_count($html,'name="ip_offer[allow_coupons]"')>=2&&substr_count($html,'name="ip_offer[stock]"')>=2);
$check('status, availability and quantity use radio choices',substr_count($html,'name="ip_offer[status]"')>=3&&substr_count($html,'name="ip_offer[stock]"')>=2&&substr_count($html,'name="ip_offer[manage_stock]"')>=2);
$check('catalog has one-click status and stock forms',str_contains($html,'class="ipo-quick-state-form"')&&str_contains($html,'Deactivate')&&str_contains($html,'In stock')&&str_contains($html,'Out of stock'));
$check('monthly, six-month and annual add buttons are present',str_contains($html,'Add monthly')&&str_contains($html,'Add 6 months')&&str_contains($html,'Add annual'));
$check('catalog shows base/current prices and tile savings',str_contains($html,'Base USD')&&str_contains($html,'Current USD')&&str_contains($html,'Tile saving'));
$check('bulk and coupon controls linked',str_contains($html,'instapass-control-pricing')&&str_contains($html,'instapass-promotions'));
foreach([138=>'openai.svg',139=>'claude.svg',140=>'claude.svg'] as $id=>$logo)$check('existing duration logo '.$id,instapass_product_logo(wc_get_product($id))===$logo);
$check('USD default outside UK and independent UK GBP',instapass_cc_country_currency('PK')==='USD'&&instapass_cc_country_currency('GB')==='GBP');
echo wp_json_encode(['scope'=>'read-only live PHP render; no form submitted','pass_count'=>count($checks),'checks'=>$checks],JSON_PRETTY_PRINT)."\n";
