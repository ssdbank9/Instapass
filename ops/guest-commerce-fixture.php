<?php
if(getenv('INSTAPASS_ISOLATED_TEST')!=='yes'){return;}
add_filter('option_home',fn()=> 'http://127.0.0.1:19051');
add_filter('option_siteurl',fn()=> 'http://127.0.0.1:19051');
add_filter('pre_wp_mail',function($ret,$args){$mail=get_option('ip_guest_test_mail',array());$mail[]=$args;update_option('ip_guest_test_mail',array_slice($mail,-50),false);return true;},999,2);
add_action('rest_api_init',function(){register_rest_route('ip-guest-fixture/v1','/state',array('methods'=>'GET','permission_callback'=>'__return_true','callback'=>function(){
 $orders=wc_get_orders(array('limit'=>-1,'orderby'=>'ID','order'=>'DESC'));$rows=array();
 foreach($orders as $o){if(strpos($o->get_billing_email(),'guest-')!==0){continue;}$rows[]=array('id'=>$o->get_id(),'email'=>$o->get_billing_email(),'activation'=>ip_guest_activation_email($o),'customer_id'=>$o->get_customer_id(),'status'=>$o->get_status(),'paid'=>$o->is_paid(),'total'=>$o->get_total(),'discount'=>$o->get_discount_total(),'reference'=>$o->get_meta('_ip_payment_reference'),'reported_method'=>$o->get_meta('_ip_payment_method'),'screenshot'=>(bool)$o->get_meta('_ip_payment_screenshot'),'url'=>$o->get_checkout_order_received_url(),'admin_url'=>$o->get_edit_order_url());}
 return array('orders'=>$rows,'mail'=>get_option('ip_guest_test_mail',array()),'registered'=>(bool)email_exists('registration-fixture@example.invalid'));
}));});
