<?php
if (!function_exists('instapass_control_page')) require '/tmp/control-center/instapass-control-center.php';
$checks=[];
add_filter('wp_die_handler',function(){return function($message,$title='',$args=[]){throw new RuntimeException('DENIED:'.($args['response']??''));};});
wp_set_current_user(0);
try{ob_start();instapass_control_page();ob_end_clean();throw new Exception('Anonymous access accepted');}catch(RuntimeException $e){if(ob_get_level())ob_end_clean();if(strpos($e->getMessage(),'DENIED:403')!==0)throw $e;$checks[]='anonymous_denied';}
$admins=get_users(['role'=>'administrator','number'=>1,'fields'=>'ID']);
if(!$admins)throw new Exception('No administrator available');
wp_set_current_user((int)$admins[0]);
$_SERVER['REQUEST_METHOD']='GET';
ob_start();instapass_control_page();$html=ob_get_clean();
if(strpos($html,'Your store, in one place.')===false || strpos($html,'Prices &amp; bulk discounts')===false)throw new Exception('Dashboard render missing controls');
$checks[]='owner_render';
$_SERVER['REQUEST_METHOD']='POST';
$_POST=['usd'=>'4.99','quantity'=>'3','_wpnonce'=>wp_create_nonce('instapass_currency_preview')];$_REQUEST=$_POST;
ob_start();instapass_control_page();$preview=ob_get_clean();
if(strpos($preview,'Rs 4,200')===false)throw new Exception('Preview quantity total wrong');
$checks[]='owner_preview_4200';
$_POST['usd']='"><script>alert(1)</script>';$_REQUEST=$_POST;
ob_start();instapass_control_page();$invalid=ob_get_clean();
if(strpos($invalid,'role="alert"')===false || strpos($invalid,'<script>alert(1)</script>')!==false)throw new Exception('Invalid input not safely rejected');
$checks[]='malformed_input_escaped';
$_POST['_wpnonce']='invalid';$_REQUEST=$_POST;
try{ob_start();instapass_control_page();ob_end_clean();throw new Exception('Invalid nonce accepted');}catch(RuntimeException $e){if(ob_get_level())ob_end_clean();if(strpos($e->getMessage(),'DENIED:')!==0)throw $e;$checks[]='invalid_nonce_denied';}
file_put_contents('/tmp/control-center-preview.html',$preview);
echo wp_json_encode(['checks'=>$checks,'order_count'=>wc_get_orders(['limit'=>1,'paginate'=>true])->total,'currency'=>get_option('woocommerce_currency')],JSON_PRETTY_PRINT);
