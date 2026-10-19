<?php
if(getenv('INSTAPASS_ISOLATED_TEST')!=='yes'){throw new RuntimeException('Isolated clone required');}
update_option('home','http://127.0.0.1:19052');update_option('siteurl','http://127.0.0.1:19052');
update_option('active_plugins',array('woocommerce/woocommerce.php','instapass-control-center/instapass-control-center.php','instapass-notifications/instapass-notifications.php','instapass-guest-orders/instapass-guest-orders.php','instapass-tenor-offers/instapass-tenor-offers.php'));
if(!function_exists('ip_tenor_offer_key')){require WP_PLUGIN_DIR.'/instapass-tenor-offers/instapass-tenor-offers.php';}
require '/tmp/configure.php';require '/tmp/three.php';
if(!WC()->session){WC()->initialize_session();}if(!WC()->customer){WC()->customer=new WC_Customer(0,true);}if(!WC()->cart){WC()->cart=new WC_Cart();}
$checks=array();$check=function($name,$ok)use(&$checks){if(!$ok){throw new RuntimeException($name);}$checks[]=$name;};
$products=array();$durations=array('1 month','3 months','6 months','12 months');$family='Isolated tenor product';$group=hash('sha256',strtolower($family));
for($i=0;$i<4;$i++){$p=new WC_Product_Simple();$p->set_name($family.' — '.$durations[$i]);$p->set_regular_price('10.99');$p->set_virtual(true);$p->set_stock_status('instock');$p->set_status('publish');$p->set_catalog_visibility('visible');$p->update_meta_data('_instapass_validity',$durations[$i]);$p->update_meta_data('_instapass_tenor_family',$family);$p->update_meta_data('_instapass_tenor_group',$group);$products[]=$p->save();}
$check('tenor plugin loaded',function_exists('ip_tenor_offer_key'));
$p=wc_get_product($products[0]);$check('related offers stay within same product family',count(ip_tenor_offers($p))===4);
$copy=new WC_Product_Simple();$copy->set_name('Isolated duplicate annual');$copy->set_regular_price('10.99');$copy->set_virtual(true);$copy->set_status('publish');$copy->set_catalog_visibility('hidden');$copy->update_meta_data('_instapass_validity','1 year');$copy->update_meta_data('_instapass_tenor_group',$group);$copyid=$copy->save();
$check('one year and twelve months share selection identity',ip_tenor_offer_key($copy)===ip_tenor_offer_key(wc_get_product($products[3])));
foreach(array('24 months'=>'soldout','36 months'=>'quote','48 months'=>'draft') as $duration=>$kind){$x=new WC_Product_Simple();$x->set_name('Isolated '.$kind);$x->set_virtual(true);$x->set_status($kind==='draft'?'draft':'publish');$x->set_catalog_visibility('visible');if($kind!=='quote'){$x->set_regular_price('20.99');}$x->set_stock_status($kind==='soldout'?'outofstock':'instock');$x->update_meta_data('_instapass_validity',$duration);$x->update_meta_data('_instapass_tenor_group',$group);$x->save();}
$markup=ip_tenor_markup(wc_get_product($products[0]));$check('unavailable tenors cannot be added',strpos($markup,'Out of stock')!==false&&strpos($markup,'Price on request')!==false&&strpos($markup,'48 months')===false);

$set=function($ids,$qty=1){WC()->cart->empty_cart();wc_clear_notices();foreach($ids as $id){WC()->cart->add_to_cart($id,$qty);}WC()->cart->calculate_totals();};
$set(array($products[3],$copyid));$check('duplicate annual listings do not inflate bundle count',instapass_cart_tool_count(WC()->cart)===1&&!WC()->cart->apply_coupon('toolkit5'));
$set(array($products[0]));$check('one product cannot use 5%',!WC()->cart->apply_coupon('toolkit5'));
$set(array_slice($products,0,2));$check('two selections reject 7.5%',!WC()->cart->apply_coupon('toolkit75'));
$set(array($products[0]),4);$check('four copies of one product cannot use 10%',!WC()->cart->apply_coupon('toolkit10'));
$set(array_slice($products,0,2));$check('two different products accept 5%',WC()->cart->apply_coupon('toolkit5'));WC()->cart->calculate_totals();
$check('two product native discount is 1.10',abs(WC()->cart->get_discount_total()-1.10)<0.001);$check('two product total is 20.88',abs(WC()->cart->get_total('edit')-20.88)<0.001);
$set(array_slice($products,0,3));$check('three products cannot use 10%',!WC()->cart->apply_coupon('toolkit10'));
$check('three product tenors accept 7.5%',WC()->cart->apply_coupon('toolkit75'));WC()->cart->calculate_totals();
$check('7.5% native coupon amount retained',(float)(new WC_Coupon('toolkit75'))->get_amount()===7.5);
$check('three selection discount is 2.47 and total 30.50',abs(WC()->cart->get_discount_total()-2.47)<0.001&&abs(WC()->cart->get_total('edit')-30.50)<0.001);
$three_id=WC()->checkout()->create_order(array('billing_email'=>'three-offer@example.invalid','payment_method'=>'instapass_binance'));if(is_wp_error($three_id)){throw new RuntimeException($three_id->get_error_message());}$three_order=wc_get_order($three_id);
$check('guest order retains three-selection discount',abs($three_order->get_discount_total()-2.47)<0.001&&abs($three_order->get_total()-30.50)<0.001&&$three_order->get_customer_id()===0);
WC()->cart->apply_coupon('toolkit5');WC()->cart->apply_coupon('toolkit75');WC()->cart->calculate_totals();$check('middle offer replaces other code without stacking',count(WC()->cart->get_applied_coupons())===1&&in_array('toolkit75',WC()->cart->get_applied_coupons(),true));

$set($products);$check('four different products accept 10%',WC()->cart->apply_coupon('toolkit10'));WC()->cart->calculate_totals();
$check('four product native discount is 4.40',abs(WC()->cart->get_discount_total()-4.40)<0.001);$check('four product total is 39.56',abs(WC()->cart->get_total('edit')-39.56)<0.001);
WC()->cart->remove_coupons();WC()->cart->apply_coupon('toolkit5');WC()->cart->apply_coupon('toolkit10');WC()->cart->calculate_totals();
echo "\nNon-stacking readback: ".wp_json_encode(WC()->cart->get_applied_coupons())."\n";$check('coupon codes do not stack',count(WC()->cart->get_applied_coupons())===1);WC()->cart->remove_coupons();$check('customer can switch to 10% after removing 5%',WC()->cart->apply_coupon('toolkit10'));
$paid=wc_create_order(array('customer_id'=>0));$paid->set_billing_email('bundle-returning@example.invalid');$paid->set_total(10);$paid->set_status('processing');$paid->save();WC()->customer->set_billing_email('bundle-returning@example.invalid');
$set($products);$check('returning guest email accepts bundle offer',WC()->cart->apply_coupon('toolkit10'));WC()->cart->calculate_totals();
foreach(array(0,2,1) as $slot){$c=instapass_promo_config($slot);$coupon=new WC_Coupon($c['coupon_id']);$check('offer '.$slot.' has no per-customer restriction',$coupon->get_usage_limit_per_user()===0&&$coupon->get_meta('_instapass_first_order_email')==='');}
$check('first-order custom function removed',!function_exists('ip_guest_first_order'));
$data=array('billing_email'=>'bundle-returning@example.invalid','instapass_activation_email'=>'','payment_method'=>'instapass_binance');$id=WC()->checkout()->create_order($data);if(is_wp_error($id)){throw new RuntimeException($id->get_error_message());}$o=wc_get_order($id);
$lines=array_values($o->get_items());$check('each saved order line retains selected tenor',count($lines)===4&&$lines[0]->get_meta('Duration')==='1 month'&&$lines[3]->get_meta('Duration')==='12 months');
$check('guest order retains 10% discount and total',abs($o->get_discount_total()-4.40)<0.001&&abs($o->get_total()-39.56)<0.001&&$o->get_customer_id()===0);
$priced=wc_get_product($products[1]);$priced->set_regular_price('20.99');$priced->save();$set(array_slice($products,0,2));WC()->cart->apply_coupon('toolkit5');WC()->cart->calculate_totals();
$check('different tenor prices remain independent in cart',abs(WC()->cart->get_subtotal()-31.98)<0.001&&abs(WC()->cart->get_discount_total()-1.60)<0.001&&abs(WC()->cart->get_total('edit')-30.38)<0.001);
$check('duration choice displays the independent higher price',strpos(ip_tenor_markup(wc_get_product($products[0])),'20.99')!==false);
$priced->set_regular_price('10.99');$priced->save();
$u='bundle-owner-fixture';$password=wp_generate_password(32,true,true);$uid=wp_create_user($u,$password,'bundle-owner@example.invalid');if(is_wp_error($uid)){throw new RuntimeException('fixture owner failed');}(new WP_User($uid))->set_role('administrator');
file_put_contents('/tmp/bundle-private.json',wp_json_encode(array('products'=>$products,'username'=>$u,'password'=>$password)));
echo "\n".wp_json_encode(array('pass_count'=>count($checks),'checks'=>$checks),JSON_PRETTY_PRINT);