<?php
/** Owner-approved additional offer only; never updates an existing product. */
defined('ABSPATH')||exit;
$sku='claude-credits-100';
if(wc_get_product_id_by_sku($sku))throw new RuntimeException('SKU already exists; inspect before repeating');
global $wpdb;
if($wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type='product' AND post_title=%s AND post_status NOT IN ('trash','auto-draft') LIMIT 1",'Claude Credits')))throw new RuntimeException('Named offer already exists; inspect before repeating');
$source=wc_get_product(34);
if(!$source||$source->get_sku('edit')!=='claude-code-credit-100'||!file_exists(get_template_directory().'/assets/logos/claude.svg'))throw new RuntimeException('Claude source/logo mismatch');
$state=function(){global $wpdb;return [
 'catalog'=>hash('sha256',wp_json_encode($wpdb->get_results("SELECT * FROM {$wpdb->posts} WHERE post_type IN ('product','product_variation') ORDER BY ID",ARRAY_A))),
 'meta'=>hash('sha256',wp_json_encode($wpdb->get_results("SELECT m.* FROM {$wpdb->postmeta} m INNER JOIN {$wpdb->posts} p ON m.post_id=p.ID WHERE p.post_type IN ('product','product_variation') ORDER BY m.meta_id",ARRAY_A))),
 'coupons'=>hash('sha256',wp_json_encode($wpdb->get_results("SELECT ID,post_title,post_status FROM {$wpdb->posts} WHERE post_type='shop_coupon' ORDER BY ID",ARRAY_A))),
 'orders'=>wc_orders_count('pending')+wc_orders_count('processing')+wc_orders_count('on-hold')+wc_orders_count('completed')+wc_orders_count('cancelled')+wc_orders_count('refunded')+wc_orders_count('failed')
];};
$before=$state();
$p=new WC_Product_Simple();
$p->set_name('Claude Credits');$p->set_slug('claude-credits');$p->set_sku($sku);$p->set_status('draft');$p->set_catalog_visibility('visible');$p->set_virtual(true);$p->set_downloadable(false);$p->set_sold_individually(false);$p->set_reviews_allowed(true);$p->set_regular_price('29.99');$p->set_price('29.99');$p->set_manage_stock(false);$p->set_stock_status('instock');$p->set_backorders('no');$p->set_category_ids($source->get_category_ids());
$p->set_short_description('<p>$100 in Claude credits for $29.99. Delivery instructions are emailed after payment verification.</p>');
$p->set_description('<p>Get $100 in Claude credits for $29.99.</p><p>Delivery instructions are sent by email after your payment has been verified. Applicable taxes are covered by Instapass, with no extra tax at checkout.</p>');
foreach(['_instapass_brand'=>'claude','_instapass_plan'=>'$100 credits','_instapass_brief'=>'$100 in Claude credits','_instapass_validity'=>'','_instapass_access_type'=>'Private','_instapass_activation'=>'Instructions by email after payment verification','_instapass_renewable'=>'no','_instapass_tenor_family'=>'','_ip_offer_coupons'=>'yes','_ip_account_allocation'=>'no','_ip_offer_managed'=>'yes','_instapass_owner_release'=>'20261020-claude-credits-100'] as $key=>$value)$p->update_meta_data($key,$value);
$id=$p->save();
// Capture only existing records again: the added offer is the sole catalog change.
$after_catalog=hash('sha256',wp_json_encode($wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE post_type IN ('product','product_variation') AND ID<>%d ORDER BY ID",$id),ARRAY_A)));
$after_meta=hash('sha256',wp_json_encode($wpdb->get_results($wpdb->prepare("SELECT m.* FROM {$wpdb->postmeta} m INNER JOIN {$wpdb->posts} p ON m.post_id=p.ID WHERE p.post_type IN ('product','product_variation') AND p.ID<>%d ORDER BY m.meta_id",$id),ARRAY_A)));
if($after_catalog!==$before['catalog']||$after_meta!==$before['meta']){throw new RuntimeException('Existing catalog changed; new offer remains draft for review');}
$p->set_status('publish');$p->save();wc_delete_product_transients($id);
$p=wc_get_product($id);
$after=$state();
if($after['orders']!==$before['orders']||$after['coupons']!==$before['coupons'])throw new RuntimeException('Order/coupon state changed; inspect');
if($p->get_name()!=='Claude Credits'||$p->get_regular_price('edit')!=='29.99'||$p->get_price('edit')!=='29.99'||$p->get_sale_price('edit')!==''||$p->get_status()!=='publish'||!$p->is_purchasable()||instapass_product_logo($p)!=='claude.svg')throw new RuntimeException('Published offer verification failed');
echo wp_json_encode(['id'=>$id,'name'=>$p->get_name(),'url'=>$p->get_permalink(),'usd'=>$p->get_price('edit'),'pkr'=>instapass_cc_amount('29.99',$p,'price','PKR'),'gbp'=>instapass_cc_amount('29.99',$p,'price','GBP'),'logo'=>instapass_product_logo($p),'stock'=>$p->get_stock_status(),'coupons'=>ip_offer_coupon_allowed($p),'existing_catalog_unchanged'=>true,'orders_unchanged'=>$after['orders']===$before['orders'],'order_count'=>$after['orders'],'coupons_unchanged'=>true],JSON_PRETTY_PRINT).PHP_EOL;