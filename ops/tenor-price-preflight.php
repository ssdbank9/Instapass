<?php
defined('ABSPATH')||exit;
$rows=[];foreach([29,30,31,32] as $id){$p=wc_get_product($id);$rows[]=['id'=>$id,'name'=>$p->get_name(),'sku'=>$p->get_sku(),'type'=>$p->get_type(),'status'=>$p->get_status(),'price'=>$p->get_price('edit'),'regular'=>$p->get_regular_price('edit'),'sale'=>$p->get_sale_price('edit'),'stock'=>$p->get_stock_status(),'manage_stock'=>$p->get_manage_stock(),'quantity'=>$p->get_stock_quantity(),'features'=>instapass_product_features($p),'family'=>$p->get_meta('_instapass_tenor_family'),'brief'=>$p->get_meta('_instapass_brief'),'permalink'=>$p->get_permalink()];}
echo wp_json_encode($rows,JSON_PRETTY_PRINT);
