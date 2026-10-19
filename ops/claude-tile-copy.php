<?php
defined('ABSPATH') || exit;
$targets=array(30=>'Claude Pro',31=>'Claude Max 5x');$rows=array();
foreach(array(30,31,32) as $id){$p=wc_get_product($id);if(!$p){throw new RuntimeException('Expected product missing');}$rows[$id]=array('id'=>$id,'name'=>$p->get_name(),'sku'=>$p->get_sku(),'brief_exists'=>metadata_exists('post',$id,'_instapass_brief'),'brief'=>(string)$p->get_meta('_instapass_brief'));}
if(getenv('IP_CLAUDE_TILE_APPLY')==='yes'&&($rows[30]['sku']!=='claude-pro'||$rows[31]['sku']!=='claude-max-gift-x5'||$rows[32]['sku']!=='claude-max-gift-x20')){throw new RuntimeException('Product identity differs; stop before writing');}
if(getenv('IP_CLAUDE_TILE_APPLY')==='yes'){
 $old20=$rows[32]['brief'];$phrase='25% additional limits';
 foreach($targets as $id=>$label){$before=$rows[$id]['brief'];$after=strpos($before,$phrase)!==false?$before:($before===''?$phrase:rtrim($before).' · '.$phrase);update_post_meta($id,'_instapass_brief',$after);if((string)get_post_meta($id,'_instapass_brief',true)!==$after){throw new RuntimeException('Tile copy failed to persist');}$rows[$id]['brief']=$after;}
 if((string)get_post_meta(32,'_instapass_brief',true)!==$old20){throw new RuntimeException('Excluded Max 20x changed');}
}
echo wp_json_encode(array('scope'=>'Claude Pro and Claude Max 5x only; wording supplied by owner','apply'=>getenv('IP_CLAUDE_TILE_APPLY')==='yes','products'=>array_values($rows)),JSON_PRETTY_PRINT);
