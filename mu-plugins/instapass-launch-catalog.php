<?php
/**
 * Plugin Name: Instapass Grouped Launch Catalog
 * Description: One product tile with independently managed duration offers and explicit price approval.
 * Version: 1.4.0
 */
defined('ABSPATH') || exit;
function ipl_groups(){return (array)get_option('instapass_launch_groups',array());}
function ipl_offer($id){foreach(ipl_groups() as $g)foreach($g['offers'] as $m=>$o)if((int)$o['id']===(int)$id)return array('group'=>$g,'months'=>(int)$m,'ready'=>!empty($o['ready']));return null;}
function ipl_public(){return !is_admin()&&!(defined('WP_CLI')&&WP_CLI)&&!(defined('REST_REQUEST')&&REST_REQUEST&&current_user_can('manage_woocommerce'));}
function ipl_ready($p){$o=ipl_offer($p->get_id());return $o&&!empty($o['group']['enabled'])&&$o['ready']&&$p->get_status()==='publish';}
function ipl_label($m){return (int)$m===1?'1 month':((int)$m===12?'12 months':'6 months');}
function ipl_credit($g){return ($g['type']??'subscription')==='credits';}
function ipl_choice_label($g,$key){return ip_om_label($g,$key);}

// Credits are one-time balance purchases; do not describe their price as a monthly subscription.
function ipl_credit_price_html($p,$face){
 $currency=get_woocommerce_currency();$full=(int)round(wc_get_price_to_display($p)*100,0,PHP_ROUND_HALF_UP);$money=static fn($n)=>wc_price($n/100,['currency'=>$currency]);
 $h='<span class="ipl-credit-pricing" data-credit-face="'.(int)$face.'" data-credit-price-cents="'.$full.'">';
 if(in_array($currency,['USD','PKR'],true)){
  $baseline=$currency==='USD'?(int)$face*100:Instapass_Control_Money::pkr_unit((int)$face*100,instapass_control_rate())*100;
  if($baseline>$full)$h.='<span class="ip-offer-saving-now">Save '.(int)round(100*($baseline-$full)/$baseline).'% vs credit face value</span>';
 }
 $rates=function_exists('ip_tenor_bundle_rates')?ip_tenor_bundle_rates($p,['full'=>$full,'months'=>1,'currency'=>$currency]):[];$best=null;foreach($rates as $rate)if(!$best||$rate['full']<$best['full'])$best=$rate;
 if($best)$h.='<span class="ip-offer-savings"><span class="ip-offer-saving-total">Additional '.esc_html($best['percent']).'% off with '.esc_html($best['code']).'<small>'.esc_html($best['tools'].' eligible items required.').'</small></span></span>';
 $h.='<span class="ipl-credit-primary">'.$money($full).'</span><span class="ipl-credit-payment">One-time credit purchase</span><span class="ip-term-upfront">For $'.(int)$face.' in credits</span>';
 $h.=apply_filters('ipl_credit_price_note','',$p,$face);return $h.'</span>';
}
add_filter('woocommerce_get_price_html',function($html,$p){
 if(!ipl_public()||!function_exists('ip_tenor_presentation_context')||!ip_tenor_presentation_context())return $html;
 $o=ipl_offer($p->get_id());return $o&&ipl_credit($o['group'])&&ipl_ready($p)&&$p->get_price()!==''?ipl_credit_price_html($p,$o['months']):$html;
},980,2);

// Preserve raw stored prices for the owner's later pricing pass, but never sell a held offer.
add_filter('woocommerce_is_purchasable',function($yes,$p){return ipl_groups()?($yes&&ipl_ready($p)):$yes;},999,2);
foreach(array('price','regular_price','sale_price') as $field)add_filter('woocommerce_product_get_'.$field,function($value,$p){return ipl_groups()&&ipl_public()&&!ipl_ready($p)?'':$value;},999,2);
add_filter('woocommerce_get_price_html',function($html,$p){return ipl_groups()&&ipl_public()&&!ipl_ready($p)?'<span class="ipl-pending">Pricing coming soon</span>':$html;},999,2);
add_filter('woocommerce_structured_data_product',function($data,$p){if(ipl_groups()&&!ipl_ready($p))unset($data['offers']);return $data;},999,2);
add_action('woocommerce_check_cart_items',function(){if(!ipl_groups()||!WC()->cart)return;foreach(WC()->cart->get_cart() as $item)if(!ipl_ready($item['data'])){wc_add_notice('This offer is awaiting its launch price. Please remove it from your basket.','error');break;}});
add_filter('woocommerce_get_item_data',function($rows,$item){$o=isset($item['data'])?ipl_offer($item['data']->get_id()):null;if($o&&ipl_credit($o['group'])){$rows[]=array('key'=>'Credit balance','value'=>'$'.$o['months']);$rows[]=array('key'=>'Credit expiry','value'=>apply_filters('ipl_credit_order_expiry','Monthly',$o['group']));}return $rows;},40,2);
add_action('woocommerce_checkout_create_order_line_item',function($item,$key,$values){$o=isset($values['data'])?ipl_offer($values['data']->get_id()):null;if($o&&ipl_credit($o['group'])){$item->add_meta_data('Credit balance','$'.$o['months'],true);$item->add_meta_data('Credit expiry',apply_filters('ipl_credit_order_expiry','Monthly',$o['group']),true);}},40,3);

function ipl_card($g,$detail=false,$selected=0){
 $representative=ip_om_representative($g);if(!$representative||$representative->get_status()!=='publish'||empty($g['enabled']))return '';
 $offers=array();foreach($g['offers'] as $months=>$item){$p=wc_get_product((int)$item['id']);if($p&&$p->get_status()==='publish')$offers[(int)$months]=$p;}
 if(!$offers)return '';
 $selected=isset($offers[$selected])?$selected:(int)array_key_first($offers);$uid=wp_unique_id('ipl-duration-');
 $logo=function_exists('instapass_product_logo')?instapass_product_logo($representative):'';
 $custom=$representative->get_image_id()?wp_get_attachment_image_url($representative->get_image_id(),'medium'):'';
 $url=$custom?:($logo?get_template_directory_uri().'/assets/logos/'.$logo:'');
 $html='<article class="ipl-card'.($detail?' ipl-card--detail':'').'" data-launch-family="'.esc_attr($g['key']).'">';
 if(!$detail){$brandclass=$logo?'ip-brand-'.sanitize_html_class(pathinfo($logo,PATHINFO_FILENAME)):'';$html.='<a class="ipl-visual" href="'.esc_url($representative->get_permalink()).'">'.($url?'<img class="'.esc_attr($brandclass).'" src="'.esc_url($url).'" width="80" height="80" loading="lazy" alt="'.esc_attr($g['name'].' logo').'">':'<span>'.esc_html($g['name']).'</span>').'</a><div class="ipl-body"><h2><a href="'.esc_url($representative->get_permalink()).'">'.esc_html($g['name']).'</a></h2><p class="ipl-caption">'.esc_html($g['caption']).'</p>';}
 else $html.='<div class="ipl-body">';
 if(ipl_credit($g)){
  $html.='<div class="ipl-credits"><label for="'.esc_attr($uid).'">Choose your credit balance</label><div class="ipl-credit-controls"><button type="button" data-credit-adjust="-25" aria-label="Decrease credits by 25 dollars">−</button><output for="'.esc_attr($uid).'" class="ipl-credit-value">$'.(int)$selected.' credits</output><button type="button" data-credit-adjust="25" aria-label="Increase credits by 25 dollars">+</button></div><input type="range" id="'.esc_attr($uid).'" min="50" max="500" step="25" value="'.(int)$selected.'" aria-valuetext="$'.(int)$selected.' credit balance" data-credit-range><div class="ipl-credit-limits"><span>$50</span><span>$500</span></div><p class="ipl-credit-expiry">'.esc_html(apply_filters('ipl_credit_expiry_label','Credits expire monthly',$g)).'</p></div>';
 }elseif(count($offers)===1){
  $html.='<p class="ipl-single-term">'.esc_html(ip_tenor_months(reset($offers))===12?'Annual plan':ipl_choice_label($g,$selected)).'</p>';
 }else{
  $html.='<fieldset class="ipl-durations"><legend>'.(count($offers)===1&&isset($offers[12])?'Annual plan':'Choose your duration').'</legend><div class="ipl-duration-options">';
  foreach($offers as $m=>$p)$html.='<label><input type="radio" name="'.esc_attr($uid).'" value="'.(int)$m.'" '.checked($m,$selected,false).'><span>'.esc_html(ipl_choice_label($g,$m)).'</span></label>';
  $html.='</div></fieldset>';
 }
 $html.='<div class="ipl-quotes" aria-live="polite">';
 foreach($offers as $m=>$p){$ready=ipl_ready($p)&&$p->get_price()!=='';$choice=ipl_choice_label($g,$m);$term=ipl_credit($g)?$choice.' · '.apply_filters('ipl_credit_expiry_label','Credits expire monthly',$g):$choice.' of access';$html.='<section class="ipl-quote" data-months="'.(int)$m.'"'.($m!==$selected?' hidden':'').' aria-label="'.esc_attr($choice.' offer').'">';
  if($ready){$html.='<div class="ipl-price">'.$p->get_price_html().'</div><p class="ipl-term">'.esc_html($term).' · Paid upfront</p>';if($p->is_purchasable()&&$p->is_in_stock())$html.='<a class="ipl-buy" href="'.esc_url(add_query_arg(array('add-to-cart'=>$p->get_id(),'ip_buy_now'=>1),wc_get_checkout_url())).'">Buy '.esc_html($choice).'</a><a class="ipl-basket" href="'.esc_url(add_query_arg(array('add-to-cart'=>$p->get_id(),'ip_add_more'=>1),wc_get_page_permalink('shop'))).'">Add to basket</a>';else $html.='<button class="ipl-buy" disabled>'.esc_html(!$p->is_in_stock()?'Out of stock':'Unavailable in this currency').'</button>';}
  else $html.=apply_filters('ipl_pending_quote_html','<div class="ipl-price ipl-pending">Pricing coming soon</div><p class="ipl-term">'.esc_html($term).' · Price to be announced</p><button class="ipl-buy" disabled>Coming soon</button>',$p,$g,$m);
  if(!$detail)$html.='<a class="ipl-details" href="'.esc_url($p->get_permalink()).'">View product details <span aria-hidden="true">↗</span></a>';
  $html.='</section>';
 }
 return $html.'</div><noscript><p>Choose an option to view its offer.</p><ul>'.implode('',array_map(function($p)use($g){return '<li><a href="'.esc_url($p->get_permalink()).'">'.esc_html(ipl_choice_label($g,ipl_offer($p->get_id())['months'])).'</a></li>';},$offers)).'</ul></noscript></div></article>';
}
function ipl_grid(){
 $html='';$search=trim((string)get_search_query());$current=is_product()?ipl_offer(get_queried_object_id()):null;
 foreach(ipl_groups() as $g){$p=ip_om_representative($g);if(!$p||empty($g['enabled']))continue;
  if($current&&$current['group']['key']===$g['key'])continue;
  if(is_product_category()&&!has_term(get_queried_object_id(),'product_cat',$p->get_id()))continue;
  if($search!==''&&stripos($g['name'].' '.$g['caption'],$search)===false)continue;
  $html.=ipl_card($g);
 }
 return '<div class="ipl-grid" aria-label="Products">'.($html?:'<p>No matching products. <a href="'.esc_url(wc_get_page_permalink('shop')).'">View all products</a></p>').'</div>';
}
add_filter('render_block_woocommerce/product-collection',function($html){return ipl_groups()?ipl_grid():$html;},999);
add_filter('render_block_core/paragraph',function($html,$parsed){return ipl_groups()&&strpos($parsed['attrs']['className']??'','ip-empty-note')!==false?'':$html;},999,2);
// A held product never shows old generated pricing, promotion badges or duplicate tenor cards.
add_filter('render_block_core/post-excerpt',function($html,$parsed,$block){if(!ipl_groups()||!is_product())return $html;$p=function_exists('instapass_block_product')?instapass_block_product($block):null;$o=$p?ipl_offer($p->get_id()):null;return $o?'<p class="ipl-product-intro">'.esc_html($o['group']['caption']).'</p>':$html;},999,3);
add_filter('render_block_woocommerce/product-price',function($html,$parsed,$block){if(!ipl_groups()||!is_product())return $html;$p=function_exists('instapass_block_product')?instapass_block_product($block):null;$o=$p?ipl_offer($p->get_id()):null;return $o?ipl_card($o['group'],true,$o['months']):$html;},999,3);
add_filter('render_block_woocommerce/add-to-cart-form',function($html,$parsed,$block){$p=function_exists('instapass_block_product')?instapass_block_product($block):null;return $p&&ipl_offer($p->get_id())?'':$html;},999,3);
add_filter('render_block_instapass/product-badges',function($html){return ipl_groups()?'':$html;},999);
add_action('wp_enqueue_scripts',function(){if(!ipl_groups())return;$base=content_url('mu-plugins/instapass-launch-catalog');wp_enqueue_style('ipl-catalog',$base.'.css',array(),(string)filemtime(__DIR__.'/instapass-launch-catalog.css'));wp_enqueue_script('ipl-catalog',$base.'.js',array(),(string)filemtime(__DIR__.'/instapass-launch-catalog.js'),true);});

add_action('admin_menu',function(){add_submenu_page('instapass-control','Product tiles & durations','Product Tiles','manage_woocommerce','instapass-product-tiles','ipl_admin');},40);
function ipl_admin(){
 if(!current_user_can('manage_woocommerce'))return;
 echo '<div class="wrap"><h1>Product tiles & durations</h1><p><a class="button button-primary" href="'.esc_url(admin_url('admin.php?page=instapass-product-manager')).'">Edit durations and prices in Product Manager</a></p><p>Each row below belongs to one storefront tile. Edit prices and stock using the offer editor. Approve a duration only after its full-term pricing is ready.</p><p><a class="button" href="'.esc_url(admin_url('admin.php?page=instapass-offers')).'">Products & Offers</a></p>';
 if(isset($_GET['saved']))echo '<div class="notice notice-success"><p>Product tiles saved.</p></div>';
 echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="ipl_save">';wp_nonce_field('ipl_save');
 echo '<input type="hidden" name="fingerprint" value="'.esc_attr(hash('sha256',wp_json_encode(ipl_groups()))).'">';
 foreach(ipl_groups() as $i=>$g){echo '<fieldset style="background:white;border:1px solid #ccd0d4;padding:20px;margin:20px 0;max-width:1000px"><legend><h2>'.esc_html($g['name']).'</h2></legend><p><label>Tile name <input name="groups['.$i.'][name]" value="'.esc_attr($g['name']).'" required></label> <label><input type="checkbox" name="groups['.$i.'][enabled]" value="1" '.checked(!empty($g['enabled']),true,false).'> Show this product tile</label></p><p><label>Description <input class="large-text" name="groups['.$i.'][caption]" value="'.esc_attr($g['caption']).'"></label></p><table class="widefat"><thead><tr><th>Duration</th><th>Offer price and stock</th><th>Allow pricing and purchases</th></tr></thead><tbody>';
  foreach($g['offers'] as $m=>$offer){$p=wc_get_product($offer['id']);echo '<tr><td>'.esc_html(ipl_choice_label($g,$m)).'</td><td><a href="'.esc_url(admin_url('admin.php?page=instapass-offers&offer='.(int)$offer['id'])).'">Edit this offer</a></td><td><label><input type="checkbox" name="groups['.$i.'][ready]['.(int)$m.']" value="1" '.checked(!empty($offer['ready']),true,false).'> Price reviewed and ready</label></td></tr>';}
  echo '</tbody></table></fieldset>';
 }
 submit_button('Save product tiles');echo '</form></div>';
}
add_action('admin_post_ipl_save',function(){
 if(!current_user_can('manage_woocommerce'))wp_die('Permission denied',403);check_admin_referer('ipl_save');$groups=ipl_groups();
 if(!hash_equals(hash('sha256',wp_json_encode($groups)),sanitize_text_field(wp_unslash($_POST['fingerprint']??''))))wp_die('Tiles changed in another session. Reload before saving.',409);
 $inputs=isset($_POST['groups'])&&is_array($_POST['groups'])?wp_unslash($_POST['groups']):array();
 foreach($groups as $i=>&$g){$v=$inputs[$i]??array();if(!is_array($v)||!is_scalar($v['name']??null)||trim($v['name'])==='')wp_die('Enter a tile name.');$g['name']=sanitize_text_field($v['name']);$g['caption']=sanitize_text_field(is_scalar($v['caption']??null)?$v['caption']:'');$g['enabled']=!empty($v['enabled']);foreach($g['offers'] as $m=>&$offer){$offer['ready']=!empty($v['ready'][$m]);$p=wc_get_product($offer['id']);if($offer['ready']&&(!$p||(float)$p->get_regular_price('edit')<=0))wp_die('Set a positive USD full-term price before approving a duration.');}unset($offer);}unset($g);
 update_option('instapass_launch_groups',$groups,false);wp_safe_redirect(admin_url('admin.php?page=instapass-product-tiles&saved=1'));exit;
});
