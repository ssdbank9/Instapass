<?php
$ids=get_users(['role'=>'administrator','number'=>1,'fields'=>'ID']);wp_set_current_user((int)$ids[0]);
if(!function_exists('woocommerce_wp_text_input'))require WC_ABSPATH.'includes/admin/wc-meta-box-functions.php';
$products=wc_get_products(['limit'=>1,'status'=>'publish','type'=>'simple']);
if(!$products)throw new RuntimeException('No simple product to inspect');
$GLOBALS['product_object']=$products[0];
ob_start();instapass_control_gbp_fields();$html=ob_get_clean();
foreach(['_instapass_gbp_regular_price','_instapass_gbp_sale_price','_instapass_gbp_discount'] as $key)if(strpos($html,'id="'.$key.'"')===false)throw new RuntimeException('Missing GBP field');
$orders=wc_get_orders(['limit'=>1,'paginate'=>true]);
echo wp_json_encode(['plugin_version'=>INSTAPASS_CONTROL_VERSION,'gbp_editor_fields'=>3,'customer_currency_switching'=>false,'store_currency'=>get_option('woocommerce_currency'),'published_products'=>(int)wp_count_posts('product')->publish,'order_count'=>$orders->total,'scope'=>'read-only live editor rendering'],JSON_PRETTY_PRINT);
