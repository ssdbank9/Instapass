<?php
if (!defined('ABSPATH')) exit;
$theme=wp_get_theme();
$gateways=[];
foreach(WC()->payment_gateways()->payment_gateways() as $id=>$g){$gateways[$id]=['enabled'=>$g->enabled,'title'=>$g->title,'instructions_configured'=>trim((string)$g->get_option('instructions',''))!==''];}
$types=[]; $priced=0; $non99=0; $stock=[];
foreach(wc_get_products(['limit'=>-1,'status'=>'publish','return'=>'objects']) as $p){$t=$p->get_type();$types[$t]=($types[$t]??0)+1;$s=$p->get_stock_status();$stock[$s]=($stock[$s]??0)+1;$v=$p->get_price('edit');if($v!==''){$priced++;if(((int)round((float)$v*100))%100!==99)$non99++;}}
$checkout=get_post(wc_get_page_id('checkout'));
echo wp_json_encode(['time_utc'=>gmdate('c'),'wordpress'=>get_bloginfo('version'),'woocommerce'=>WC_VERSION,'theme'=>['name'=>$theme->get('Name'),'version'=>$theme->get('Version'),'stylesheet'=>get_stylesheet()],'store_currency'=>get_option('woocommerce_currency'),'price_decimals'=>get_option('woocommerce_price_num_decimals'),'taxes'=>get_option('woocommerce_calc_taxes'),'prices_include_tax'=>get_option('woocommerce_prices_include_tax'),'geolocation_default'=>get_option('woocommerce_default_customer_address'),'maxmind_key_configured'=>trim((string)(get_option('woocommerce_maxmind_geolocation_settings',[])['license_key']??''))!=='','checkout_blocks'=>$checkout?has_block('woocommerce/checkout',$checkout):null,'products'=>['types'=>$types,'priced'=>$priced,'non_99_current_prices'=>$non99,'stock'=>$stock],'order_count'=>wc_get_orders(['limit'=>1,'paginate'=>true])->total,'gateways'=>$gateways],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
