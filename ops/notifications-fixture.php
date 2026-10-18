<?php
// Only mounted into the isolated test container; never deployed.
if(getenv('INSTAPASS_ISOLATED_TEST')!=='yes')return;
add_filter('pre_wp_mail',function($ret,$args){$mail=get_option('instapass_fixture_mail',[]);$mail[]=['subject'=>$args['subject'],'message'=>$args['message']];update_option('instapass_fixture_mail',array_slice($mail,-10),false);return true;},999,2);
add_action('rest_api_init',function(){
 register_rest_route('instapass-fixture/v1','/row',['methods'=>'POST','permission_callback'=>'__return_true','callback'=>function($request){
  global $wpdb;
  $email='browser-fixture@example.invalid';
  $r=$wpdb->get_row($wpdb->prepare('SELECT * FROM '.Instapass_Notifications::table().' WHERE email_hash=%s ORDER BY id DESC LIMIT 1',hash('sha256',$email)));
  if(!$r)return ['status'=>'absent'];
  $op=$request->get_param('op');
  if($op==='confirm-mail')Instapass_Notifications::job($r->id,'confirm');
  if($op==='restock'){$p=wc_get_product($r->product_id);$p->set_stock_status('instock');$p->save();Instapass_Notifications::job($r->id,'stock');}
  $r=$wpdb->get_row($wpdb->prepare('SELECT * FROM '.Instapass_Notifications::table().' WHERE id=%d',$r->id));
  return ['status'=>$r->status,'confirm'=>Instapass_Notifications::url($r,'confirm'),'unsubscribe'=>Instapass_Notifications::url($r,'unsubscribe'),'mail'=>get_option('instapass_fixture_mail',[])];
 }]);
});
