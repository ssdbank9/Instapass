<?php
defined('ABSPATH')||exit;
$mode=$args[0]??'';$id=absint($args[1]??0);$run=$args[2]??'';
if(!preg_match('/^dummy-[0-9]{14}-[a-f0-9]{6}$/D',$run))throw new RuntimeException('Invalid test run');
$receipt='instapass0408+'.$run.'@gmail.com';
function ipdt2_order_hash($o){$d=json_decode(wp_json_encode($o->get_data()),true);usort($d['meta_data'],static fn($a,$b)=>$a['id']<=>$b['id']);return hash('sha256',wp_json_encode($d));}
function ipdt2_state(){
  $orders=[];foreach(wc_get_orders(['limit'=>-1,'orderby'=>'ID','order'=>'ASC']) as $o)$orders[$o->get_id()]=ipdt2_order_hash($o);
  $c=new WC_Coupon(wc_get_coupon_id_by_code('toolkit5'));$p=wc_get_product(31);
  return ['orders'=>$orders,'coupon_usage'=>(int)$c->get_usage_count(),'stock_quantity'=>$p->get_stock_quantity(),'stock_status'=>$p->get_stock_status(),'price'=>$p->get_price('edit')];
}
if($mode==='before'){
  if(wc_get_orders(['billing_email'=>$receipt,'limit'=>1]))throw new RuntimeException('This test run already has an order');
  $p=wc_get_product(31);if(!$p||$p->get_sku()!=='claude-max-gift-x5'||!ip_alloc_enabled($p)||!$p->is_in_stock()||Instapass_Control_Money::usd_cents((string)$p->get_price('edit'))!==7500)throw new RuntimeException('Expected monthly offer unavailable or repriced');
  $c=new WC_Coupon(wc_get_coupon_id_by_code('toolkit5'));if(!$c->get_id()||!((float)$c->get_amount()===5.0))throw new RuntimeException('toolkit5 coupon missing or not 5%');
  $emails=WC()->mailer()->get_emails();$new=$emails['WC_Email_New_Order'];$owner_only=true;
  if($new->is_enabled())foreach(explode(',',$new->get_recipient()) as $recipient)if(strtolower(trim($recipient))!=='instapass0408@gmail.com')$owner_only=false;
  if(!$owner_only)throw new RuntimeException('Admin new-order mail is not restricted to the requested owner inbox');
  echo wp_json_encode(['run'=>$run,'ready'=>true,'state'=>ipdt2_state(),'receipt_enabled'=>$emails['WC_Email_Customer_On_Hold_Order']->is_enabled(),'admin_recipient_owner'=>true],JSON_PRETTY_PRINT)."\n";return;
}
$matches=wc_get_orders(['billing_email'=>$receipt,'limit'=>2,'orderby'=>'ID','order'=>'DESC']);
if(!$matches&&$mode==='cancel'){echo wp_json_encode(['not_found'=>true])."\n";return;}
if(count($matches)!==1)throw new RuntimeException('Expected exactly one matching test order');
$o=$matches[0];if($id&&$o->get_id()!==$id)throw new RuntimeException('Test order ID mismatch');
if($o->get_customer_id()!==0||!$o->get_date_created()||$o->get_date_created()->getTimestamp()<time()-7200||$o->is_paid()||$o->get_transaction_id()||$o->get_meta('_ip_payment_verified_at')||$o->get_meta('_ip_payment_reported_at'))throw new RuntimeException('Test order safety checks failed');
$items=array_values($o->get_items());if(count($items)!==1||$items[0]->get_product_id()!==31||(int)$items[0]->get_quantity()!==2)throw new RuntimeException('Unexpected test cart');
$item=$items[0];$plan=$item->get_meta('_ip_account_allocation');$saved=true;
if(!is_array($plan)||count($plan['accounts']??[])!==2||array_column($plan['accounts'],'units')!==[1,1])throw new RuntimeException('Saved allocation does not match1+1');
foreach($plan['accounts'] as $i=>$row)if($row['email']!=='instapass0408+'.$run.'-a'.($i+1).'@gmail.com')$saved=false;
if(!$saved||$o->get_currency()!=='USD'||(float)$o->get_total()!==142.50||(float)$o->get_discount_total()!==7.50||!in_array('toolkit5',$o->get_coupon_codes(),true))throw new RuntimeException('Saved email or price verification failed');
$usage_country=strtoupper((string)$o->get_meta('_instapass_usage_country',true));
if(!$usage_country)throw new RuntimeException('Country of use not saved');
if(!$o->get_meta('_ip_dummy_test_run')){
  $o->update_meta_data('_ip_dummy_test_run',$run);$o->set_billing_first_name('TEST ONLY');$o->set_billing_last_name('DO NOT FULFIL');
  $o->set_customer_note('Owner-authorized DUMMY checkout verification. Do not fulfil or activate. No payment made.');
  $o->save();$o->add_order_note('TEST ONLY — owner-authorized live checkout test. No payment made or verified. Do not fulfil. Allocation verified:1+1months on2accounts.');
}
$before_status=$o->get_status();
if($mode==='cancel'&&!$o->has_status('cancelled'))$o->update_status('cancelled','Dummy checkout verification complete. Cancelled without payment or fulfilment.',true);
elseif($mode!=='verify'&&$mode!=='cancel')throw new RuntimeException('Invalid audit mode');
$fresh=wc_get_order($o->get_id());
echo wp_json_encode(['id'=>$fresh->get_id(),'run'=>$run,'status_before'=>$before_status,'status'=>$fresh->get_status(),'paid'=>$fresh->is_paid(),'payment_reported'=>(bool)$fresh->get_meta('_ip_payment_reported_at'),'payment_verified'=>(bool)$fresh->get_meta('_ip_payment_verified_at'),'test_marked'=>$fresh->get_meta('_ip_dummy_test_run')===$run,'quantity'=>(int)$item->get_quantity(),'accounts'=>count($plan['accounts']),'units'=>array_column($plan['accounts'],'units'),'all_activation_emails_saved'=>$saved,'currency'=>$fresh->get_currency(),'total'=>$fresh->get_total(),'discount'=>$fresh->get_discount_total(),'usage_country'=>$usage_country,'guest'=>$fresh->get_customer_id()===0,'state'=>ipdt2_state()],JSON_PRETTY_PRINT)."\n";
