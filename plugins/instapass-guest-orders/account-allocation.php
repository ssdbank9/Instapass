<?php
/** Purchased-term allocation. These are fulfilment instructions, never payment proof. */
defined('ABSPATH') || exit;

function ip_alloc_months($p){
 if(!$p||!$p->is_virtual()||!$p->is_type('simple'))return 0;
 $duration=trim((string)$p->get_meta('_instapass_validity'));
 if(!preg_match('/^([1-9][0-9]{0,2})\s*(months?|years?)$/iD',$duration,$m))return 0;
 return (int)$m[1]*(stripos($m[2],'year')===0?12:1);
}
function ip_alloc_policy($p){
 if(!$p)return 'no';$saved=$p->get_meta('_ip_account_allocation',true,'edit');
 if(in_array($saved,['yes','no'],true))return $saved;
 // Only this owner-confirmed monthly offer opts in automatically. Other offers require an explicit owner choice.
 return $p->get_sku('edit')==='claude-max-gift-x5'&&ip_alloc_months($p)===1?'yes':'no';
}
function ip_alloc_enabled($p){return ip_alloc_policy($p)==='yes'&&ip_alloc_months($p)>0&&strtolower((string)$p->get_meta('_instapass_access_type'))!=='shared';}
function ip_alloc_stamp($p,$qty){return hash_hmac('sha256',$p->get_id().'|'.$qty.'|'.ip_alloc_months($p).'|'.ip_alloc_policy($p),wp_salt('nonce'));}
function ip_alloc_validate($raw,$p,$qty,$fallback){
 if(!ip_alloc_enabled($p)||$qty<2)return [];
 if(!is_array($raw)||!is_string($raw['stamp']??null)||!hash_equals(ip_alloc_stamp($p,$qty),$raw['stamp']))throw new InvalidArgumentException('Your basket or offer changed. Refresh checkout and review the account split.');
 $mode=$raw['mode']??'';if(!is_string($mode)||!in_array($mode,['one','each','custom'],true))throw new InvalidArgumentException('Choose how to split the purchased units across accounts.');
 $expected=$mode==='one'?1:$qty;
 if($mode==='custom'){
  $count=$raw['account_count']??null;
  if(!is_scalar($count)||!preg_match('/^[1-9][0-9]{0,2}$/D',(string)$count)||(int)$count>min($qty,100))throw new InvalidArgumentException('Choose the number of accounts, then enter one activation email for each. Refresh checkout if the account selector is missing.');
  $expected=(int)$count;
 }
 $input=$raw['accounts']??null;if(!is_array($input)||!$input||count($input)>100)throw new InvalidArgumentException('Enter account details (up to 100 accounts per offer).');
 $rows=[];$sum=0;$seen=[];
 foreach(array_values($input) as $i=>$row){
  if(!is_array($row))throw new InvalidArgumentException('Enter valid account details.');
  if($mode==='one'&&$i>0)break;
  $units=$mode==='one'?(string)$qty:($mode==='each'?'1':($row['units']??''));
  if($mode==='custom'&&$units==='')continue;
  if(!is_scalar($units)||!preg_match('/^[1-9][0-9]{0,6}$/D',(string)$units)||(int)$units>$qty)throw new InvalidArgumentException('Use positive whole units for each account.');
  $kind=$row['kind']??'';if(!is_string($kind)||!in_array($kind,['existing','new'],true))throw new InvalidArgumentException('Choose existing or new for each account.');
  $email=$row['email']??'';if(!is_string($email))throw new InvalidArgumentException('Enter valid activation emails.');
  $email=strtolower(trim($email));if($expected===1&&$email==='')$email=strtolower(trim($fallback));
  if(!is_email($email))throw new InvalidArgumentException('Enter a valid activation email for every account. For one account, leave it blank to use your checkout activation email.');
  if(isset($seen[$email]))throw new InvalidArgumentException('Use a different activation email for each account within this offer.');
  $seen[$email]=true;$sum+=(int)$units;$rows[]=['units'=>(int)$units,'months'=>(int)$units*ip_alloc_months($p),'kind'=>$kind,'email'=>$email];
 }
 if(!$rows||$sum!==$qty)throw new InvalidArgumentException('Account allocations must total exactly '.$qty.' purchased units.');
 if(count($rows)!==$expected)throw new InvalidArgumentException('Enter details for exactly '.$expected.' selected accounts.');
 return ['version'=>1,'unit_months'=>ip_alloc_months($p),'quantity'=>(int)$qty,'accounts'=>$rows];
}
function ip_alloc_post(){return isset($_POST['ip_alloc'])&&is_array($_POST['ip_alloc'])?wp_unslash($_POST['ip_alloc']):[];}
function ip_alloc_summary($plan){
 if(!is_array($plan)||empty($plan['accounts']))return '';
 return implode(' + ',array_map(static fn($r)=>(int)$r['months'].' month'.((int)$r['months']===1?'':'s'),$plan['accounts'])).' across '.count($plan['accounts']).' account'.(count($plan['accounts'])===1?'':'s');
}
function ip_alloc_order_has_plan($order){foreach($order->get_items() as $item)if($item->get_meta('_ip_account_allocation'))return true;return false;}
add_action('woocommerce_after_checkout_billing_form',function(){
 if(!WC()->cart)return;
 foreach(WC()->cart->get_cart() as $key=>$line){
  $p=$line['data'];$qty=(int)$line['quantity'];if($qty<2||!ip_alloc_enabled($p))continue;$months=ip_alloc_months($p);
  echo '<fieldset class="ip-allocation" data-quantity="'.esc_attr($qty).'" data-months="'.esc_attr($months).'"><legend>Choose accounts for '.esc_html($p->get_name()).'</legend><p>'.absint($qty).' purchased units × '.absint($months).' month'.($months===1?'':'s').' each. Keep them together or split them. Your price and quantity discount stay the same.</p>';
  $prefix='ip_alloc['.$key.']';$id='ipa-'.$key;
  echo '<input type="hidden" name="'.esc_attr($prefix.'[stamp]').'" value="'.esc_attr(ip_alloc_stamp($p,$qty)).'"><p><label for="'.esc_attr($id.'-mode').'">How would you like to use these units?</label><select required id="'.esc_attr($id.'-mode').'" name="'.esc_attr($prefix.'[mode]').'" class="ip-allocation-mode"><option value="">Choose an account split…</option><option value="one">All '.absint($qty*$months).' months on one account</option><option value="each" '.disabled($qty>100,true,false).'>'.absint($qty).' accounts, '.absint($months).' month'.($months===1?'':'s').' each</option><option value="custom">Custom split across accounts</option></select></p><p class="ip-allocation-count-wrap"><label for="'.esc_attr($id.'-count').'">Number of accounts</label><select id="'.esc_attr($id.'-count').'" class="ip-allocation-count" name="'.esc_attr($prefix.'[account_count]').'"><option value="">Choose the number of accounts…</option>';
  for($n=1;$n<=min($qty,100);$n++)echo '<option value="'.absint($n).'">'.absint($n).' account'.($n===1?'':'s').' — '.absint($n).' activation email'.($n===1?'':'s').'</option>';
  echo '</select></p><p class="ip-allocation-help">Choose how many accounts you need. Each selected account gets its own activation email field. You can adjust the units on each account; the total must be '.absint($qty).'. For a new account, supply the email to activate. Never enter a password.</p><noscript><p>Fill only the selected number of account rows and leave the others blank.</p></noscript><div class="ip-allocation-accounts">';
  for($i=0;$i<min($qty,100);$i++){
   $name=$prefix.'[accounts]['.$i.']';$row=$id.'-'.$i;
   echo '<div class="ip-allocation-account"><h4>Account '.($i+1).'</h4><p><label for="'.esc_attr($row.'-units').'">Purchased units</label><input class="ip-allocation-units" type="number" inputmode="numeric" min="1" max="'.absint($qty).'" step="1" id="'.esc_attr($row.'-units').'" name="'.esc_attr($name.'[units]').'" value=""><small class="ip-allocation-term"></small></p><p><label for="'.esc_attr($row.'-kind').'">Account type</label><select id="'.esc_attr($row.'-kind').'" name="'.esc_attr($name.'[kind]').'"><option value="existing">Existing account</option><option value="new">New account</option></select></p><p><label for="'.esc_attr($row.'-email').'">Activation email</label><input type="email" autocomplete="off" id="'.esc_attr($row.'-email').'" name="'.esc_attr($name.'[email]').'"></p></div>';
  }
  echo '</div><p class="ip-allocation-total" role="status" aria-live="polite"></p><p>One-account email can be left blank to use the activation email above (or receipt email). Split accounts each need their own email. Consecutive terms are fulfilled on the chosen account; no automatic payment is taken. Instructions are saved with your order for our team after payment verification.</p></fieldset>';
 }
},25);
add_action('wp_enqueue_scripts',function(){if(is_checkout()&&!is_order_received_page()){wp_enqueue_style('ip-account-allocation',plugins_url('account-allocation.css',__FILE__),[],IP_GUEST_VERSION);wp_enqueue_script('ip-account-allocation',plugins_url('account-allocation.js',__FILE__),[],IP_GUEST_VERSION,true);}});
add_action('woocommerce_after_checkout_validation',function($data,$errors){
 $raw=ip_alloc_post();$fallback=$data['instapass_activation_email']??'';if(!$fallback)$fallback=$data['billing_email']??'';
 foreach(WC()->cart->get_cart() as $key=>$line){try{ip_alloc_validate($raw[$key]??null,$line['data'],(int)$line['quantity'],$fallback);}catch(InvalidArgumentException $e){$errors->add('ip_alloc_'.$key,$line['data']->get_name().': '.$e->getMessage());}}
},130,2);
add_action('woocommerce_checkout_create_order_line_item',function($item,$key,$line,$order){
 $raw=ip_alloc_post();$activation=isset($_POST['instapass_activation_email'])&&is_string($_POST['instapass_activation_email'])?strtolower(trim(wp_unslash($_POST['instapass_activation_email']))):'';
 $fallback=$activation?:$order->get_billing_email();$plan=ip_alloc_validate($raw[$key]??null,$line['data'],(int)$line['quantity'],$fallback);
 if($plan){$item->add_meta_data('_ip_account_allocation',$plan,true);$item->add_meta_data('Account split',ip_alloc_summary($plan),true);}
},50,4);
// The site uses classic checkout. Fail closed on alternate API checkout until it supports these instructions.
add_action('woocommerce_store_api_checkout_update_order_from_request',function($order,$request){
 if($request->get_method()!=='POST'||!WC()->cart)return;
 foreach(WC()->cart->get_cart() as $line)if($line['quantity']>=2&&ip_alloc_enabled($line['data']))throw new \Automattic\WooCommerce\StoreApi\Exceptions\RouteException('ip_account_split','Use the website checkout to choose accounts for this offer.',400);
},2,2);
function ip_alloc_details($item,$plain=false){
 $plan=$item->get_meta('_ip_account_allocation');if(!is_array($plan)||empty($plan['accounts']))return;
 if(!$plain)echo '<div class="ip-account-instructions"><strong>Account activation instructions</strong><ul>';
 foreach($plan['accounts'] as $i=>$row){$text='Account '.($i+1).': '.$row['email'].' — '.($row['kind']==='new'?'new':'existing').' account — '.$row['months'].' month'.($row['months']===1?'':'s').' ('.$row['units'].' purchased unit'.($row['units']===1?'':'s').')';echo $plain?"\n".$text:"<li>".esc_html($text)."</li>";}
 if(!$plain)echo '</ul></div>';
}
add_action('woocommerce_after_order_itemmeta',function($id,$item){if($item instanceof WC_Order_Item_Product)ip_alloc_details($item);},25,2);
add_action('woocommerce_order_item_meta_end',function($id,$item,$order,$plain){
 // This hook runs inside authorised WooCommerce order/receipt rendering, never our limited tracking result.
 if($item instanceof WC_Order_Item_Product)ip_alloc_details($item,$plain);
},25,4);
add_action('woocommerce_single_product_summary',function(){global $product;if(ip_alloc_enabled($product))echo '<p class="ip-account-flexibility">Buying several units? Choose consecutive months on one account or split them across accounts at checkout. Eligible quantity discounts still apply.</p>';},28);
