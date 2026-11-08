<?php
if(!defined('WP_CLI')||!WP_CLI||getenv('IPL_APPLY')!=='yes'||!function_exists('ipl_groups'))throw new RuntimeException('Guarded CLI migration only.');
if(ipl_groups())throw new RuntimeException('Grouped catalog already configured; refusing to overwrite owner changes.');
$spec=array(
 array('key'=>'claude-pro','name'=>'Claude Pro','brand'=>'claude','caption'=>'An AI assistant for writing, analysis and everyday work.','offers'=>array(1=>30,6=>0,12=>142)),
 array('key'=>'claude-max','name'=>'Claude Max 5×','brand'=>'claude','caption'=>'Choose a term for Claude Max at the 5× tier.','offers'=>array(1=>31,6=>139,12=>140)),
 array('key'=>'claude-ultra','name'=>'Claude Ultra 20×','brand'=>'claude','caption'=>'Our Claude 20× tier offering, with three access durations.','offers'=>array(1=>32,6=>0,12=>0)),
 array('key'=>'chatgpt','name'=>'ChatGPT Plus','brand'=>'openai','caption'=>'Write, brainstorm and explore ideas with ChatGPT Plus.','offers'=>array(1=>29,6=>0,12=>138)),
 array('key'=>'canva','name'=>'Canva Pro','brand'=>'canva','caption'=>'Create presentations, social graphics and visual designs.','offers'=>array(12=>55)),
);
function ipl_migrate_orders_hash(){return hash('sha256',wp_json_encode(array_map(function($o){return $o->get_data();},wc_get_orders(array('limit'=>-1,'orderby'=>'ID','order'=>'ASC')))));}
function ipl_migrate_prices($p){$a=array($p->get_regular_price('edit'),$p->get_sale_price('edit'),$p->get_price('edit'),$p->get_stock_status('edit'),$p->get_stock_quantity('edit'),$p->get_manage_stock('edit'),$p->get_meta('_ip_offer_coupons'));foreach(array('pkr','gbp') as $c)foreach(array('regular','sale') as $t)$a[]=$p->get_meta('_instapass_'.$c.'_'.$t.'_price');return $a;}
$original=array();foreach(wc_get_products(array('status'=>array('publish','draft','private','pending'),'limit'=>-1)) as $p)$original[$p->get_id()]=array('data'=>$p->get_data(),'prices'=>ipl_migrate_prices($p));
$orders=ipl_migrate_orders_hash();$backup=getenv('IPL_BACKUP');if(!$backup||strpos($backup,'/opt/instapass/recovery/')!==0)throw new RuntimeException('Protected backup path required.');
file_put_contents($backup,wp_json_encode(array('products'=>$original,'orders_hash'=>$orders),JSON_PRETTY_PRINT));chmod($backup,0600);
// Publishing/renaming is a catalog operation, not a customer notification request.
if(class_exists('Instapass_Notifications'))remove_action('woocommerce_update_product',array('Instapass_Notifications','restocked'));
$groups=array();$keep=array();$created=array();
foreach($spec as $g){$parent_id=(int)reset($g['offers']);$source=wc_get_product($parent_id);if(!$source)throw new RuntimeException('Missing source offer.');$sourcecats=$source->get_category_ids();$group=$g;$group['parent']=$parent_id;$group['enabled']=true;$group['offers']=array();
 foreach($g['offers'] as $months=>$id){$p=$id?wc_get_product($id):new WC_Product_Simple();if(!$p||!$p->is_type('simple'))throw new RuntimeException('Expected simple source offers.');
  $p->set_name($id===$parent_id?$g['name']:$g['name'].' — '.ipl_label($months));$p->set_status('publish');$p->set_catalog_visibility($id===$parent_id?'visible':'hidden');
  $p->set_description('<p>'.esc_html($g['caption']).'</p><p>Choose your access duration. Pricing is being prepared for this offer. Purchases will open after pricing is confirmed.</p>');$p->set_short_description('<p>'.esc_html($g['caption']).'</p>');
  if(!$id){$p->set_sku('launch-'.$g['key'].'-'.$months.'months');$p->set_virtual(true);$p->set_reviews_allowed(true);$p->set_category_ids($sourcecats);$p->set_stock_status('instock');}
  foreach(array('_instapass_brand'=>$g['brand'],'_instapass_tenor_family'=>$g['name'],'_instapass_tenor_group'=>hash('sha256',strtolower($g['name'])),'_instapass_validity'=>$months===12?'1 year':$months.' month'.($months===1?'':'s'),'_instapass_brief'=>$g['caption'],'_instapass_access_type'=>'Private','_instapass_activation'=>'On customer email','_instapass_renewable'=>'yes','_ip_launch_family'=>$g['key']) as $key=>$value)$p->update_meta_data($key,$value);
  $new_id=$p->save();if(!$id)$created[]=$new_id;$keep[]=$new_id;$group['offers'][$months]=array('id'=>$new_id,'ready'=>false);wc_delete_product_transients($new_id);
 }unset($group['brand']);$groups[]=$group;
}
foreach($original as $id=>$snapshot){if(in_array($id,$keep,true))continue;$p=wc_get_product($id);if($p->get_status()==='publish'){wp_update_post(array('ID'=>$id,'post_status'=>'draft'));wc_delete_product_transients($id);}}
update_option('instapass_launch_groups',$groups,false);
foreach($original as $id=>$snapshot){$p=wc_get_product($id);if(ipl_migrate_prices($p)!==$snapshot['prices'])throw new RuntimeException('Stored price/stock/coupon preservation failed: '.$id);}
if($orders!==ipl_migrate_orders_hash())throw new RuntimeException('Historical orders changed.');
$public=wc_get_products(array('status'=>'publish','visibility'=>'visible','limit'=>-1,'return'=>'ids'));
if(count($groups)!==5||count($keep)!==13||count($created)!==4||count($public)!==5)throw new RuntimeException('Launch catalog shape assertion failed.');
file_put_contents(dirname($backup).'/launch-map.json',wp_json_encode(array('groups'=>$groups,'created'=>$created,'original_ids'=>array_keys($original)),JSON_PRETTY_PRINT));
echo wp_json_encode(array('result'=>'pass','families'=>5,'duration_offers'=>13,'created_unpriced_offers'=>4,'visible_products'=>5,'stored_prices_stock_preserved'=>true,'orders_preserved'=>true,'groups'=>$groups),JSON_PRETTY_PRINT);
