<?php
/**
 * Plugin Name: Instapass Tenor Offers
 * Description: Link separately priced durations and display current offer savings.
 * Version: 0.3.1
 */
defined('ABSPATH') || exit;
function ip_tenor_duration($p){return function_exists('instapass_product_features')?(string)instapass_product_features($p)['validity']:(string)$p->get_meta('_instapass_validity');}
function ip_tenor_duration_key($text){
 $text=trim(preg_replace('/\s+/u',' ',wp_strip_all_tags($text)));$text=function_exists('mb_strtolower')?mb_strtolower($text):strtolower($text);
 if(preg_match('/^([1-9][0-9]*)\s*(day|days|week|weeks|month|months|year|years)$/',$text,$m)){
  $unit=rtrim($m[2],'s');$n=(int)$m[1];if($unit==='year'){$n*=12;$unit='month';}return $n.':'.$unit;
 }
 return $text;
}
function ip_tenor_offer_key($p){
 $group=(string)$p->get_meta('_instapass_tenor_group');$duration=ip_tenor_duration_key(ip_tenor_duration($p));
 return $group!==''&&$duration!==''?'tenor:'.$group.':'.$duration:'product:'.$p->get_id();
}
function ip_tenor_offers($p){
 $group=(string)$p->get_meta('_instapass_tenor_group');if($group===''||!$p->is_type('simple')){return array();}
 $offers=wc_get_products(array('status'=>'publish','type'=>'simple','limit'=>100,'orderby'=>'menu_order','order'=>'ASC','meta_key'=>'_instapass_tenor_group','meta_value'=>$group));
 return array_values(array_filter($offers,function($offer){return $offer->get_catalog_visibility()!=='hidden'&&ip_tenor_duration($offer)!=='';}));
}
function ip_tenor_fields(){
 global $product_object;if(!$product_object){return;}
 echo '<div class="options_group show_if_simple"><input type="hidden" name="ip_tenor_fields" value="1">';
 woocommerce_wp_text_input(array('id'=>'_instapass_tenor_family','label'=>'Same product / other tenors','value'=>$product_object->get_meta('_instapass_tenor_family'),'description'=>'Use the same product and plan name on each duration listing, for example ChatGPT Plus. Set Access duration, price and stock separately on each listing. Leave blank to keep this offer separate.','desc_tip'=>false));
 echo '</div>';
}
add_action('woocommerce_product_options_general_product_data','ip_tenor_fields',25);
add_action('woocommerce_admin_process_product_object',function($p){
 if(!isset($_POST['ip_tenor_fields'])||!$p->is_type('simple')||!isset($_POST['_instapass_tenor_family'])||!is_scalar($_POST['_instapass_tenor_family'])){return;}
 $family=sanitize_text_field(wp_unslash($_POST['_instapass_tenor_family']));
 $p->update_meta_data('_instapass_tenor_family',$family);
 $p->update_meta_data('_instapass_tenor_group',$family===''?'':hash('sha256',strtolower(trim(preg_replace('/\s+/u',' ',$family)))));
},30);
function ip_tenor_markup($p){
 $offers=ip_tenor_offers($p);if(count($offers)<2){return '';}
 $html='<section class="ip-tenor-offers" aria-label="Subscription durations"><h3>Choose a duration</h3><p>Each duration has its own price and stock. Add different durations separately to the same order.</p><ul>';
 foreach($offers as $offer){
  $current=$offer->get_id()===$p->get_id();$duration=ip_tenor_duration($offer);$buy=$offer->is_purchasable()&&$offer->is_in_stock();
  $html.='<li data-offer-id="'.absint($offer->get_id()).'"'.($current?' class="is-current"':'').'><div><a href="'.esc_url($offer->get_permalink()).'"'.($current?' aria-current="page"':'').'>'.esc_html($duration).'</a>'.($current?'<span class="ip-tenor-current">Selected</span>':'').'<div class="ip-tenor-price">'.$offer->get_price_html().'</div></div><div class="ip-tenor-action">';
  if($buy){$html.='<a class="button" href="'.esc_url(add_query_arg(array('add-to-cart'=>$offer->get_id(),'ip_buy_now'=>1),wc_get_checkout_url())).'">Add '.esc_html($duration).'</a>';}
  else{$html.='<span>'.esc_html(!$offer->is_in_stock()?'Out of stock':($offer->get_price()===''?'Price on request':'Unavailable in this currency')).'</span>';}
  $html.='</div></li>';
 }
 return $html.'</ul><p class="ip-tenor-bundle-help">All eligible units count for bundle discounts, including repeated quantities of the same product and duration.</p></section>';
}
// The block theme uses a post excerpt before its native add-to-cart form.
add_filter('render_block_core/post-excerpt',function($html,$parsed,$block){
 if(!function_exists('is_product')||!is_product()||!function_exists('instapass_block_product')){return $html;}
 $p=instapass_block_product($block);return $p?$html.ip_tenor_markup($p):$html;
},35,3);
add_filter('woocommerce_get_item_data',function($data,$item){
 $p=$item['data']??null;if(!$p){return $data;}$duration=ip_tenor_duration($p);
 if($duration!==''){foreach($data as $row){if(($row['key']??'')==='Duration'){return $data;}}$data[]=array('key'=>'Duration','value'=>$duration);}
 return $data;
},25,2);
add_action('woocommerce_checkout_create_order_line_item',function($item,$key,$values){
 $p=$values['data']??null;if($p&&!$item->get_meta('Duration',true)&&ip_tenor_duration($p)!==''){$item->add_meta_data('Duration',ip_tenor_duration($p),true);}
},30,3);
add_action('wp_enqueue_scripts',function(){if(!is_admin()){wp_enqueue_style('instapass-tenor-offers',plugins_url('tenor-offers.css',__FILE__),array(),'0.2.3');}});
add_action('admin_menu',function(){add_submenu_page('edit.php?post_type=product','Subscription tenors','Subscription Tenors','manage_woocommerce','instapass-tenor-offers','ip_tenor_admin');});
function ip_tenor_admin(){
 if(!current_user_can('manage_woocommerce')){return;}
 echo '<div class="wrap"><h1>Subscription tenors</h1><p>Each tenor is a separate product listing with its own price, discount, stock and restock alert. Customers can add multiple tenors to one order.</p><ol><li>Open a product and use Duplicate to prepare another duration, or add a new simple product.</li><li>Under Product data &rarr; General, enter the same product and plan name in <strong>Same product / other tenors</strong> on every related listing. Keep different plans in different families.</li><li>Set the exact Access duration, price, stock, and delivery terms for each listing. Include the duration in the product title. Save as a draft until ready.</li><li>Publish priced, available offers. The product page lists the other published durations automatically. Draft and hidden offers stay private.</li></ol><p>TOOLKIT5 applies at two eligible items (5% off), TOOLKIT75 at three (7.5% off), and TOOLKIT10 at four (10% off). Repeated quantities of the same product and duration count. Configure percentages, eligible offers and coupon dates under Promotions.</p><p><a class="button button-primary" href="'.esc_url(admin_url('post-new.php?post_type=product')).'">Add a tenor listing</a> <a class="button" href="'.esc_url(admin_url('edit.php?post_type=product')).'">Manage products</a> <a class="button" href="'.esc_url(admin_url('edit.php?post_type=product&page=instapass-promotions')).'">Manage bundle offers</a></p><p>No higher-tenor price is calculated automatically. USD and GBP prices are entered independently; PKR follows your saved rate and rounding rule.</p></div>';
}
require_once __DIR__.'/monthly-pricing.php';
