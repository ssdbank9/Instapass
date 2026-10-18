<?php
if(getenv('INSTAPASS_ISOLATED_TEST')!=='yes')throw new RuntimeException('Isolated clone required');
add_filter('pre_wp_mail','__return_true',999);
delete_option('instapass_fixture_mail');
update_option('home','http://127.0.0.1:19041');update_option('siteurl','http://127.0.0.1:19041');
update_option('active_plugins',['woocommerce/woocommerce.php','instapass-control-center/instapass-control-center.php','instapass-notifications/instapass-notifications.php']);
$username='instapass-alert-qa';$password=wp_generate_password(32,true,true);
$uid=username_exists($username);
if(!$uid)$uid=wp_insert_user(['user_login'=>$username,'user_pass'=>$password,'user_email'=>'admin-fixture@example.invalid','role'=>'administrator']);
else wp_set_password($password,$uid);
$p=new WC_Product_Simple();$p->set_name('Restock browser fixture');$p->set_slug('restock-browser-fixture');$p->set_sku('QA-RESTOCK-'.time());$p->set_regular_price('19.99');$p->set_stock_status('outofstock');$p->set_status('publish');$p->update_meta_data('_instapass_restock_date','2026-12-01');$p->update_meta_data('_instapass_restock_note','Estimated availability');$p->save();
require_once WP_PLUGIN_DIR.'/instapass-notifications/instapass-notifications.php';
update_option(Instapass_Notifications::OPTION,['enabled'=>1,'verified'=>Instapass_Notifications::sender(),'daily_limit'=>100]);
global $wpdb;$wpdb->query('TRUNCATE TABLE '.$wpdb->prefix.'instapass_mail_budget');
file_put_contents('/tmp/notifications-browser-private.json',wp_json_encode(['username'=>$username,'password'=>$password,'id'=>$p->get_id(),'url'=>get_permalink($p->get_id())]));
echo "Isolated browser fixture prepared.\n";
