<?php
if(getenv('INSTAPASS_ISOLATED_TEST')!=='yes')throw new RuntimeException('Isolated clone required');
update_option('home','http://127.0.0.1:19063');update_option('siteurl','http://127.0.0.1:19063');
// Run the same narrow setup approved by the owner: Pro1month and annual matching Plus.
ob_start();require '/tmp/pro-setup.php';$setup=json_decode(ob_get_clean(),true);
$GLOBALS['ips_checks']=[];function ips_check($n,$ok){if(!$ok)throw new RuntimeException('FAIL '.$n);$GLOBALS['ips_checks'][]=$n;}
function ips_text($html){return html_entity_decode(wp_strip_all_tags($html),ENT_QUOTES,'UTF-8');}
$original=[];foreach(wc_get_products(['limit'=>-1,'status'=>['publish','draft','private','pending'],'orderby'=>'ID','order'=>'ASC']) as $p)$original[$p->get_id()]=hash('sha256',wp_json_encode($p->get_data()));
$GLOBALS['ip_cc_test_front']=true;$GLOBALS['ip_cc_test_currency']='USD';
foreach([[31,10000,25,33],[30,2000,55,60],[29,2000,25,33],[138,2000,28,35],[139,10000,28,35],[140,10000,30,37],[$setup['annual_id'],2000,28,35]] as [$id,$official,$saving,$bundle]){
 $p=wc_get_product($id);$plan=ip_offer_official_plan($p);ips_check('confirmed plan benchmark '.$id,$plan&&$plan['monthly']===$official);$h=$p->get_price_html();$text=wp_strip_all_tags($h);
 ips_check('official source and tax displayed '.$id,str_contains($h,'Official US price:')&&str_contains($h,'before applicable tax')&&str_contains($h,'We absorb applicable taxes'));
 ips_check('savings precede price '.$id,str_starts_with($h,'<span class="ip-offer-savings'));
 ips_check('whole savings percentages '.$id,!preg_match('/\([0-9]+\.[0-9]+%\)/',$text));
 if($saving!==null)ips_check('current official saving '.$id,str_contains($text,'('.$saving.'%)'));
 if($bundle!==null)ips_check('coupon official total saving '.$id,str_contains($text,'('.$bundle.'%) total'));
}
ips_check('Max20x does not use100USD benchmark',ip_offer_official_plan(wc_get_product(32))===null);
ips_check('owner confirmed Pro monthly keeps8.99sale',wc_get_product(30)->get_sale_price('edit')==='8.99'&&ip_tenor_duration(wc_get_product(30))==='1 month');
ips_check('Pro annual matches Plus USD and fixedPKR',$setup['annual_status']==='publish'&&$setup['annual_usd']==='173.33'&&(float)$setup['annual_pkr']===47500.0&&$setup['annual_gbp']==='');
ips_check('round half upward',ip_offer_saving_percent(3250,10000)==='33');ips_check('round fractional down',ip_offer_saving_percent(6667,24000)==='28');
$p=wc_get_product(31);$view=ip_tenor_price_view($p);$rates=ip_tenor_bundle_rates($p,$view);ips_check('7.5percent coupon remains exact',in_array(7.5,array_column($rates,'percent'),true));
$GLOBALS['ip_cc_test_currency']='PKR';$h=wc_get_product(31)->get_price_html();ips_check('PKR current saving uses current rate',preg_match('/7,000(?:\.00)? \(25%\)/',wp_strip_all_tags($h))&&str_contains($h,'PKR comparison uses Rs 280.00 per USD'));
$GLOBALS['ip_cc_test_currency']='GBP';$h=wc_get_product(31)->get_price_html();ips_check('GBP keeps official USD reference without invented percent',str_contains($h,'Official US price:')&&!str_contains($h,'ip-offer-saving-now')&&!str_contains($h,'ip-offer-saving-total')&&str_contains($h,'independent GBP offer price'));
$GLOBALS['ip_cc_test_currency']='USD';
$user='saving-fixture-'.wp_generate_password(8,false,false);$password=wp_generate_password(28,true,true);$uid=wp_create_user($user,$password,$user.'@example.invalid');if(is_wp_error($uid))throw new RuntimeException('Fixture user failed');(new WP_User($uid))->set_role('administrator');wp_set_current_user($uid);
$v=ip_offer_read(wc_get_product(140));$v['id']=0;$v['fingerprint']='new';$v['sku']='QA-SAVINGS-OWNER-ANNUAL';$v['name']='QA Owner Max Annual';$v['usd_regular']='900';$v['usd_sale']='';$v['gbp_regular']='700';$v['gbp_sale']='';$v['pkr_mode']='fixed';$v['pkr_regular']='240000';$v['pkr_sale']='';$v['allocation']='no';$id=ip_offer_save($v);$p=wc_get_product($id);
ips_check('owner-created linked tenor inherits confirmed benchmark',ip_offer_official_plan($p)['monthly']===10000);
ips_check('owner-entered USD price recalculates saving',str_contains(ips_text($p->get_price_html()),'$300.00 (25%)'));
$GLOBALS['ip_cc_test_currency']='PKR';ips_check('independent fixed PKR computes against converted official baseline',(bool)preg_match('/96,000(?:\.00)? \(29%\)/',wp_strip_all_tags(wc_get_product($id)->get_price_html())));
update_option('instapass_pkr_rate','300');ips_check('owner rate edit refreshes PKR benchmark',(bool)preg_match('/120,000(?:\.00)? \(33%\)/',wp_strip_all_tags(wc_get_product($id)->get_price_html())));
$GLOBALS['ip_cc_test_currency']='GBP';$h=wc_get_product($id)->get_price_html();ips_check('owner independent GBP price remains700',str_contains(ips_text($h),'£700.00 paid upfront')&&!str_contains($h,'ip-offer-saving-now'));
update_option('instapass_pkr_rate','280');$GLOBALS['ip_cc_test_currency']='USD';
$p=wc_get_product($id);$p->set_regular_price('1300');$p->set_price('1300');$h=$p->get_price_html();ips_check('no current saving when above official total',!str_contains($h,'ip-offer-saving-now'));
$p->set_price('');$h=ip_offer_savings_html($p);ips_check('unpriced offer retains reference without free-price claim',str_contains($h,'Official US price')&&!str_contains($h,'ip-offer-saving-now'));
$v=ip_offer_read();$v=array_merge($v,['name'=>'QA Unrelated Offer','sku'=>'QA-SAVINGS-UNRELATED','family'=>'Unrelated','duration'=>'1 month','status'=>'publish','usd_regular'=>'20','usd_sale'=>'18']);$other=ip_offer_save($v);ips_check('unrelated offers keep regular-price comparison',str_contains(wc_get_product($other)->get_price_html(),'vs regular price')&&ip_offer_official_plan(wc_get_product($other))===null);
$v=ip_offer_read(wc_get_product($other));$v['usd_regular']='3';$v['usd_sale']='2.8';ip_offer_save($v);ips_check('other saving percentages also round to7',str_contains(wc_get_product($other)->get_price_html(),'(7%)'));
foreach($original as $pid=>$hash)if(hash('sha256',wp_json_encode(wc_get_product($pid)->get_data()))!==$hash)throw new RuntimeException('Existing catalog changed');ips_check('original catalog unchanged',true);
file_put_contents('/tmp/official-savings-private.json',wp_json_encode(['username'=>$user,'password'=>$password,'annual'=>$id]));
echo wp_json_encode(['pass_count'=>count($GLOBALS['ips_checks']),'checks'=>$GLOBALS['ips_checks']],JSON_PRETTY_PRINT)."\n";
