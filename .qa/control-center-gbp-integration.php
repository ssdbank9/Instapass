<?php
if (getenv('INSTAPASS_ISOLATED_TEST')!=='yes')throw new RuntimeException('Isolated test flag required.');
require '/tmp/gbp/gbp-editor.php';
if(!class_exists('WC_Admin_Meta_Boxes'))require WC_ABSPATH.'includes/admin/class-wc-admin-meta-boxes.php';
if(!function_exists('woocommerce_wp_text_input'))require WC_ABSPATH.'includes/admin/wc-meta-box-functions.php';
function gbp_assert($condition,$message){if(!$condition)throw new RuntimeException($message);}
wp_set_current_user(1);
$before=wc_get_orders(['limit'=>1,'paginate'=>true])->total;
$p=new WC_Product_Simple();$p->set_name('DISPOSABLE GBP QA');$p->set_status('draft');$p->set_virtual(true);$p->set_regular_price('17.99');$p->set_price('17.99');$id=$p->save();
$checks=[];
$save=function($regular,$sale,$discount='',$nonce=true,$stale=false)use($id){
    $p=wc_get_product($id);
    $_POST=['instapass_gbp_nonce'=>$nonce?wp_create_nonce('instapass_gbp_product_'.$id):'bad','instapass_gbp_fingerprint'=>$stale?'stale':instapass_control_gbp_fingerprint($p),'_instapass_gbp_regular_price'=>$regular,'_instapass_gbp_sale_price'=>$sale,'_instapass_gbp_discount'=>$discount];
    do_action('woocommerce_admin_process_product_object',$p);$p->save();clean_post_cache($id);return wc_get_product($id);
};
try{
 $p=$save('20','','25');gbp_assert($p->get_meta('_instapass_gbp_regular_price')==='20.00'&&$p->get_meta('_instapass_gbp_sale_price')==='15.00','Discount persistence failed');$checks[]='discount_saved_and_reloaded';
 gbp_assert($p->get_regular_price('edit')==='17.99'&&$p->get_price('edit')==='17.99','USD changed');$checks[]='usd_unchanged';
 $p=$save('24.99','19.75');gbp_assert($p->get_meta('_instapass_gbp_sale_price')==='19.75','Manual GBP failed');$checks[]='manual_sale_saved';
 $p=$save('24.99','', '0');gbp_assert($p->get_meta('_instapass_gbp_sale_price')==='','Remove discount failed');$checks[]='remove_sale';
 foreach([['25','15','',false,false],['25','15','',true,true],['24.99','30','',true,false],['24.99',[],'',true,false]] as $case){$p=$save(...$case);gbp_assert($p->get_meta('_instapass_gbp_regular_price')==='24.99'&&$p->get_meta('_instapass_gbp_sale_price')==='','Invalid update altered GBP');}
 $checks[]='nonce_stale_invalid_and_array_rejected';
 $_POST=[];do_action('woocommerce_admin_process_product_object',$p);$p->save();$p=wc_get_product($id);gbp_assert($p->get_meta('_instapass_gbp_regular_price')==='24.99','Absent fields cleared GBP');$checks[]='absent_fields_preserved';
 wp_set_current_user(0);$p=$save('30','15');gbp_assert($p->get_meta('_instapass_gbp_regular_price')==='24.99','Anonymous update accepted');$checks[]='anonymous_save_denied';wp_set_current_user(1);
 $p=$save('','','');gbp_assert(Instapass_Control_GBP::effective((string)$p->get_meta('_instapass_gbp_regular_price'),(string)$p->get_meta('_instapass_gbp_sale_price'))===null,'Blank became zero');$checks[]='blank_unavailable';
 $GLOBALS['product_object']=$p;ob_start();instapass_control_gbp_fields();$html=ob_get_clean();gbp_assert(strpos($html,'GBP regular price')!==false&&strpos($html,'Apply GBP discount')!==false,'Fields missing');file_put_contents('/tmp/gbp-fields.html',$html);$checks[]='fields_rendered';
 gbp_assert(wc_get_orders(['limit'=>1,'paginate'=>true])->total===$before,'Order count changed');gbp_assert(get_option('woocommerce_currency')==='USD','Base currency changed');
 echo wp_json_encode(['checks'=>$checks,'orders_unchanged'=>true,'store_currency'=>'USD','scope'=>'isolated database only'],JSON_PRETTY_PRINT);
}finally{wc_get_product($id)->delete(true);}
