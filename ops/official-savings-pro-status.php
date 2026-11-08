<?php
defined('ABSPATH')||exit;
$p=wc_get_product(30);echo wp_json_encode(['id'=>$p->get_id(),'sku'=>$p->get_sku(),'regular'=>$p->get_regular_price('edit'),'sale'=>$p->get_sale_price('edit'),'duration'=>ip_tenor_duration($p),'family'=>$p->get_meta('_instapass_tenor_family'),'group'=>$p->get_meta('_instapass_tenor_group')],JSON_PRETTY_PRINT)."\n";
