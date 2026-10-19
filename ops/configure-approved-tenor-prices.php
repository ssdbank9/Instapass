<?php
defined('ABSPATH')||exit;
$sourcePlus=wc_get_product(29);$sourceMax=wc_get_product(31);
if(!$sourcePlus||$sourcePlus->get_sku()!=='chatgpt-plus'||!$sourceMax||$sourceMax->get_sku()!=='claude-max-gift-x5'||!$sourcePlus->is_type('simple')||!$sourceMax->is_type('simple'))throw new RuntimeException('Source identity mismatch');
$tag='20261019-approved-terms-v1';$mode=getenv('IP_PRICE_MODE')?:'read';$apply=in_array($mode,['stage','publish'],true);
$specs=[
 ['sku'=>'instapass-chatgpt-plus-annual','source'=>29,'name'=>'ChatGPT Plus - 1 year','duration'=>'1 year','usd'=>'173.33','pkr'=>'47500.00','family'=>'ChatGPT Plus','note'=>'One year of access, paid upfront. USD full-term total: $173.33. With the eligible 10% bundle coupon, this item costs about $156 for the year ($13 per month equivalent). The coupon requires four different product/duration selections. Renewal requires a new confirmation and payment.'],
 ['sku'=>'instapass-claude-max-5x-6months','source'=>31,'name'=>'Claude Max 5× - 6 months','duration'=>'6 months','usd'=>'432.00','pkr'=>'','family'=>'Claude Max 5x','note'=>'Six months of access, paid upfront. USD full-term total: $432 ($72 per month equivalent). No automatic monthly charge.'],
 ['sku'=>'instapass-claude-max-5x-annual','source'=>31,'name'=>'Claude Max 5× - 1 year','duration'=>'1 year','usd'=>'833.33','pkr'=>'','family'=>'Claude Max 5x','note'=>'One year of access, paid upfront. USD full-term total: $833.33. With the eligible 10% bundle coupon, this item costs about $750 for the year ($62.50 per month equivalent). The coupon requires four different product/duration selections. No automatic monthly charge.']
];
$rows=[];foreach($specs as $s){
 $id=wc_get_product_id_by_sku($s['sku']);$p=$id?wc_get_product($id):false;
 if($p&&$p->get_meta('_instapass_price_release')!==$tag)throw new RuntimeException('SKU collision; no overwrite');
 if($apply&&!$p){$src=wc_get_product($s['source']);$p=new WC_Product_Simple();
  $p->set_name($s['name']);$p->set_sku($s['sku']);$p->set_status('draft');$p->set_catalog_visibility('visible');$p->set_virtual($src->is_virtual());
  $p->set_description('<p>'.esc_html($src->get_meta('_instapass_brief')).'</p><p>'.esc_html($s['note']).'</p><p>Private access. Activation instructions are sent to your chosen activation email, or your order email if no separate activation email is provided.</p><p>Use one eligible bundle coupon per order. See our <a href="'.esc_url(home_url('/refund-policy/')).'">refund policy</a> for delivery and access issues.</p>');$p->set_short_description('<p>'.esc_html($s['note']).'</p>');
  $p->set_image_id($src->get_image_id());$p->set_gallery_image_ids($src->get_gallery_image_ids());$p->set_category_ids($src->get_category_ids());$p->set_tag_ids($src->get_tag_ids());$p->set_tax_status($src->get_tax_status());$p->set_tax_class($src->get_tax_class());
  $p->set_manage_stock(false);$p->set_stock_status($src->get_stock_status());$p->set_reviews_allowed(true);$p->set_regular_price($s['usd']);$p->set_price($s['usd']);
  foreach(instapass_product_features($src) as $k=>$v)$p->update_meta_data('_instapass_'.$k,is_bool($v)?($v?'yes':'no'):$v);
  $p->update_meta_data('_instapass_validity',$s['duration']);$p->update_meta_data('_instapass_tenor_family',$s['family']);$p->update_meta_data('_instapass_tenor_group',hash('sha256',strtolower($s['family'])));
  $p->update_meta_data('_instapass_brief',$src->get_meta('_instapass_brief'));$p->update_meta_data('_instapass_exact_usd','yes');$p->update_meta_data('_instapass_price_release',$tag);
  if($s['pkr']!=='')$p->update_meta_data('_instapass_pkr_regular_price',$s['pkr']);
  $p->save();
 }
 if($p){if($p->get_regular_price('edit')!=$s['usd']||$p->get_sale_price('edit')!==''||$p->get_meta('_instapass_validity')!==$s['duration']||(string)$p->get_meta('_instapass_pkr_regular_price')!==$s['pkr'])throw new RuntimeException('Prepared offer changed; review before publication');
  if($mode==='publish'&&$p->get_status()==='draft'){$p->set_status('publish');$p->save();}
  $rows[]=['id'=>$p->get_id(),'sku'=>$p->get_sku(),'status'=>$p->get_status(),'name'=>$p->get_name(),'usd'=>$p->get_price('edit'),'pkr_override'=>$p->get_meta('_instapass_pkr_regular_price'),'duration'=>ip_tenor_duration($p),'url'=>$p->get_permalink()];
 }else{$rows[]=['sku'=>$s['sku'],'status'=>'not_created'];}
}
if($mode==='publish'){
 if(!in_array($sourceMax->get_sale_price('edit'),['74.99','75'],true))throw new RuntimeException('Monthly Max price changed since approval');
 $sourceMax->set_sale_price('75');$sourceMax->set_price('75');$sourceMax->update_meta_data('_instapass_validity','1 month');$sourceMax->update_meta_data('_instapass_tenor_family','Claude Max 5x');$sourceMax->update_meta_data('_instapass_tenor_group',hash('sha256','claude max 5x'));$sourceMax->update_meta_data('_instapass_exact_usd','yes');$sourceMax->save();
 $sourcePlus->update_meta_data('_instapass_tenor_family','ChatGPT Plus');$sourcePlus->update_meta_data('_instapass_tenor_group',hash('sha256','chatgpt plus'));
 if(getenv('IP_PLUS_MONTHLY')==='yes')$sourcePlus->update_meta_data('_instapass_validity','1 month');
 $sourcePlus->save();
}
echo wp_json_encode(['mode'=>$mode,'offers'=>$rows,'monthly_max_usd'=>wc_get_product(31)->get_price('edit'),'monthly_plus_duration'=>ip_tenor_duration(wc_get_product(29)),'excluded_max20'=>wc_get_product(32)->get_price('edit')],JSON_PRETTY_PRINT);