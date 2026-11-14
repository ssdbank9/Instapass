<?php
/** Owner product and offer editor. */
defined('ABSPATH') || exit;

function ip_offer_brands(){
 $out=[''=>'Automatic / product image'];
 foreach(array_merge(glob(get_template_directory().'/assets/logos/*.svg')?:[],glob(get_template_directory().'/assets/logos/*.png')?:[]) as $path){$key=pathinfo($path,PATHINFO_FILENAME);$out[$key]=ucwords(str_replace('-',' ',$key));}
 asort($out);return $out;
}
function ip_offer_date($value){
 if($value==='')return null;
 $d=DateTimeImmutable::createFromFormat('!Y-m-d\TH:i',$value,wp_timezone());
 if(!$d||$d->format('Y-m-d\TH:i')!==$value)throw new InvalidArgumentException('Enter a valid sale date and time.');
 return $d->getTimestamp();
}
function ip_offer_coupon_allowed($p,$at=null){
 if(!$p)return false;$at=$at??time();
 if($p->get_meta('_ip_offer_coupons')==='no')return false;
 return true;
}
function ip_offer_sale_active($p,$at=null){
 $at=$at??time();$window=ip_offer_sale_window($p);
 return (!$window[0]||$window[0]<=$at)&&(!$window[1]||$window[1]>=$at);
}
function ip_offer_sale_window($p){$dates=[];foreach(['from','to'] as $end){$native=$p->{'get_date_on_sale_'.$end}('edit');$dates[]=$p->meta_exists('_ip_offer_sale_'.$end)?(int)$p->get_meta('_ip_offer_sale_'.$end,true,'edit'):($native?$native->getTimestamp():0);}return $dates;}
// Keep independent-currency sale windows after WooCommerce's cron clears expired
// native sale dates. Explicit edits in the standard product editor replace them.
add_action('woocommerce_admin_process_product_object',function($p){foreach(['from','to'] as $end){$d=$p->{'get_date_on_sale_'.$end}('edit');$p->update_meta_data('_ip_offer_sale_'.$end,$d?$d->getTimestamp():0);}},80);
function ip_offer_read($p=null){
 $v=['id'=>0,'fingerprint'=>'new','name'=>'','sku'=>'','family'=>'','plan'=>'','duration'=>'1 month','brand'=>'','brand_secondary'=>'','image_id'=>0,'status'=>'draft','visibility'=>'visible','stock'=>'instock','manage_stock'=>'no','stock_quantity'=>'','restock_date'=>'','restock_note'=>'','description'=>'','brief'=>'','usd_regular'=>'','usd_sale'=>'','usd_discount'=>'','pkr_mode'=>'auto','pkr_regular'=>'','pkr_sale'=>'','pkr_discount'=>'','gbp_regular'=>'','gbp_sale'=>'','gbp_discount'=>'','eur_regular'=>'','eur_sale'=>'','eur_discount'=>'','sale_from'=>'','sale_to'=>'','allow_coupons'=>'yes','access_type'=>'Private','activation'=>'On customer email','renewable'=>'yes','categories'=>[]];
 $v['allocation']='auto';$v['card_brief']='';if(!$p)return $v;
 foreach(['id'=>'id','name'=>'name','sku'=>'sku','status'=>'status','visibility'=>'catalog_visibility','stock'=>'stock_status','description'=>'description','brief'=>'short_description','image_id'=>'image_id','usd_regular'=>'regular_price','usd_sale'=>'sale_price','stock_quantity'=>'stock_quantity','categories'=>'category_ids'] as $field=>$getter)$v[$field]=$p->{'get_'.$getter}('edit');
 $f=function_exists('instapass_product_features')?instapass_product_features($p):[];
 foreach(['plan','duration'=>'validity','access_type','activation','renewable'] as $k=>$meta){if(is_int($k))$k=$meta;$v[$k]=$f[$meta]??$v[$k];}
 $v['renewable']=in_array($v['renewable'],[true,'yes',1,'1'],true)?'yes':'no';$v['manage_stock']=$p->get_manage_stock('edit')?'yes':'no';
 $v['stock_quantity']=$v['stock_quantity']===null?'':(string)$v['stock_quantity'];
 $v['image_id']=(int)$v['image_id'];
 foreach(['family'=>'_instapass_tenor_family','brand'=>'_instapass_brand','brand_secondary'=>'_instapass_brand_secondary','restock_date'=>'_instapass_restock_date','restock_note'=>'_instapass_restock_note','pkr_regular'=>'_instapass_pkr_regular_price','pkr_sale'=>'_instapass_pkr_sale_price','gbp_regular'=>'_instapass_gbp_regular_price','gbp_sale'=>'_instapass_gbp_sale_price','eur_regular'=>'_instapass_eur_regular_price','eur_sale'=>'_instapass_eur_sale_price'] as $field=>$meta)$v[$field]=(string)$p->get_meta($meta,true,'edit');
 $v['card_brief']=(string)$p->get_meta('_instapass_brief',true,'edit');
 $v['allocation']=in_array($p->get_meta('_ip_account_allocation',true,'edit'),['yes','no'],true)?$p->get_meta('_ip_account_allocation',true,'edit'):'auto';
 $v['pkr_mode']=$v['pkr_regular']===''?'auto':'fixed';$v['allow_coupons']=$p->get_meta('_ip_offer_coupons')==='no'?'no':'yes';
 foreach(array_combine(['sale_from','sale_to'],ip_offer_sale_window($p)) as $field=>$date)$v[$field]=$date?wp_date('Y-m-d\TH:i',$date,wp_timezone()):'';
 $v['fingerprint']=hash('sha256',wp_json_encode([$v,$p->get_date_modified('edit')?$p->get_date_modified('edit')->getTimestamp():0]));return $v;
}
function ip_offer_values($in){
 $v=ip_offer_read();foreach($v as $k=>$default){if($k==='categories')continue;if(!array_key_exists($k,$in)||!is_scalar($in[$k]))throw new InvalidArgumentException('The offer form is incomplete. Reload and try again.');$v[$k]=trim((string)$in[$k]);}
 foreach(['name','sku','family','plan','duration','restock_note','activation','card_brief'] as $k)$v[$k]=sanitize_text_field($v[$k]);
 if($v['name']===''||strlen($v['name'])>200)throw new InvalidArgumentException('Enter a product name of up to 200 characters.');
 if($v['duration']===''||strlen($v['duration'])>80)throw new InvalidArgumentException('Enter the access duration, for example 1 month or 1 year.');
 foreach(['status'=>['draft','publish','private'],'visibility'=>['visible','catalog','search','hidden'],'stock'=>['instock','outofstock'],'pkr_mode'=>['auto','fixed'],'allow_coupons'=>['yes','no'],'manage_stock'=>['yes','no'],'renewable'=>['yes','no'],'access_type'=>['Private','Shared']] as $k=>$choices)if(!in_array($v[$k],$choices,true))throw new InvalidArgumentException('Choose a valid '.$k.' setting.');
 if(!in_array($v['allocation'],['auto','yes','no'],true))throw new InvalidArgumentException('Choose a valid account allocation setting.');
 if($v['allocation']==='yes'&&($v['access_type']!=='Private'||!preg_match('/^[1-9][0-9]{0,2}\s*(months?|years?)$/iD',$v['duration'])))throw new InvalidArgumentException('Account splitting requires private access and a duration in whole months or years.');
 foreach(['brand','brand_secondary'] as $k)if(!array_key_exists($v[$k],ip_offer_brands()))throw new InvalidArgumentException('Choose a logo from the library.');
 if(!ctype_digit($v['id'])||!ctype_digit($v['image_id']))throw new InvalidArgumentException('Invalid product or image selection.');
 $v['id']=(int)$v['id'];$v['image_id']=(int)$v['image_id'];
 if($v['image_id']&&(!wp_attachment_is_image($v['image_id'])||!current_user_can('upload_files')))throw new InvalidArgumentException('Choose an image from your media library.');
 if($v['manage_stock']==='yes'&&(!preg_match('/^[0-9]{1,7}$/D',$v['stock_quantity'])))throw new InvalidArgumentException('Stock quantity must be a whole number from 0 to 9999999.');
 if($v['restock_date']!==''&&(!preg_match('/^\d{4}-\d{2}-\d{2}$/D',$v['restock_date'])||!checkdate((int)substr($v['restock_date'],5,2),(int)substr($v['restock_date'],8,2),(int)substr($v['restock_date'],0,4))))throw new InvalidArgumentException('Enter a valid restock date.');
 // Use the same integer validation as the existing currency editors. No forced .99.
 try{$usd=Instapass_Control_PKR::values($v['usd_regular'],$v['usd_sale'],$v['usd_discount']);}catch(InvalidArgumentException $e){throw new InvalidArgumentException(str_replace('PKR','USD',$e->getMessage()));}
 foreach($usd as $k=>$amount){if($amount!=='')Instapass_Control_Money::usd_cents($amount);$v['usd_'.$k]=$amount;}
 $pkr=$v['pkr_mode']==='auto'?['regular'=>'','sale'=>'']:Instapass_Control_PKR::values($v['pkr_regular'],$v['pkr_sale'],$v['pkr_discount']);
 if($v['pkr_mode']==='fixed'&&$pkr['regular']==='')throw new InvalidArgumentException('Enter a fixed PKR regular price, or choose Automatic PKR.');
 $gbp=Instapass_Control_GBP::values($v['gbp_regular'],$v['gbp_sale'],$v['gbp_discount']);
 $eur=Instapass_Control_EUR::values($v['eur_regular'],$v['eur_sale'],$v['eur_discount']);
 foreach(['pkr'=>$pkr,'gbp'=>$gbp,'eur'=>$eur] as $currency=>$prices)foreach($prices as $k=>$amount)$v[$currency.'_'.$k]=$amount;
 $v['from_timestamp']=ip_offer_date($v['sale_from']);$v['to_timestamp']=ip_offer_date($v['sale_to']);
 if($v['from_timestamp']&&$v['to_timestamp']&&$v['from_timestamp']>$v['to_timestamp'])throw new InvalidArgumentException('Sale end must be after sale start.');
 $v['description']=wp_kses_post($v['description']);$v['brief']=wp_kses_post($v['brief']);$v['categories']=[];
 if(isset($in['categories'])&&!is_array($in['categories']))throw new InvalidArgumentException('Choose valid product categories.');
 foreach($in['categories']??[] as $id){if(!is_scalar($id)||!ctype_digit((string)$id)||!term_exists((int)$id,'product_cat'))throw new InvalidArgumentException('Choose valid product categories.');$v['categories'][]=(int)$id;}
 return $v;
}
function ip_offer_save($in){
 if(!current_user_can('manage_woocommerce')||!current_user_can('edit_products'))throw new RuntimeException('You do not have permission to edit offers.');
 $v=ip_offer_values($in);$p=$v['id']?wc_get_product($v['id']):new WC_Product_Simple();
 if(!$p||!$p->is_type('simple')||($v['id']&&!current_user_can('edit_post',$v['id'])))throw new RuntimeException('This offer is unavailable or needs the standard product editor.');
 if($v['status']==='publish'&&!current_user_can('publish_products'))throw new RuntimeException('You do not have permission to publish products.');
 if(!hash_equals($v['id']?ip_offer_read($p)['fingerprint']:'new',$v['fingerprint']))throw new RuntimeException('This offer changed in another session. Reload before saving.');
 if(!add_option('ip_offer_editor_lock',time(),'','no'))throw new RuntimeException('An offer is currently being saved. Wait and reload before trying again.');
 try{
  if($v['sku']===''&&!$v['id'])$v['sku']=wc_product_generate_unique_sku(0,sanitize_title($v['name']));
  $sku_owner=$v['sku']!==''?wc_get_product_id_by_sku($v['sku']):0;if($sku_owner&&$sku_owner!==$v['id'])throw new InvalidArgumentException('That SKU already belongs to another offer. Choose a unique SKU.');
  foreach(['name'=>'name','sku'=>'sku','status'=>'status','visibility'=>'catalog_visibility','description'=>'description','brief'=>'short_description','image_id'=>'image_id','usd_regular'=>'regular_price','usd_sale'=>'sale_price','categories'=>'category_ids'] as $field=>$setter)$p->{'set_'.$setter}($v[$field]);
  if(!$v['id']){$p->set_virtual(true);$p->set_sold_individually(false);$p->set_reviews_allowed(true);}
  $p->set_manage_stock($v['manage_stock']==='yes');$p->set_stock_quantity($v['manage_stock']==='yes'?(int)$v['stock_quantity']:null);$p->set_backorders('no');$p->set_stock_status($v['manage_stock']==='yes'&&!(int)$v['stock_quantity']?'outofstock':$v['stock']);
  $p->set_date_on_sale_from($v['from_timestamp']);$p->set_date_on_sale_to($v['to_timestamp']);
  $p->update_meta_data('_ip_offer_sale_from',$v['from_timestamp']??0);$p->update_meta_data('_ip_offer_sale_to',$v['to_timestamp']??0);
  $p->set_price($v['usd_sale']!==''&&ip_offer_sale_active($p)?$v['usd_sale']:$v['usd_regular']);
  foreach(['family'=>'_instapass_tenor_family','brand'=>'_instapass_brand','brand_secondary'=>'_instapass_brand_secondary','plan'=>'_instapass_plan','duration'=>'_instapass_validity','access_type'=>'_instapass_access_type','activation'=>'_instapass_activation','renewable'=>'_instapass_renewable','restock_date'=>'_instapass_restock_date','restock_note'=>'_instapass_restock_note','allow_coupons'=>'_ip_offer_coupons'] as $field=>$meta)$p->update_meta_data($meta,$v[$field]);
  $p->update_meta_data('_instapass_brief',$v['card_brief']);
  if($v['allocation']==='auto')$p->delete_meta_data('_ip_account_allocation');else $p->update_meta_data('_ip_account_allocation',$v['allocation']);
  $p->update_meta_data('_instapass_tenor_group',$v['family']===''?'':hash('sha256',strtolower(trim(preg_replace('/\s+/u',' ',$v['family'])))));
  foreach(['pkr','gbp','eur'] as $currency)foreach(['regular','sale'] as $field){$meta='_instapass_'.$currency.'_'.$field.'_price';if($v[$currency.'_'.$field]==='')$p->delete_meta_data($meta);else $p->update_meta_data($meta,$v[$currency.'_'.$field]);}
  $p->update_meta_data('_ip_offer_managed','yes');$id=$p->save();wc_delete_product_transients($id);return $id;
 }finally{delete_option('ip_offer_editor_lock');}
}
add_action('admin_post_ip_offer_save',function(){
 if(!current_user_can('manage_woocommerce'))wp_die('Permission denied.','',['response'=>403]);check_admin_referer('ip_offer_save','ip_offer_nonce');
 $in=isset($_POST['ip_offer'])&&is_array($_POST['ip_offer'])?wp_unslash($_POST['ip_offer']):[];$id=absint($in['id']??0);
 $add_next=!empty($_POST['save_next_tenor']);
 try{$id=ip_offer_save($in);set_transient('ip_offer_notice_'.get_current_user_id(),['success'=>$add_next?'Offer saved. Set the next tenor and its prices below.':'Offer saved.'],120);}catch(Throwable $e){set_transient('ip_offer_notice_'.get_current_user_id(),['error'=>$e->getMessage(),'input'=>$in],120);$add_next=false;}
 $url=$add_next?add_query_arg(['page'=>'instapass-offers','from'=>$id,'preset_duration'=>'custom'],admin_url('admin.php')):add_query_arg(['page'=>'instapass-offers','offer'=>$id],admin_url('admin.php'));wp_safe_redirect($url);exit;
});
add_action('admin_post_ip_offer_trash',function(){
 $id=absint($_POST['offer_id']??0);if(!current_user_can('manage_woocommerce')||!current_user_can('delete_post',$id))wp_die('Permission denied.','',['response'=>403]);check_admin_referer('ip_offer_trash_'.$id);$p=wc_get_product($id);
 if(!$p||!hash_equals(ip_offer_read($p)['fingerprint'],sanitize_text_field(wp_unslash($_POST['fingerprint']??''))))wp_die('Offer changed. Reload first.','',['response'=>409]);
 wp_trash_post($id);wp_safe_redirect(admin_url('admin.php?page=instapass-offers&trashed=1'));exit;
});
add_action('admin_menu',function(){add_submenu_page('instapass-control','Products & Offers','Products & Offers','manage_woocommerce','instapass-offers','ip_offer_page');},25);
add_action('admin_enqueue_scripts',function($hook){if($hook!=='instapass_page_instapass-offers')return;wp_enqueue_media();wp_enqueue_style('ip-offer-editor',plugins_url('../assets/offers.css',__FILE__),[],INSTAPASS_CONTROL_VERSION);wp_enqueue_script('ip-offer-editor',plugins_url('../assets/offers.js',__FILE__),['jquery'],INSTAPASS_CONTROL_VERSION,true);});
function ip_offer_input($v,$key,$label,$type='text',$hint=''){
 echo '<label class="ipo-field"><span>'.esc_html($label).'</span><input name="ip_offer['.esc_attr($key).']" id="ipo-'.esc_attr($key).'" type="'.esc_attr($type).'" value="'.esc_attr($v[$key]).'"'.(in_array($key,['name','duration'],true)?' required':'').'>'.($hint?'<small>'.esc_html($hint).'</small>':'').'</label>';
}
function ip_offer_select($v,$key,$label,$choices){echo '<label class="ipo-field"><span>'.esc_html($label).'</span><select name="ip_offer['.esc_attr($key).']" id="ipo-'.esc_attr($key).'">';foreach($choices as $value=>$text)echo '<option value="'.esc_attr($value).'" '.selected((string)$v[$key],(string)$value,false).'>'.esc_html($text).'</option>';echo '</select></label>';}
function ip_offer_duration_presets($duration){$map=['1 month'=>'Monthly','6 months'=>'6 months','1 year'=>'Annual'];$selected=array_key_exists($duration,$map)?$duration:'custom';echo '<fieldset class="ipo-radio-field ipo-tenor-presets"><legend>Tenor shortcut</legend><div class="ipo-radio-options">';foreach($map as $value=>$label)echo '<label class="ipo-radio-option"><input type="radio" name="ipo_duration_preset" value="'.esc_attr($value).'" '.checked($selected,$value,false).'><span>'.esc_html($label).'</span></label>';echo '<label class="ipo-radio-option"><input type="radio" name="ipo_duration_preset" value="custom" '.checked($selected,'custom',false).'><span>Other duration</span></label></div></fieldset>';}
function ip_offer_quick_form($p,$mode,$value,$label,$confirm=''){
 $id=$p->get_id();echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'" class="ipo-quick-state-form"><input type="hidden" name="action" value="ip_offer_quick_state"><input type="hidden" name="offer_id" value="'.absint($id).'"><input type="hidden" name="mode" value="'.esc_attr($mode).'"><input type="hidden" name="value" value="'.esc_attr($value).'"><input type="hidden" name="fingerprint" value="'.esc_attr(ip_offer_read($p)['fingerprint']).'">';wp_nonce_field('ip_offer_quick_state_'.$id,'ip_offer_quick_nonce');echo '<button class="button button-small" type="submit"'.($confirm?' data-confirm="'.esc_attr($confirm).'"':'').'>'.esc_html($label).'</button></form>';
}
function ip_offer_radio($v,$key,$label,$choices,$help=''){
 echo '<fieldset class="ipo-radio-field"><legend>'.esc_html($label).'</legend><div class="ipo-radio-options">';
 foreach($choices as $value=>$text){$id='ipo-'.sanitize_html_class($key).'-'.sanitize_html_class((string)$value);echo '<label class="ipo-radio-option" for="'.esc_attr($id).'"><input type="radio" name="ip_offer['.esc_attr($key).']" id="'.esc_attr($id).'" value="'.esc_attr($value).'" '.checked((string)$v[$key],(string)$value,false).'><span>'.esc_html($text).'</span></label>';}
 echo '</div>'.($help?'<small>'.esc_html($help).'</small>':'').'</fieldset>';
}
function ip_offer_quick_state_product($id,$mode,$value,$fingerprint=''){
 if(!current_user_can('manage_woocommerce')||!current_user_can('edit_products'))throw new RuntimeException('You do not have permission to update offers.');
 $id=absint($id);$p=$id?wc_get_product($id):null;
 if(!$p||!$p->is_type('simple')||!current_user_can('edit_post',$id))throw new RuntimeException('This simple offer is unavailable or cannot be edited here.');
 if($fingerprint!==''&&!hash_equals(ip_offer_read($p)['fingerprint'],sanitize_text_field($fingerprint)))throw new RuntimeException('This offer changed in another session. Reload the catalog before updating it.');
 if($mode==='status'){
  if(!in_array($value,['draft','publish'],true))throw new InvalidArgumentException('Choose Activate or Deactivate.');
  if($value==='publish'&&!current_user_can('publish_products'))throw new RuntimeException('You do not have permission to activate offers.');
 }elseif($mode==='stock'){
  if(!in_array($value,['instock','outofstock'],true))throw new InvalidArgumentException('Choose In stock or Out of stock.');
  if($value==='instock'&&$p->get_manage_stock('edit')&&(int)$p->get_stock_quantity('edit')<1)throw new InvalidArgumentException('Set a quantity above zero before marking this tracked offer in stock.');
 }else throw new InvalidArgumentException('Choose a valid offer setting.');
 return $p;
}
function ip_offer_quick_state_save($p,$mode,$value){
 if($mode==='status')$p->set_status($value);else $p->set_stock_status($value);
 $id=$p->save();if(!$id)throw new RuntimeException('WordPress could not save this offer.');wc_delete_product_transients($id);return $id;
}
function ip_offer_quick_state_redirect($done,$failed,$message=''){
 $url=wp_get_referer();if(!$url||strpos($url,admin_url('admin.php?page=instapass-offers'))!==0)$url=admin_url('admin.php?page=instapass-offers');
 set_transient('ip_offer_notice_'.get_current_user_id(),['success'=>$message?:($done.' offer setting(s) updated.'.($failed?' '.$failed.' could not be updated.':''))],120);
 wp_safe_redirect($url);exit;
}
add_action('admin_post_ip_offer_quick_state',function(){
 $id=absint($_POST['offer_id']??0);if(!current_user_can('manage_woocommerce'))wp_die('Permission denied.','',['response'=>403]);check_admin_referer('ip_offer_quick_state_'.$id,'ip_offer_quick_nonce');
 try{$mode=sanitize_key(wp_unslash($_POST['mode']??''));$value=sanitize_key(wp_unslash($_POST['value']??''));$p=ip_offer_quick_state_product($id,$mode,$value,(string)wp_unslash($_POST['fingerprint']??''));ip_offer_quick_state_save($p,$mode,$value);ip_offer_quick_state_redirect(1,0,'Offer updated.');}
 catch(Throwable $e){set_transient('ip_offer_notice_'.get_current_user_id(),['error'=>$e->getMessage()],120);wp_safe_redirect(admin_url('admin.php?page=instapass-offers'));exit;}
});
add_action('admin_post_ip_offer_bulk_status',function(){
 if(!current_user_can('manage_woocommerce'))wp_die('Permission denied.','',['response'=>403]);check_admin_referer('ip_offer_bulk_status','ip_offer_bulk_nonce');$target=sanitize_key(wp_unslash($_POST['target_status']??''));$ids=isset($_POST['offer_ids'])&&is_array($_POST['offer_ids'])?array_values(array_unique(array_filter(array_map('absint',wp_unslash($_POST['offer_ids']))))):[];$fingerprints=isset($_POST['fingerprints'])&&is_array($_POST['fingerprints'])?wp_unslash($_POST['fingerprints']):[];
 if(!$ids||count($ids)>100||!in_array($target,['draft','publish'],true)){set_transient('ip_offer_notice_'.get_current_user_id(),['error'=>'Select up to 100 offers and choose Activate or Deactivate.'],120);wp_safe_redirect(admin_url('admin.php?page=instapass-offers'));exit;}
 try{$products=[];foreach($ids as $id){$fp=isset($fingerprints[$id])&&is_string($fingerprints[$id])?$fingerprints[$id]:'';$products[$id]=ip_offer_quick_state_product($id,'status',$target,$fp);} $done=0;foreach($products as $p){try{ip_offer_quick_state_save($p,'status',$target);$done++;}catch(Throwable $e){set_transient('ip_offer_notice_'.get_current_user_id(),['error'=>$done.' offer(s) updated before a save failed. Reload the catalog and check all selected statuses before retrying.'],120);wp_safe_redirect(admin_url('admin.php?page=instapass-offers'));exit;}}ip_offer_quick_state_redirect($done,0,$done.' offer(s) updated.');}
 catch(Throwable $e){set_transient('ip_offer_notice_'.get_current_user_id(),['error'=>$e->getMessage()],120);wp_safe_redirect(admin_url('admin.php?page=instapass-offers'));exit;}
});
function ip_offer_page(){
 if(!current_user_can('manage_woocommerce'))wp_die('Permission denied.','',['response'=>403]);
 $id=absint($_GET['offer']??0);$source=absint($_GET['from']??0);$p=$id?wc_get_product($id):($source?wc_get_product($source):null);
 if($p&&(!current_user_can('edit_post',$p->get_id())||!$p->is_type('simple'))){echo '<div class="wrap"><h1>Products & Offers</h1><p>Open this product in the standard product editor.</p></div>';return;}
 $v=ip_offer_read($p);if(!$id){$v['id']=0;$v['fingerprint']='new';$v['sku']='';$v['status']='draft';if($source)$v['name'].=' — new duration';}
 $preset=isset($_GET['preset_duration'])&&is_scalar($_GET['preset_duration'])?sanitize_text_field(wp_unslash($_GET['preset_duration'])):'';
 if(!$id&&$source&&in_array($preset,['1 month','6 months','1 year','custom'],true)){$v['duration']=$preset==='custom'?'':$preset;foreach(['usd_regular','usd_sale','usd_discount','pkr_regular','pkr_sale','pkr_discount','gbp_regular','gbp_sale','gbp_discount','eur_regular','eur_sale','eur_discount'] as $key)$v[$key]='';$v['pkr_mode']='auto';$v['stock_quantity']='';$v['manage_stock']='no';}
 $notice=get_transient('ip_offer_notice_'.get_current_user_id());if($notice){delete_transient('ip_offer_notice_'.get_current_user_id());if(isset($notice['input']))$v=array_merge($v,array_intersect_key($notice['input'],$v));}
 echo '<div class="wrap ipo"><header class="ipo-header"><div><span>INSTAPASS / CATALOG STUDIO</span><h1>Products & Offers</h1><p>Create a duration, choose its look, set prices and decide when discounts apply.</p></div><a class="button" href="'.esc_url(admin_url('admin.php?page=instapass-offers')).'">New offer</a><a class="button" href="'.esc_url(admin_url('edit.php?post_type=product&page=instapass-promotions')).'">Coupon rules & dates</a><a class="button" href="'.esc_url(admin_url('admin.php?page=instapass-control-pricing')).'">Bulk / Excel pricing</a></header>';
 if($notice)echo '<div class="notice notice-'.(isset($notice['error'])?'error':'success').'"><p>'.esc_html($notice['error']??$notice['success']).'</p></div>';
 if(isset($_GET['trashed']))echo '<div class="notice notice-success"><p>Offer moved to Trash. Restore it from Products → Trash if needed.</p></div>';
 echo '<form method="get" class="ipo-template"><input type="hidden" name="page" value="instapass-offers"><label>Start from an existing offer <select name="from"><option value="">Choose a product…</option>';
 foreach(wc_get_products(['limit'=>500,'type'=>'simple','status'=>['publish','draft','private'],'orderby'=>'name','order'=>'ASC']) as $o)echo '<option value="'.absint($o->get_id()).'">'.esc_html($o->get_name()).'</option>';
 echo '</select></label><button class="button">Prepare a new draft</button></form><form method="post" action="'.esc_url(admin_url('admin-post.php')).'" id="ipo-editor"><input type="hidden" name="action" value="ip_offer_save">';wp_nonce_field('ip_offer_save','ip_offer_nonce');
 foreach(['id','fingerprint','image_id'] as $key)echo '<input type="hidden" id="ipo-'.esc_attr($key).'" name="ip_offer['.esc_attr($key).']" value="'.esc_attr($v[$key]).'">';
 echo '<div class="ipo-grid"><section class="ipo-card"><h2>1 · Product & duration</h2>';
 ip_offer_input($v,'name','Offer name');ip_offer_input($v,'family','Product / plan family','text','Use the same family on monthly and annual offers, for example ChatGPT Plus.');ip_offer_duration_presets($v['duration']);ip_offer_input($v,'duration','Access duration','text','Each tenor is its own offer tile with its own base price, discount, stock and active state.');ip_offer_input($v,'plan','Plan details');ip_offer_input($v,'sku','Unique SKU','text','Optional for new offers; generated from the name if blank.');
 ip_offer_radio($v,'access_type','Access',['Private'=>'Private access','Shared'=>'Shared access']);ip_offer_input($v,'activation','Activation instructions');ip_offer_radio($v,'renewable','Renewal available',['yes'=>'Yes — customer confirms and pays again','no'=>'No']);
 ip_offer_radio($v,'allocation','Repeated units: account allocation',['auto'=>'Default — Claude Max 5x monthly only','yes'=>'Allow consecutive terms or separate accounts','no'=>'Disable account splitting']);echo '<p>Enable only when you can fulfil consecutive terms on a private account. Duration must be whole months/years. Customers choose the split and emails at checkout; the purchased quantity and price stay unchanged.</p>';
 echo '<label class="ipo-field"><span>Product categories</span><select name="ip_offer[categories][]" multiple size="4">';foreach(get_terms(['taxonomy'=>'product_cat','hide_empty'=>false]) as $term)echo '<option value="'.absint($term->term_id).'" '.selected(in_array((int)$term->term_id,array_map('intval',(array)$v['categories']),true),true,false).'>'.esc_html($term->name).'</option>';echo '</select></label></section><section class="ipo-card"><h2>2 · Logo & product image</h2>';
 ip_offer_select($v,'brand','Primary logo',ip_offer_brands());ip_offer_select($v,'brand_secondary','Optional second logo',ip_offer_brands());
 echo '<p>A selected product image takes priority over built-in logos. Clear it to use the selected logo.</p><div id="ipo-image-preview">'.($v['image_id']?wp_get_attachment_image((int)$v['image_id'],'thumbnail'):'').'</div><button type="button" class="button" id="ipo-image-choose">Choose / upload image</button> <button type="button" class="button" id="ipo-image-clear">Clear image</button><div id="ipo-logo-preview" data-base="'.esc_url(get_template_directory_uri().'/assets/logos/').'" data-files="'.esc_attr(wp_json_encode(array_map('basename',array_merge(glob(get_template_directory().'/assets/logos/*.svg')?:[],glob(get_template_directory().'/assets/logos/*.png')?:[])))).'"></div>';
 echo '<h2>Product copy</h2>';ip_offer_input($v,'card_brief','Product-card caption','text','The short sentence below the product name in the shop.');foreach(['brief'=>'Product-page short description','description'=>'Full description'] as $key=>$label)echo '<label class="ipo-field"><span>'.esc_html($label).'</span><textarea name="ip_offer['.$key.']" rows="5">'.esc_textarea($v[$key]).'</textarea></label>';echo '</section></div>';
 echo '<section class="ipo-card"><h2>3 · Base price & discount</h2><p>Enter the full-term base price for this tenor. Add a lower sale price or a discount percentage; the product tile calculates and shows the amount and percentage saved automatically.</p><div class="ipo-price-grid">';
 foreach(['usd'=>'USD','pkr'=>'PKR','gbp'=>'GBP','eur'=>'EUR'] as $c=>$label){echo '<fieldset><legend>'.esc_html($label).'</legend>';if($c==='pkr')ip_offer_radio($v,'pkr_mode','PKR price source',['auto'=>'Automatic from USD rate','fixed'=>'Independent PKR price']);foreach(['regular'=>'Base price — full term','sale'=>'Sale price — full term (optional)','discount'=>'Apply discount % (optional)'] as $field=>$field_label)ip_offer_input($v,$c.'_'.$field,$field_label,'text');echo '</fieldset>';}
 echo '</div><div class="ipo-grid">';ip_offer_input($v,'sale_from','Sale starts (optional)','datetime-local');ip_offer_input($v,'sale_to','Sale ends (optional)','datetime-local');echo '</div><p>Dates use '.esc_html(wp_timezone_string()).' and apply to sale prices in all currencies. Blank start means now; blank end means no expiry. GBP and EUR are always independent. The editable USD/PKR rate is on the main Instapass dashboard.</p><div id="ipo-price-preview" role="status"></div></section>';
 echo '<div class="ipo-grid"><section class="ipo-card"><h2>4 · Stock & availability</h2>';ip_offer_radio($v,'stock','Availability',['instock'=>'In stock','outofstock'=>'Out of stock']);ip_offer_radio($v,'manage_stock','Track exact quantity',['no'=>'No — quantity not tracked','yes'=>'Yes — enter available quantity']);ip_offer_input($v,'stock_quantity','Available quantity','number','Leave quantity untracked unless you choose the option above.');ip_offer_input($v,'restock_date','Expected restock date','date');ip_offer_input($v,'restock_note','Restock message');echo '<p>Confirmed restock subscribers are notified when an available, priced product returns to stock.</p></section><section class="ipo-card"><h2>5 · Coupons & publishing</h2>';ip_offer_radio($v,'allow_coupons','Allow coupon discounts on this offer',['yes'=>'Yes','no'=>'No — sale price only']);ip_offer_radio($v,'status','Offer status',['publish'=>'Active — visible according to catalog setting','draft'=>'Deactivated — hidden from the shop','private'=>'Private']);ip_offer_radio($v,'visibility','Catalog visibility',['visible'=>'Shop and search','catalog'=>'Shop only','search'=>'Search only','hidden'=>'Hidden — direct link only']);echo '<p>Basket quantities count: 2 × the same monthly offer can qualify. Coupon eligibility, percentages, selected products and schedules are controlled in Coupon rules & dates.</p><button class="button button-primary button-hero" type="submit">Save offer</button> <button class="button button-hero" type="submit" name="save_next_tenor" value="1">Save & add another tenor</button>';
 if($id)echo '<p><a href="'.esc_url(get_permalink($id)).'" target="_blank" rel="noopener">View offer ↗</a> · <a href="'.esc_url(get_edit_post_link($id,'')).'">Advanced product editor</a></p>';echo '</section></div></form>';
 if($id){echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'" class="ipo-trash"><input type="hidden" name="action" value="ip_offer_trash"><input type="hidden" name="offer_id" value="'.absint($id).'"><input type="hidden" name="fingerprint" value="'.esc_attr($v['fingerprint']).'">';wp_nonce_field('ip_offer_trash_'.$id);echo '<button class="button" type="submit">Move this offer to Trash</button><small>It can be restored from Products → Trash. Existing order records remain.</small></form>';}
 $search=isset($_GET['offer_search'])&&is_scalar($_GET['offer_search'])?sanitize_text_field(wp_unslash($_GET['offer_search'])):'';$page=max(1,absint($_GET['catalog_page']??1));$q=new WP_Query(['post_type'=>'product','post_status'=>['publish','draft','private'],'posts_per_page'=>20,'paged'=>$page,'s'=>$search,'orderby'=>'title','order'=>'ASC']);
 echo '<section class="ipo-card"><h2>Your product catalog</h2><form method="get"><input type="hidden" name="page" value="instapass-offers"><input type="search" name="offer_search" value="'.esc_attr($search).'" placeholder="Search offers"><button class="button">Search</button></form>';
 echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'" id="ipo-bulk-status"><input type="hidden" name="action" value="ip_offer_bulk_status">';wp_nonce_field('ip_offer_bulk_status','ip_offer_bulk_nonce');echo '<fieldset class="ipo-bulk-options"><legend>Selected offers</legend><label><input type="radio" name="target_status" value="draft" checked> Deactivate</label><label><input type="radio" name="target_status" value="publish"> Activate</label><button class="button button-primary" type="submit">Apply to selected</button><small>Deactivation saves the offer as a draft; orders are kept.</small></fieldset></form>';
 echo '<div class="ipo-table"><table class="widefat striped"><thead><tr><th><input type="checkbox" class="ipo-select-all" aria-label="Select all offers on this page"></th><th>Product / tenor</th><th>State</th><th>Base USD</th><th>Current USD</th><th>Tile saving</th><th>Stock</th><th>Quick actions</th></tr></thead><tbody>';
 foreach($q->posts as $post){$o=wc_get_product($post->ID);if(!$o)continue;$simple=$o->is_type('simple');$base=$o->get_regular_price('edit');$now=$o->get_price('edit');$saving='—';if(is_numeric($base)&&is_numeric($now)&&(float)$base>0&&(float)$now<(float)$base){$saved=(float)$base-(float)$now;$saving=wc_price($saved,['currency'=>'USD']).' ('.(int)round(100*$saved/(float)$base).'% off)';}$qty=$o->get_manage_stock('edit')?$o->get_stock_quantity('edit'):null;$stock_label=$o->get_stock_status()==='instock'?'In stock':'Out of stock';if($qty!==null)$stock_label.=' · '.(int)$qty.' left';echo '<tr><td>'.($simple&&current_user_can('edit_post',$o->get_id())?'<input type="checkbox" class="ipo-offer-select" value="'.absint($o->get_id()).'" data-fingerprint="'.esc_attr(ip_offer_read($o)['fingerprint']).'" aria-label="Select '.esc_attr($o->get_name()).'">':'—').'</td><td>'.esc_html($o->get_name()).'<small>'.esc_html($o->get_sku()).'</small></td><td>'.esc_html($o->get_status()==='publish'?'Active':($o->get_status()==='draft'?'Deactivated':'Private')).'</td><td>'.($base===''?'—':wp_kses_post(wc_price((float)$base,['currency'=>'USD']))).'</td><td>'.($now===''?'—':wp_kses_post(wc_price((float)$now,['currency'=>'USD']))).'</td><td>'.wp_kses_post($saving).'</td><td>'.esc_html($stock_label).'</td><td class="ipo-quick-actions">';
  echo '<a href="'.esc_url(add_query_arg(['page'=>'instapass-offers','offer'=>$o->get_id()],admin_url('admin.php'))).'">Edit</a>';
  if($simple&&current_user_can('edit_post',$o->get_id())){$target_status=$o->get_status()==='publish'?'draft':'publish';ip_offer_quick_form($o,'status',$target_status,$target_status==='draft'?'Deactivate':'Activate',$target_status==='draft'?'Deactivate this offer? It will be hidden from the shop. Existing orders remain unchanged.':'');$target_stock=$o->get_stock_status()==='instock'?'outofstock':'instock';$can_instock=$target_stock!=='instock'||!$o->get_manage_stock('edit')||(int)$o->get_stock_quantity('edit')>0;if($can_instock)ip_offer_quick_form($o,'stock',$target_stock,$target_stock==='instock'?'Mark in stock':'Mark out of stock',$target_stock==='outofstock'?'Mark this offer out of stock?':'');else echo '<small>Set quantity above zero to mark in stock.</small>';
   foreach(['1 month'=>'Add monthly','6 months'=>'Add 6 months','1 year'=>'Add annual'] as $duration=>$label)echo '<a class="button button-small" href="'.esc_url(add_query_arg(['page'=>'instapass-offers','from'=>$o->get_id(),'preset_duration'=>$duration],admin_url('admin.php'))).'">'.esc_html($label).'</a>';
  }else echo '<small>Use the standard product editor for this product type.</small>';
  echo '</td></tr>';}
 echo '</tbody></table></div>';for($n=1;$n<=$q->max_num_pages;$n++)echo '<a class="button" href="'.esc_url(add_query_arg(['page'=>'instapass-offers','catalog_page'=>$n,'offer_search'=>$search],admin_url('admin.php'))).'">'.absint($n).'</a> ';echo '</section></div>';
}
// Coupon line eligibility must agree with the offer's owner toggle.
add_filter('woocommerce_coupon_is_valid_for_product',function($valid,$p){return $valid&&ip_offer_coupon_allowed($p);},25,2);

function ip_offer_official_plan($p){
 if(!$p||!$p->is_type('simple'))return null;
 if(function_exists('ip_tenor_months')&&ip_tenor_months($p)===0)return null;
 $group=(string)$p->get_meta('_instapass_tenor_group');
 foreach(['claude-max-gift-x20'=>['monthly'=>20000,'name'=>'Claude Ultra','url'=>'https://support.claude.com/en/articles/17728952-what-is-the-ultra-plan'],'claude-max-gift-x5'=>['monthly'=>10000,'name'=>'Claude Max 5×','url'=>'https://claude.com/pricing'],'claude-pro'=>['monthly'=>2000,'name'=>'Claude Pro','url'=>'https://claude.com/pricing'],'chatgpt-plus'=>['monthly'=>2000,'name'=>'ChatGPT Plus','url'=>'https://help.openai.com/en/articles/6950777-what-is-chatgpt-plus']] as $sku=>$plan){
  if($p->get_sku('edit')===$sku)return $plan;
  $root=wc_get_product(wc_get_product_id_by_sku($sku));
  if($group!==''&&$root&&$root->get_meta('_instapass_tenor_group')===$group)return $plan;
 }return null;
}
function ip_offer_saving_percent($saved,$baseline){return (string)(int)round(100*$saved/$baseline,0,PHP_ROUND_HALF_UP);}
function ip_offer_official_savings_html($p,$plan){
 $duration=function_exists('ip_tenor_duration_key')?ip_tenor_duration_key(ip_tenor_duration($p)):'';
 $months=preg_match('/^([1-9][0-9]{0,3}):month$/D',$duration,$m)?(int)$m[1]:0;
 $currency=get_woocommerce_currency();$baseline=$plan['monthly']*$months;$raw=$p->get_price();$priced=$months>0&&$raw!==''&&is_numeric($raw);$current=$priced?(int)round(wc_get_price_to_display($p)*100):0;
 $h='';$money=static fn($c)=>wc_price($c/100,['currency'=>$currency]);
 // GBP prices are independent. Never invent a GBP equivalent of a US list price.
 if($currency==='PKR')$baseline=(int)round($baseline*(float)instapass_control_rate(),0,PHP_ROUND_HALF_UP);
 if($priced&&in_array($currency,['USD','PKR'],true)&&$baseline>0){
  if($current<$baseline)$h.='<span class="ip-offer-saving-now">Save '.$money($baseline-$current).' ('.esc_html(ip_offer_saving_percent($baseline-$current,$baseline)).'%) vs official monthly billing</span>';
 }
  $rates=$priced&&function_exists('ip_tenor_bundle_rates')?ip_tenor_bundle_rates($p,['full'=>$current,'months'=>$months,'currency'=>$currency]):[];$best=null;foreach($rates as $r)if(!$best||$r['full']<$best['full'])$best=$r;
 if($best&&$best['full']<$current)$h.='<span class="ip-offer-saving-total">Additional '.esc_html($best['percent']).'% off with '.esc_html($best['code']).'<small>'.esc_html($best['tools'].' eligible items required.').'</small></span>';
 $h.='<span class="ip-official-reference"><a href="'.esc_url($plan['url']).'" target="_blank" rel="noopener">Official US price: '.wp_kses_post(wc_price($plan['monthly']/100,['currency'=>'USD'])).'/month</a> before applicable tax.';
 if($months>1)$h.=' Compared over '.absint($months).' monthly payments.';
 if(in_array($currency,['GBP','EUR'],true))$h.=' See the independent '.esc_html($currency).' offer price below.';
 if(!$months)$h.=' Confirm the offer duration before comparing totals.';
 $h.='</span>';
 return '<span class="ip-offer-savings ip-official-savings" data-official-usd-monthly="'.esc_attr($plan['monthly']).'">'.$h.'</span>';
}
function ip_offer_savings_html($p){
 if(!function_exists('instapass_cc_front')||!instapass_cc_front()||is_cart()||is_checkout()||is_account_page())return '';
 $official=ip_offer_official_plan($p);if($official)return ip_offer_official_savings_html($p,$official);
 if(!function_exists('instapass_cc_front')||!instapass_cc_front()||is_cart()||is_checkout()||is_account_page()||$p->get_price()==='')return '';
 $regular=$p->get_regular_price();if($regular===''||!is_numeric($regular))return '';
 $currency=get_woocommerce_currency();$current=(int)round(wc_get_price_to_display($p)*100);$baseline=(int)round(wc_get_price_to_display($p,['price'=>(float)$regular])*100);$label='regular price';
 $months=function_exists('ip_tenor_months')?ip_tenor_months($p):0;
 if($months>1)foreach(ip_tenor_offers($p) as $other){if(ip_tenor_months($other)!==1||$other->get_price()==='')continue;$comparison=(int)round(wc_get_price_to_display($other)*100)*$months;if($comparison>$baseline){$baseline=$comparison;$label=$months.' monthly purchases';}break;}
 if($baseline<=0)return '';
 $money=static fn($c)=>wc_price($c/100,['currency'=>$currency]);$pct=static fn($c)=>ip_offer_saving_percent($c,$baseline);$h='';
 if($current<$baseline)$h.='<span class="ip-offer-saving-now">Save '.$money($baseline-$current).' ('.esc_html($pct($baseline-$current)).'%) vs '.esc_html($label).'</span>';
 if(function_exists('ip_tenor_bundle_rates')){
  $rates=ip_tenor_bundle_rates($p,['full'=>$current,'months'=>max(1,$months),'currency'=>$currency]);$best=null;foreach($rates as $r)if(!$best||$r['full']<$best['full'])$best=$r;
  if($best&&$baseline>$best['full'])$h.='<span class="ip-offer-saving-total">Additional '.esc_html($best['percent']).'% off with '.esc_html($best['code']).'<small>'.esc_html($best['tools'].' eligible items required.').'</small></span>';
 }
 return $h?'<span class="ip-offer-savings">'.$h.'</span>':'';
}
add_filter('woocommerce_get_price_html',function($html,$p){return ip_offer_savings_html($p).$html;},170,2);
add_action('wp_enqueue_scripts',function(){if(!is_admin())wp_enqueue_style('ip-offer-savings',plugins_url('../assets/offer-savings.css',__FILE__),[],INSTAPASS_CONTROL_VERSION);});
