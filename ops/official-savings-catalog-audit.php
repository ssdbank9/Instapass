<?php
defined('ABSPATH')||exit;
$out=[];foreach(wc_get_products(['limit'=>-1,'status'=>['publish','draft','private','pending','future'],'orderby'=>'ID','order'=>'ASC']) as $p)$out[$p->get_id()]=hash('sha256',wp_json_encode($p->get_data()));echo wp_json_encode($out,JSON_PRETTY_PRINT)."\n";
