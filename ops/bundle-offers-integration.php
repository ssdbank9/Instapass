<?php
if(getenv('INSTAPASS_ISOLATED_TEST')!=='yes'){throw new RuntimeException('Isolated clone required');}
update_option('home','http://127.0.0.1:19052');update_option('siteurl','http://127.0.0.1:19052');
update_option('active_plugins',array('woocommerce/woocommerce.php','instapass-control-center/instapass-control-center.php','instapass-notifications/instapass-notifications.php','instapass-guest-orders/instapass-guest-orders.php'));
require '/tmp/configure.php';
if(!WC()->session){WC()->initialize_session();}if(!WC()->customer){WC()->customer=new WC_Customer(0,true);}if(!WC()->cart){WC()->cart=new WC_Cart();}
$checks=array();$check=function($name,$ok)use(&$checks){if(!$ok){throw new RuntimeException($name);}$checks[]=$name;};
$products=array();for($i=1;$i<=4;$i++){$p=new WC_Product_Simple();$p->set_name('Isolated bundle product '.$i);$p->set_regular_price('10.99');$p->set_virtual(true);$p->set_stock_status('instock');$p->set_status('publish');$p->set_catalog_visibility('hidden');$products[]=$p->save();}
$set=function($ids,$qty=1){WC()->cart->empty_cart();wc_clear_notices();foreach($ids as $id){WC()->cart->add_to_cart($id,$qty);}WC()->cart->calculate_totals();};
$set(array($products[0]));$check('one product cannot use 5%',!WC()->cart->apply_coupon('toolkit5'));
$set(array($products[0]),4);$check('four copies of one product cannot use 10%',!WC()->cart->apply_coupon('toolkit10'));
$set(array_slice($products,0,2));$check('two different products accept 5%',WC()->cart->apply_coupon('toolkit5'));WC()->cart->calculate_totals();
$check('two product native discount is 1.10',abs(WC()->cart->get_discount_total()-1.10)<0.001);$check('two product total is 20.88',abs(WC()->cart->get_total('edit')-20.88)<0.001);
$set(array_slice($products,0,3));$check('three products cannot use 10%',!WC()->cart->apply_coupon('toolkit10'));
$set($products);$check('four different products accept 10%',WC()->cart->apply_coupon('toolkit10'));WC()->cart->calculate_totals();
$check('four product native discount is 4.40',abs(WC()->cart->get_discount_total()-4.40)<0.001);$check('four product total is 39.56',abs(WC()->cart->get_total('edit')-39.56)<0.001);
WC()->cart->remove_coupons();WC()->cart->apply_coupon('toolkit5');WC()->cart->apply_coupon('toolkit10');WC()->cart->calculate_totals();
echo "\nNon-stacking readback: ".wp_json_encode(WC()->cart->get_applied_coupons())."\n";$check('coupon codes do not stack',count(WC()->cart->get_applied_coupons())===1);WC()->cart->remove_coupons();$check('customer can switch to 10% after removing 5%',WC()->cart->apply_coupon('toolkit10'));
$paid=wc_create_order(array('customer_id'=>0));$paid->set_billing_email('bundle-returning@example.invalid');$paid->set_total(10);$paid->set_status('processing');$paid->save();WC()->customer->set_billing_email('bundle-returning@example.invalid');
$set($products);$check('returning guest email accepts bundle offer',WC()->cart->apply_coupon('toolkit10'));WC()->cart->calculate_totals();
foreach(array(0,1) as $slot){$c=instapass_promo_config($slot);$coupon=new WC_Coupon($c['coupon_id']);$check('offer '.$slot.' has no per-customer restriction',$coupon->get_usage_limit_per_user()===0&&$coupon->get_meta('_instapass_first_order_email')==='');}
$check('first-order custom function removed',!function_exists('ip_guest_first_order'));
$data=array('billing_email'=>'bundle-returning@example.invalid','instapass_activation_email'=>'','payment_method'=>'instapass_binance');$id=WC()->checkout()->create_order($data);if(is_wp_error($id)){throw new RuntimeException($id->get_error_message());}$o=wc_get_order($id);
$check('guest order retains 10% discount and total',abs($o->get_discount_total()-4.40)<0.001&&abs($o->get_total()-39.56)<0.001&&$o->get_customer_id()===0);
$u='bundle-owner-fixture';$password=wp_generate_password(32,true,true);$uid=wp_create_user($u,$password,'bundle-owner@example.invalid');if(is_wp_error($uid)){throw new RuntimeException('fixture owner failed');}(new WP_User($uid))->set_role('administrator');
file_put_contents('/tmp/bundle-private.json',wp_json_encode(array('products'=>$products,'username'=>$u,'password'=>$password)));
echo "\n".wp_json_encode(array('pass_count'=>count($checks),'checks'=>$checks),JSON_PRETTY_PRINT);
