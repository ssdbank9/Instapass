<?php
if(getenv('INSTAPASS_ISOLATED_TEST')!=='yes')throw new RuntimeException('Isolated clone required');
update_option('home','http://127.0.0.1:19062');update_option('siteurl','http://127.0.0.1:19062');
$GLOBALS['ipa_checks']=[];
function ipa_check($n,$ok){if(!$ok)throw new RuntimeException('FAIL '.$n);$GLOBALS['ipa_checks'][]=$n;}
function ipa_bad($n,$fn){try{$fn();}catch(InvalidArgumentException $e){ipa_check($n,true);return;}ipa_check($n,false);}
$old=[];foreach(wc_get_products(['limit'=>-1,'status'=>['publish','draft','private','pending']]) as $p)$old[$p->get_id()]=hash('sha256',wp_json_encode($p->get_data()));
function ipa_order_data($o){$d=json_decode(wp_json_encode($o->get_data()),true);usort($d['meta_data'],static fn($a,$b)=>$a['id']<=>$b['id']);return $d;}
$GLOBALS['ipa_old_orders']=[];foreach(wc_get_orders(['limit'=>-1]) as $o)$GLOBALS['ipa_old_orders'][$o->get_id()]=ipa_order_data($o);
$user='alloc-fixture-'.wp_generate_password(8,false,false);$pass=wp_generate_password(28,true,true);$uid=wp_create_user($user,$pass,$user.'@example.invalid');if(is_wp_error($uid))throw new RuntimeException('Fixture user failed');(new WP_User($uid))->set_role('administrator');wp_set_current_user($uid);
$v=array_merge(ip_offer_read(),['name'=>'QA Flexible Monthly','sku'=>'QA-ALLOCATION-MONTH','family'=>'QA Allocation','duration'=>'1 month','brand'=>'claude','usd_regular'=>'75','status'=>'publish','allocation'=>'yes']);$id=ip_offer_save($v);$p=wc_get_product($id);
ipa_check('owner saves enabled allocation',ip_offer_read($p)['allocation']==='yes'&&ip_alloc_enabled($p));
$off=ip_offer_read($p);$off['allocation']='no';ip_offer_save($off);ipa_check('owner can disable allocation',!ip_alloc_enabled(wc_get_product($id)));$off=ip_offer_read(wc_get_product($id));$off['allocation']='yes';ip_offer_save($off);$p=wc_get_product($id);
$bad=ip_offer_read($p);$bad['duration']='Lifetime';ipa_bad('non-monthly owner policy refused',fn()=>ip_offer_save($bad));$bad=ip_offer_read($p);$bad['access_type']='Shared';ipa_bad('shared account policy refused',fn()=>ip_offer_save($bad));
ipa_check('confirmed live Claude Max monthly opts in by default',ip_alloc_enabled(wc_get_product(31)));
ipa_check('other products not silently opted in',!ip_alloc_enabled(wc_get_product(29))&&!ip_alloc_enabled(wc_get_product(140)));
foreach(instapass_promo_options() as $slot=>$key){$c=instapass_promo_config($slot);$c=array_merge($c,['enabled'=>true,'mode'=>'quantity','product_ids'=>[],'starts_at'=>0,'ends_at'=>0]);instapass_write_promo($c,$slot);}
update_option('woocommerce_bacs_settings',['enabled'=>'yes','title'=>'QA transfer only','instructions'=>'Synthetic isolated test. Never transfer funds.','instapass_currencies'=>['USD']]);
WC()->initialize_session();WC()->cart=new WC_Cart();$GLOBALS['ip_cc_test_front']=true;$GLOBALS['ip_cc_test_currency']='USD';wp_set_current_user(0);
function ipa_payload($p,$qty,$parts,$mode='custom'){$rows=[];foreach($parts as $i=>$n)$rows[]=['units'=>(string)$n,'kind'=>$i%2?'new':'existing','email'=>'activation-'.($i+1).'@example.invalid'];return ['stamp'=>ip_alloc_stamp($p,$qty),'mode'=>$mode,'account_count'=>(string)count($parts),'accounts'=>$rows];}
foreach([[4],[3,1],[2,2],[2,1,1],[1,1,1,1]] as $parts){
 $label=implode('+',$parts);$raw=ipa_payload($p,4,$parts);$plan=ip_alloc_validate($raw,$p,4,'receipt@example.invalid');
 ipa_check('partition '.$label.' validates',array_column($plan['accounts'],'units')===$parts);
 ipa_check('partition '.$label.' months preserved',array_sum(array_column($plan['accounts'],'months'))===4);
 WC()->cart->empty_cart();$key=WC()->cart->add_to_cart($id,4);ipa_check('partition '.$label.' quantity coupon accepted',WC()->cart->apply_coupon('TOOLKIT10'));WC()->cart->calculate_totals();
 $_POST=['ip_alloc'=>[$key=>$raw],'instapass_activation_email'=>'order-activation@example.invalid'];
 $oid=WC()->checkout()->create_order(['billing_email'=>'allocation-'.$label.'@example.invalid','payment_method'=>'bacs','createaccount'=>false]);if(is_wp_error($oid))throw new RuntimeException($oid->get_error_message());$o=wc_get_order($oid);$o->update_meta_data('_ip_allocation_fixture','yes');$o->save();$item=array_values($o->get_items())[0];
 ipa_check('partition '.$label.' immutable plan saved',$item->get_meta('_ip_account_allocation')===$plan);
 ipa_check('partition '.$label.' total and discount unchanged',(float)$o->get_total()===270.0&&(float)$o->get_discount_total()===30.0&&$o->get_customer_id()===0);
 ob_start();do_action('woocommerce_order_item_meta_end',$item->get_id(),$item,$o,false);$html=ob_get_clean();ipa_check('partition '.$label.' authorised instructions rendered',str_contains($html,'activation-1@example.invalid')&&str_contains($html,'purchased unit'));
 $_POST=['ip_track_action'=>'track','order_number'=>(string)$oid,'order_email'=>$o->get_billing_email(),'_ip_track_nonce'=>wp_create_nonce('ip_guest_track')];$_SERVER['REQUEST_METHOD']='POST';$tracking=ip_guest_tracking_shortcode();ipa_check('partition '.$label.' tracking summary without activation emails',str_contains($tracking,ip_alloc_summary($plan))&&!str_contains($tracking,'activation-1@example.invalid'));unset($_SERVER['REQUEST_METHOD']);
}
$raw=ipa_payload($p,4,[4],'one');$raw['accounts'][0]['email']='';$plan=ip_alloc_validate($raw,$p,4,'Fallback@Example.invalid');ipa_check('one account defaults to activation email',$plan['accounts'][0]['email']==='fallback@example.invalid');
$raw=ipa_payload($p,4,[1,1,1,1],'each');ipa_check('each-account preset preserves four destinations',count(ip_alloc_validate($raw,$p,4,'fallback@example.invalid')['accounts'])===4);
foreach(['missing'=>null,'array stamp'=>['stamp'=>[]],'wrong stamp'=>['stamp'=>'wrong']] as $n=>$raw)ipa_bad($n.' rejected',fn()=>ip_alloc_validate($raw,$p,4,'receipt@example.invalid'));
foreach(['under'=>[2,1],'over'=>[3,2],'zero'=>[0,4],'negative'=>[-1,5],'fractional'=>['1.5',2.5]] as $n=>$parts){$raw=ipa_payload($p,4,$parts);ipa_bad($n.' allocation rejected',fn()=>ip_alloc_validate($raw,$p,4,'r@example.invalid'));}
$raw=ipa_payload($p,4,[2,2]);$raw['accounts'][1]['email']='ACTIVATION-1@example.invalid';ipa_bad('duplicate case-insensitive account email rejected',fn()=>ip_alloc_validate($raw,$p,4,'r@example.invalid'));
$raw=ipa_payload($p,4,[2,2]);$raw['accounts'][0]['email']='invalid';ipa_bad('invalid activation email rejected',fn()=>ip_alloc_validate($raw,$p,4,'r@example.invalid'));
$raw=ipa_payload($p,4,[2,2]);$raw['accounts'][0]['kind']='administrator';ipa_bad('forged account type rejected',fn()=>ip_alloc_validate($raw,$p,4,'r@example.invalid'));
$raw=ipa_payload($p,4,[4]);$raw['mode']=[];ipa_bad('array mode rejected',fn()=>ip_alloc_validate($raw,$p,4,'r@example.invalid'));
$raw=ipa_payload($p,4,[4]);ipa_bad('stale cart quantity refused',fn()=>ip_alloc_validate($raw,$p,3,'r@example.invalid'));
$raw=ipa_payload($p,4,[4]);$changed=clone $p;$changed->update_meta_data('_instapass_validity','6 months');ipa_bad('stale duration refused',fn()=>ip_alloc_validate($raw,$changed,4,'r@example.invalid'));
ipa_check('one unit retains simple activation path',ip_alloc_validate(null,$p,1,'r@example.invalid')===[]);
foreach(['zero'=>'0','fraction'=>'2.5','array'=>[],'excess'=>'5'] as $name=>$count){$raw=ipa_payload($p,4,[2,2]);$raw['account_count']=$count;ipa_bad('invalid account count '.$name,fn()=>ip_alloc_validate($raw,$p,4,'r@example.invalid'));}
$raw=ipa_payload($p,4,[2,2]);unset($raw['account_count']);ipa_bad('missing custom count rejected',fn()=>ip_alloc_validate($raw,$p,4,'r@example.invalid'));
$raw=ipa_payload($p,4,[2,2]);$raw['account_count']='3';ipa_bad('declared account count must match populated rows',fn()=>ip_alloc_validate($raw,$p,4,'r@example.invalid'));
$raw=ipa_payload($p,4,[2,1,1]);$raw['accounts'][2]['email']='';ipa_bad('every selected account requires email',fn()=>ip_alloc_validate($raw,$p,4,'r@example.invalid'));
$raw=ipa_payload($p,4,[4]);$raw['accounts'][0]['email']='';ipa_check('single custom account may use activation fallback',ip_alloc_validate($raw,$p,4,'fallback@example.invalid')['accounts'][0]['email']==='fallback@example.invalid');
$changed=clone $p;$changed->update_meta_data('_ip_account_allocation','no');ipa_check('disabled offer retains original checkout',ip_alloc_validate(null,$changed,4,'r@example.invalid')===[]);
// Order-create hook runs before order-level activation metadata is saved.
WC()->cart->empty_cart();$key=WC()->cart->add_to_cart($id,4);$raw=ipa_payload($p,4,[4],'one');$raw['accounts'][0]['email']='';$_POST=['ip_alloc'=>[$key=>$raw],'instapass_activation_email'=>'separate-activation@example.invalid'];$oid=WC()->checkout()->create_order(['billing_email'=>'receipt-only@example.invalid','payment_method'=>'bacs']);$o=wc_get_order($oid);$o->update_meta_data('_ip_allocation_fixture','yes');$o->save();$item=array_values($o->get_items())[0];ipa_check('different activation email used during real order construction',$item->get_meta('_ip_account_allocation')['accounts'][0]['email']==='separate-activation@example.invalid');
$_POST=[];$request=new WP_REST_Request('POST','/wc/store/v1/checkout');try{do_action('woocommerce_store_api_checkout_update_order_from_request',$o,$request);ipa_check('alternate API checkout blocked',false);}catch(\Automattic\WooCommerce\StoreApi\Exceptions\RouteException $e){ipa_check('alternate API cannot skip allocation',true);}
foreach($old as $pid=>$hash)if(hash('sha256',wp_json_encode(wc_get_product($pid)->get_data()))!==$hash)throw new RuntimeException('Original catalog changed');ipa_check('original product data unchanged',true);
foreach($GLOBALS['ipa_old_orders'] as $oid=>$before){$after=ipa_order_data(wc_get_order($oid));if($before!==$after){$changed=[];foreach($before as $k=>$v)if(($after[$k]??null)!==$v)$changed[]=$k;throw new RuntimeException('Original order changed fields: '.implode(',',$changed));}}ipa_check('original orders unchanged',true);
file_put_contents('/tmp/account-allocation-private.json',wp_json_encode(['username'=>$user,'password'=>$pass,'monthly'=>$id]));
echo wp_json_encode(['pass_count'=>count($GLOBALS['ipa_checks']),'checks'=>$GLOBALS['ipa_checks']],JSON_PRETTY_PRINT)."\n";
