<?php
if(!defined('WP_CLI')||!WP_CLI)throw new RuntimeException('CLI only');
$mode=getenv('IPPC_MODE')?:'verify';$path='/recovery/copy-before.json';
function ippc_specs(){
 $subscription=['We activate your selected plan on the account linked to the email you provide.','Private access, with activation confirmation and instructions emailed after payment verification.','Export your chat history using the provider’s account data-export tools.','Full 30-day warranty from activation — replacement or refund if access does not work as described.'];
 return ['claude-pro'=>['benefits'=>$subscription],'claude-max'=>['benefits'=>$subscription],'claude-ultra'=>['benefits'=>$subscription],'chatgpt'=>['benefits'=>$subscription],
 'claude-credits'=>['name'=>'Claude API Credits','caption'=>'Monthly credits for Claude API, Message Batches API, Playground, Claude Agent SDK and Claude Managed Agents. Exact batch expiry shown before purchase.','benefits'=>['API usage credits for Claude API, Message Batches API, Playground, Claude Agent SDK and Claude Managed Agents.','Use an API key from the linked Claude Console organization.','Choose your balance; the exact expiry and remaining validity are shown before purchase.','Monthly credits expire at the batch boundary without rollover. Claude Code and extra Claude app usage are not included.']],
 'chatgpt-credits'=>['name'=>'OpenAI API Credits','caption'=>'OpenAI API credits for applications, API-based agents and supported Batch API workloads. Offer validity is shown before purchase; ChatGPT subscription limits are separate.','benefits'=>['OpenAI API credits for eligible API Platform usage, including API-based agents, SDK integrations and supported Batch API workloads.','Use the linked API organization and its project API keys for supported API services and API Playground usage.','Your chosen balance and this offer’s validity are shown before purchase.','API credits are separate from ChatGPT subscriptions and do not increase ChatGPT usage limits. Monthly subscription credits do not cover Codex usage through API keys.']]];
}
function ippc_prior_openai(){return ['name'=>'OpenAI API Credits','caption'=>'OpenAI API usage credits for applications and integrations. This offer’s validity is shown before purchase; it does not increase ChatGPT subscription limits.','benefits'=>['OpenAI API credits for supported API Platform usage.','Use the API organization and activation details supplied for your order.','Your chosen balance and this offer’s validity are shown before purchase.','API credits are separate from ChatGPT subscriptions and do not increase ChatGPT usage limits.']];}
function ippc_snapshot(){
 $groups=ipl_groups();$credit=[];foreach($groups as $g)if(in_array($g['key'],['claude-credits','chatgpt-credits'],true))foreach($g['offers'] as $o)$credit[(int)$o['id']]=true;
 $products=[];$copy=[];foreach(wc_get_products(['limit'=>-1,'status'=>['publish','draft','private','pending']]) as $p){$d=$p->get_data();if(isset($credit[$p->get_id()])){$copy[$p->get_id()]=['name'=>$p->get_name('edit'),'description'=>$p->get_description('edit'),'short_description'=>$p->get_short_description('edit')];foreach(['name','description','short_description','date_modified','date_modified_gmt'] as $field)unset($d[$field]);}$products[$p->get_id()]=$d;}ksort($products);
 $orders=[];foreach(wc_get_orders(['limit'=>-1]) as $o)$orders[$o->get_id()]=$o->get_data();ksort($orders);
 $options=[];foreach(['instapass_claude_credit_cycle','instapass_pkr_rate','instapass_rate_history','instapass_promotion','instapass_promotion_three','instapass_promotion_four','woocommerce_instapass_transfer_settings','woocommerce_instapass_crypto_settings','woocommerce_instapass_binance_settings','woocommerce_instapass_easypaisa_settings'] as $k)$options[$k]=get_option($k);
 return ['groups'=>$groups,'copy'=>$copy,'product_guard'=>hash('sha256',wp_json_encode($products)),'order_guard'=>hash('sha256',wp_json_encode($orders)),'option_guard'=>hash('sha256',wp_json_encode($options))];
}
function ippc_verify($before){
 $checks=0;$after=ippc_snapshot();foreach(['product_guard','order_guard','option_guard'] as $k){if($before[$k]!==$after[$k])throw new RuntimeException('Protected data changed: '.$k);$checks++;}
 $expected=$before['groups'];foreach($expected as &$g)if(isset(ippc_specs()[$g['key']]))$g=array_replace($g,ippc_specs()[$g['key']]);unset($g);if($expected!==$after['groups'])throw new RuntimeException('Unapproved catalog setting change');$checks++;
 foreach(ipl_groups() as $g){$spec=ippc_specs()[$g['key']]??null;if(!$spec)continue;if($g['benefits']!==$spec['benefits'])throw new RuntimeException('Checklist mismatch');$checks++;foreach($g['offers'] as $face=>$o){$p=wc_get_product($o['id']);if(isset($spec['name'])&&$p->get_name('edit')!==$spec['name'].' — $'.$face)throw new RuntimeException('Credit name mismatch');$checks++;}}
 if(ipl_benefit_lines(" one\n\n two ")!==['one','two'])throw new RuntimeException('Checklist normalization');$checks++;foreach([str_repeat("x\n",9),str_repeat('x',501),[]] as $bad){try{ipl_benefit_lines($bad);throw new RuntimeException('Bad checklist accepted');}catch(InvalidArgumentException $e){$checks++;}}
 if(ipl_benefit_lines('<b>Safe</b>')!==['Safe'])throw new RuntimeException('Checklist plain text sanitization');$checks++;
 $admins=get_users(['role'=>'administrator','number'=>1,'fields'=>'ID']);if(!$admins)throw new RuntimeException('No owner for read-only form render');wp_set_current_user((int)$admins[0]);ob_start();ipl_admin();$form=ob_get_clean();if(substr_count($form,'[benefits]')!==count(ipl_groups())||!str_contains($form,'name="_wpnonce"')||!str_contains($form,'name="fingerprint"'))throw new RuntimeException('Owner checklist form missing guards');$checks++;
 echo wp_json_encode(['result'=>'pass','checks'=>$checks,'credit_titles_updated'=>count($after['copy']),'prices_stock_terms_orders_payments_unchanged'=>true]);
}
add_filter('pre_wp_mail',static function(){throw new RuntimeException('Unexpected mail during copy update');},999);
if(class_exists('Instapass_Notifications'))remove_action('woocommerce_update_product',['Instapass_Notifications','restocked']);
if($mode==='prepare'){if(file_exists($path))throw new RuntimeException('Backup exists');file_put_contents($path,wp_json_encode(ippc_snapshot()));chmod($path,0600);echo 'COPY_BACKUP_SAVED';return;}
$before=json_decode(file_get_contents($path),true,512,JSON_THROW_ON_ERROR);
if($mode==='verify'){ippc_verify($before);return;}
wc_transaction_query('start');try{
 if($mode==='restore'){
  foreach($before['copy'] as $id=>$fields){$p=wc_get_product($id);foreach($fields as $k=>$v)$p->{'set_'.$k}($v);$p->save();}update_option('instapass_launch_groups',$before['groups'],false);
 }elseif($mode==='refresh-batch'){
  $now=ippc_snapshot();$prior=$before['groups'];foreach($prior as &$g){$spec=ippc_specs()[$g['key']]??null;if(!$spec)continue;if($g['key']==='claude-credits'){$spec['caption']=str_replace('Claude API, Message Batches API, Playground','Claude API, Playground',$spec['caption']);$spec['benefits'][0]=str_replace('Claude API, Message Batches API, Playground','Claude API, Playground',$spec['benefits'][0]);}if($g['key']==='chatgpt-credits')$spec=ippc_prior_openai();$g=array_replace($g,$spec);}unset($g);if($now['groups']!==$prior)throw new RuntimeException('Checklist changed before Batch API follow-up');
  $groups=$now['groups'];foreach($groups as &$g)if(in_array($g['key'],['claude-credits','chatgpt-credits'],true)){$g=array_replace($g,ippc_specs()[$g['key']]);foreach($g['offers'] as $o){$p=wc_get_product($o['id']);$p->set_short_description($g['caption']);$p->save();}}unset($g);update_option('instapass_launch_groups',$groups,false);
 }elseif($mode==='apply'){
  $now=ippc_snapshot();if($now['groups']!==$before['groups']||$now['copy']!==$before['copy'])throw new RuntimeException('Content changed after backup');$groups=$now['groups'];$found=[];
  foreach($groups as &$g){$spec=ippc_specs()[$g['key']]??null;if(!$spec)continue;$found[]=$g['key'];$g=array_replace($g,$spec);if(!isset($spec['name']))continue;
   foreach($g['offers'] as $face=>$o){$p=wc_get_product($o['id']);$old=$p->get_name('edit');$p->set_name($spec['name'].' — $'.$face);$description=str_ireplace(['Claude Credits','ChatGPT Credits'],[$g['key']==='claude-credits'?'Claude API Credits':'Claude Credits',$g['key']==='chatgpt-credits'?'OpenAI API Credits':'ChatGPT Credits'],$p->get_description('edit'));$p->set_description($description);$p->set_short_description($spec['caption']);$p->save();}
  }unset($g);if(count($found)!==6)throw new RuntimeException('Expected six copy groups');update_option('instapass_launch_groups',$groups,false);
 }else throw new RuntimeException('Unknown mode');wc_transaction_query('commit');
}catch(Throwable $e){wc_transaction_query('rollback');throw $e;}
if(in_array($mode,['apply','refresh-batch'],true))ippc_verify($before);else echo 'COPY_RESTORED';
