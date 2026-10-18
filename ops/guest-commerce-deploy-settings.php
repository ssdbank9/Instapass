<?php
$keys=array('woocommerce_enable_guest_checkout','woocommerce_enable_signup_and_login_from_checkout','woocommerce_enable_myaccount_registration','woocommerce_registration_generate_username','woocommerce_registration_generate_password','woocommerce_enable_coupons','woocommerce_new_order_settings','instapass_tracking_page_id');
if(getenv('IP_GUEST_CAPTURE')==='yes'){$before=array();foreach($keys as $key){$before[$key]=get_option($key,null);}echo wp_json_encode($before,JSON_PRETTY_PRINT);return;}
if(!function_exists('ip_guest_install')){throw new Exception('Guest plugin is not active');}
$result=ip_guest_install();if(is_wp_error($result)){throw new Exception($result->get_error_message());}
$settings=get_option('woocommerce_new_order_settings',array());$settings['recipient']='instapass0408@gmail.com';update_option('woocommerce_new_order_settings',$settings);
echo "Guest settings and owner notification recipient configured.\n";
