<?php
if(!defined('WP_CLI')||!WP_CLI||getenv('IP4_APPLY')!=='yes')throw new RuntimeException('Guarded CLI only');
$path=getenv('IP4_BACKUP');if(!$path||!str_starts_with($path,'/opt/instapass/recovery/'))throw new RuntimeException('Protected backup required');$b=json_decode(file_get_contents($path),true,512,JSON_THROW_ON_ERROR);add_filter('pre_wp_mail','__return_true',999);
foreach($b['target'] as $id=>$v){$p=wc_get_product($id);$p->set_regular_price($v['regular']);$p->set_sale_price($v['sale']);$p->set_price($v['price']);$p->set_date_on_sale_from($v['from']);$p->set_date_on_sale_to($v['to']);$p->set_description($v['description']);foreach($v['meta'] as $k=>$m)if($m['exists'])$p->update_meta_data($k,$m['value']);else $p->delete_meta_data($k);$p->save();wc_delete_product_transients($id);}
foreach($b['options'] as $k=>$v)if($v===false)delete_option($k);else update_option($k,$v,false);
foreach(instapass_promo_options() as $slot=>$key){$r=instapass_write_promo(instapass_promo_config($slot),$slot);if(is_wp_error($r))throw new RuntimeException($r->get_error_message());}
echo 'Scoped pricing rollback complete; customer orders untouched.';
