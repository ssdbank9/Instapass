<?php
if(!defined('WP_CLI')||!WP_CLI)throw new RuntimeException('CLI only.');
$file=getenv('IPL_BACKUP');if(!$file||strpos($file,'/opt/instapass/recovery/')!==0||!is_readable($file))throw new RuntimeException('Protected snapshot required.');
$snap=json_decode(file_get_contents($file),true);if(empty($snap['products']))throw new RuntimeException('Snapshot invalid.');
if(class_exists('Instapass_Notifications'))remove_action('woocommerce_update_product',array('Instapass_Notifications','restocked'));
$metakeys=array('_instapass_brand','_instapass_tenor_family','_instapass_tenor_group','_instapass_validity','_instapass_brief','_instapass_access_type','_instapass_activation','_instapass_renewable','_ip_launch_family','_instapass_credit_amount','_instapass_credit_expiry','_ip_account_allocation');
foreach($snap['products'] as $id=>$row){$p=wc_get_product($id);if(!$p)throw new RuntimeException('Original product missing.');$data=$row['data'];foreach(array('name','status','catalog_visibility','description','short_description') as $key)$p->{'set_'.$key}($data[$key]);$old=array();foreach($data['meta_data'] as $meta)$old[$meta['key']]=$meta['value'];foreach($metakeys as $key){if(array_key_exists($key,$old))$p->update_meta_data($key,$old[$key]);else $p->delete_meta_data($key);}$p->save();wc_delete_product_transients($id);}
foreach(wc_get_products(array('limit'=>-1,'status'=>array('publish','draft'),'meta_key'=>'_ip_launch_family','meta_compare'=>'EXISTS')) as $p)if(!isset($snap['products'][$p->get_id()])){$p->set_status('draft');$p->save();}
if(isset($snap['groups_before']))update_option('instapass_launch_groups',$snap['groups_before'],false);else delete_option('instapass_launch_groups');echo 'Scoped catalog rollback complete; historical orders were not restored or modified.';
