<?php
if(getenv('INSTAPASS_ISOLATED_TEST')!=='yes')throw new RuntimeException('Isolated clone required');
add_filter('pre_wp_mail','__return_true',999);
update_option('home','http://127.0.0.1:19058');update_option('siteurl','http://127.0.0.1:19058');
update_option('active_plugins',['woocommerce/woocommerce.php','instapass-control-center/instapass-control-center.php']);
update_option('instapass_pkr_rate','280.00');delete_option('instapass_control_bulk_lock');update_option('ipcb_history',[]);
foreach(['QA-BULK-A'=>['19.99','22.00'],'QA-BULK-B'=>['1.99','2.00']] as $sku=>$prices){$p=wc_get_product(wc_get_product_id_by_sku($sku));$p->set_regular_price($prices[0]);$p->set_sale_price('');$p->set_price($prices[0]);$p->set_date_on_sale_from(null);$p->set_date_on_sale_to(null);$p->update_meta_data('_instapass_gbp_regular_price',$prices[1]);$p->delete_meta_data('_instapass_gbp_sale_price');$p->delete_meta_data('_instapass_pkr_regular_price');$p->delete_meta_data('_instapass_pkr_sale_price');$p->save();}
echo "Browser clone initialized.\n";
