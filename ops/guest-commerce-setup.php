<?php
if(getenv('INSTAPASS_ISOLATED_TEST')!=='yes'){throw new Exception('Not isolated');}
remove_all_filters('option_home');remove_all_filters('option_siteurl');
update_option('home','http://127.0.0.1:19051');update_option('siteurl','http://127.0.0.1:19051');
update_option('active_plugins',array('woocommerce/woocommerce.php','instapass-control-center/instapass-control-center.php','instapass-notifications/instapass-notifications.php'));
require_once ABSPATH.'wp-admin/includes/plugin.php';
activate_plugin('instapass-guest-orders/instapass-guest-orders.php');
if(!function_exists('ip_guest_install')){require_once WP_PLUGIN_DIR.'/instapass-guest-orders/instapass-guest-orders.php';}
$r=ip_guest_install();if(is_wp_error($r)){throw new Exception($r->get_error_message());}
update_option('ip_guest_test_mail',array());
foreach(array('instapass_binance','instapass_crypto','instapass_easypaisa') as $id){$s=get_option('woocommerce_'.$id.'_settings',array());$s['enabled']='yes';$s['instructions']='TEST ONLY — no real payment. Use the simulated reference on this isolated site.';$s['instapass_currencies']=array($id==='instapass_easypaisa'?'PKR':'USD');update_option('woocommerce_'.$id.'_settings',$s);}
$settings=get_option('woocommerce_new_order_settings',array());$settings['recipient']='instapass0408@gmail.com';update_option('woocommerce_new_order_settings',$settings);
$pids=array();foreach(array('10.99','20.99') as $i=>$price){$p=new WC_Product_Simple();$p->set_name('Isolated Guest Test '.($i+1));$p->set_status('publish');$p->set_virtual(true);$p->set_regular_price($price);$p->set_stock_status('instock');$p->set_catalog_visibility('hidden');$pids[]=$p->save();}
$c=new WC_Coupon();$c->set_code('qa-first');$c->set_discount_type('percent');$c->set_amount(10);$c->set_usage_limit_per_user(1);$c->set_individual_use(true);$c->update_meta_data('_instapass_first_order_email','yes');$c->save();
$o=wc_create_order(array('customer_id'=>0));$o->set_billing_email('used@example.invalid');$o->set_status('processing');$o->set_total(10);$o->save();
$tests=array();$check=function($label,$condition)use(&$tests){if(!$condition){throw new Exception($label);}$tests[]=$label;};
$check('guest checkout enabled',get_option('woocommerce_enable_guest_checkout')==='yes');
$check('checkout never requires registration',apply_filters('woocommerce_checkout_registration_required',false)===false);
$check('optional account cannot auto-authenticate unverified email',apply_filters('woocommerce_registration_auth_new_customer',true)===false);
$check('first paid email rejected',ip_guest_first_order('used@example.invalid')===false);
$check('new guest email allowed',ip_guest_first_order('guest-first@example.invalid')===true);
$check('different email same visitor allowed',ip_guest_first_order('guest-another@example.invalid')===true);
$check('tracking wrong email rejects',ip_guest_lookup_order($o->get_id(),'wrong@example.invalid')===false);
$check('tracking correct email accepts',ip_guest_lookup_order($o->get_id(),'used@example.invalid')->get_id()===$o->get_id());
$check('reference normalization spans payment methods',ip_payment_reference_key('instapass_binance','Ab-12 34')===ip_payment_reference_key('instapass_crypto','ab1234'));
$methods=ip_payment_methods($o);$check('USD payment proof excludes PKR-only method',!isset($methods['instapass_easypaisa']));
$check('unpriced product still unavailable',wc_get_product(114)->get_price()==='');
$user='guest-fixture-owner';$password=wp_generate_password(30,true,true);$uid=wp_create_user($user,$password,'fixture-owner@example.invalid');if(is_wp_error($uid)){throw new Exception('fixture user failure');}(new WP_User($uid))->set_role('administrator');
file_put_contents('/tmp/guest-commerce-private.json',wp_json_encode(array('products'=>$pids,'username'=>$user,'password'=>$password,'paid_order'=>$o->get_id())));
echo wp_json_encode(array('pass_count'=>count($tests),'checks'=>$tests),JSON_PRETTY_PRINT);
