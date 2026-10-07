import { PHP } from '@php-wasm/universal';
import { loadNodeRuntime } from '@php-wasm/node';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const projectRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const php = new PHP(await loadNodeRuntime('8.3', { emscriptenOptions: { processId: 1 } }));
php.writeFile('/pricing.php', fs.readFileSync(path.join(projectRoot, 'theme-v1.9.0/instapass/inc/pricing.php'), 'utf8'));
php.writeFile('/automation.php', fs.readFileSync(path.join(projectRoot, 'plugins/instapass-automation/instapass-automation.php'), 'utf8'));

const harness = `<?php
define('ABSPATH', '/'); define('ARRAY_A', 'ARRAY_A'); define('DB_NAME','instapass_test'); mkdir('/wp-admin/includes',0777,true); file_put_contents('/wp-admin/includes/upgrade.php','<?php');
class WP_REST_Server { const CREATABLE='POST'; const READABLE='GET'; }
class WP_Error { public $code; public $message; public $data; function __construct($c,$m,$d=[]){$this->code=$c;$this->message=$m;$this->data=$d;} function get_error_message(){return $this->message;} }
class WP_REST_Request { private $params; function __construct($p){$this->params=$p;} function get_param($k){return $this->params[$k]??null;} }
class FakeDB {
  public $prefix='wp_'; public $options='wp_options'; public $rows=[]; public $option_rows=[]; public $last_error='';
  function get_charset_collate(){return '';}
  function prepare($sql,...$args){foreach($args as $arg){$quoted=is_string($arg)?"'".addslashes($arg)."'":(string)(int)$arg;$sql=preg_replace('/%[sd]/',$quoted,$sql,1);}return $sql;}
  function insert($table,$data,$formats=[]){if($table===$this->options){if(isset($this->option_rows[$data['option_name']]))return false;$this->option_rows[$data['option_name']]=$data['option_value'];return 1;}if(isset($this->rows[$data['operation_id']]))return false;$this->rows[$data['operation_id']]=array_merge(['fingerprint_after'=>'','confirmed_at'=>null,'undone_at'=>null],$data);return 1;}
  function get_row($sql,$output=null){preg_match("/operation_id = '([^']+)'/",$sql,$id);preg_match('/user_id = (\\d+)/',$sql,$uid);$row=$this->rows[$id[1]??'']??null;return $row && (int)$row['user_id']===(int)($uid[1]??-1)?$row:null;}
  function get_results($sql,$output=null){preg_match('/user_id = (\\d+)/',$sql,$uid);$rows=array_values(array_filter($this->rows,fn($r)=>(int)$r['user_id']===(int)($uid[1]??-1)));usort($rows,fn($a,$b)=>strcmp($b['created_at'],$a['created_at']));return array_slice($rows,0,30);}
  function query($sql){preg_match("/SET state = '([^']+)' WHERE operation_id = '([^']+)' AND user_id = (\\d+) AND state = '([^']+)'/",$sql,$m);if(!$m)return false;$r=$this->rows[$m[2]]??null;if(!$r||(int)$r['user_id']!==(int)$m[3]||$r['state']!==$m[4])return 0;if(preg_match("/expires_at > '([^']+)'/",$sql,$expiry)&&strtotime($r['expires_at'].' UTC')<=strtotime($expiry[1].' UTC'))return 0;$r['state']=$m[1];$this->rows[$m[2]]=$r;return 1;}
  function update($table,$data,$where,$formats=[],$where_formats=[]){if($table===$this->options){$name=$where['option_name']??'';if(!array_key_exists($name,$this->option_rows)||(string)$this->option_rows[$name] !== (string)($where['option_value']??''))return 0;$this->option_rows[$name]=$data['option_value'];return 1;}foreach($this->rows as $id=>$row){$match=true;foreach($where as $k=>$v){if((string)$row[$k] !== (string)$v){$match=false;break;}}if($match){$this->rows[$id]=array_merge($row,$data);return 1;}}return 0;}
  function delete($table,$where,$formats=[]){if($table!==$this->options)return 0;$name=$where['option_name']??'';if(!array_key_exists($name,$this->option_rows)||(string)$this->option_rows[$name] !== (string)($where['option_value']??''))return 0;unset($this->option_rows[$name]);return 1;}
  function get_var($sql){preg_match("/SELECT option_value FROM wp_options WHERE option_name = '([^']+)'/",$sql,$m);return $m?($this->option_rows[$m[1]]??null):null;}
}
class Fixture_Product {
  public $regular='20.00'; public $sale=''; public $save_count=0; public $throw_after_save=false; public $price='20.00';
  function get_id(){return 24;} function get_sku(){return 'chatgpt-plus';} function get_name(){return 'ChatGPT Plus';}
  function get_regular_price($context='view'){return $this->regular;} function get_sale_price($context='view'){return $this->sale;}
  function get_date_on_sale_from(){return null;} function get_date_on_sale_to(){return null;} function get_status(){return 'publish';}
  function get_stock_status(){return 'instock';} function get_stock_quantity(){return null;} function get_manage_stock(){return false;}
  function is_type($types){return in_array('simple',(array)$types,true);} function is_on_sale($context='view'){return $this->sale!==''&&(float)$this->sale<(float)$this->regular;}
  function set_regular_price($v){$this->regular=(string)$v;} function set_sale_price($v){$this->sale=(string)$v;} function set_price($v){$this->price=(string)$v;}
  function save(){$this->save_count++;if($this->throw_after_save)throw new RuntimeException('fixture save interruption');return 24;}
}
$GLOBALS['wpdb']=new FakeDB(); $GLOBALS['fixture_product']=new Fixture_Product(); $GLOBALS['authorized']=true; $GLOBALS['current_user']=7; $GLOBALS['routes']=[]; $GLOBALS['actions']=[]; $GLOBALS['uuid']=0;
function register_rest_route($ns,$route,$args){$GLOBALS['routes'][$ns.$route]=$args;} function add_action($hook,$cb){$GLOBALS['actions'][$hook]=$cb;}
function register_activation_hook($file,$cb){$GLOBALS['activation_callback']=$cb;} function add_submenu_page(...$args){} function plugin_dir_url($f){return '/plugins/';}
function wp_enqueue_script(...$args){} function wp_enqueue_style(...$args){} function wp_localize_script(...$args){} function wp_die($m){throw new Exception($m);}
function current_user_can($cap,$id=null){return $GLOBALS['authorized']&&('manage_woocommerce'===$cap||('edit_post'===$cap&&(int)$id===24));}
function get_current_user_id(){return $GLOBALS['current_user'];} function current_time($type,$gmt=false){return 'timestamp'===$type?time():gmdate('Y-m-d H:i:s');}
function dbDelta($sql){$GLOBALS['schema_sql']=$sql;return [];}
function wp_cache_delete($key,$group=''){return true;}
function wp_generate_uuid4(){$GLOBALS['uuid']++;return sprintf('00000000-0000-4000-8000-%012d',$GLOBALS['uuid']);}
function is_wp_error($v){return $v instanceof WP_Error;} function __($s,$d=null){return $s;} function esc_html__($s,$d=null){return htmlspecialchars($s,ENT_QUOTES,'UTF-8');} function esc_html($s){return htmlspecialchars($s,ENT_QUOTES,'UTF-8');}
function esc_url_raw($s){return $s;} function wp_create_nonce($a){return 'nonce';} function rest_url($p){return '/wp-json/'.$p;} function wp_json_encode($v){return json_encode($v);}
function sanitize_text_field($s){return trim(strip_tags($s));} function wp_strip_all_tags($s){return strip_tags($s);} function rest_ensure_response($v){return $v;}
function wc_get_product_id_by_sku($s){return $s==='chatgpt-plus'?24:0;} function wc_get_product($id){return (int)$id===24?$GLOBALS['fixture_product']:false;}
function wc_get_products($a){return $a['s']==='ChatGPT Plus'?[$GLOBALS['fixture_product']]:[];} function wc_get_price_decimals(){return 2;}
function wc_format_decimal($v,$dp=2){return ''===(string)$v?'':number_format((float)$v,$dp,'.','');} function get_woocommerce_currency(){return 'USD';}
function check($ok,$message){if(!$ok)throw new Exception($message);} function err($v,$code){return $v instanceof WP_Error&&$v->code===$code;}
include '/pricing.php'; include '/automation.php';
Instapass_Automation_Preview::register_routes();
check(isset($GLOBALS['routes']['instapass-automation/v1/commands/preview'],$GLOBALS['routes']['instapass-automation/v1/commands/confirm'],$GLOBALS['routes']['instapass-automation/v1/commands/undo'],$GLOBALS['routes']['instapass-automation/v1/commands/history']),'REST routes incomplete');
check($GLOBALS['routes']['instapass-automation/v1/commands/confirm']['permission_callback'](),'owner confirm permission denied');
$GLOBALS['authorized']=false;check(!$GLOBALS['routes']['instapass-automation/v1/commands/confirm']['permission_callback'](),'unauthorized confirmation permission accepted');$GLOBALS['authorized']=true;
call_user_func($GLOBALS['activation_callback']);
check(str_contains($GLOBALS['schema_sql'],'wp_instapass_price_operations')&&str_contains($GLOBALS['schema_sql'],'PRIMARY KEY  (operation_id)')&&str_contains($GLOBALS['schema_sql'],'audit_log longtext'),'activation schema/migration incomplete');
$preview=Instapass_Automation_Preview::preview_command(new WP_REST_Request(['command'=>'Give chatgpt-plus 25% off rounded']));
check($preview['state']==='pending_confirmation'&&isset($preview['operation_id']),'preview was not persisted');
$id=$preview['operation_id']; $applied=Instapass_Automation_Preview::confirm_operation(new WP_REST_Request(['operation_id'=>$id]));
check($applied['state']==='applied'&&$GLOBALS['fixture_product']->sale==='14.99','explicit confirmation did not apply expected sale price');
check($GLOBALS['wpdb']->rows[$id]['confirmed_at']!==null,'confirmation timestamp was not recorded');
$saves=$GLOBALS['fixture_product']->save_count; $retry=Instapass_Automation_Preview::confirm_operation(new WP_REST_Request(['operation_id'=>$id]));
check($retry['state']==='already_applied'&&$GLOBALS['fixture_product']->save_count===$saves,'duplicate confirmation reapplied product');
$GLOBALS['wpdb']->update('', ['expires_at'=>gmdate('Y-m-d H:i:s',time()-10)], ['operation_id'=>$id]);
$undone=Instapass_Automation_Preview::undo_operation(new WP_REST_Request(['operation_id'=>$id]));
check($undone['state']==='undone'&&$GLOBALS['fixture_product']->sale===''&&$GLOBALS['fixture_product']->regular==='20.00','guarded undo failed to restore prior prices');
check($GLOBALS['wpdb']->rows[$id]['undone_at']!==null,'undo timestamp was not recorded');
check(Instapass_Automation_Preview::undo_operation(new WP_REST_Request(['operation_id'=>$id]))['state']==='already_undone','duplicate undo was not idempotent');
$busy=Instapass_Automation_Preview::preview_command(new WP_REST_Request(['command'=>'Set chatgpt-plus sale price to 11.99']));
$lockName='_instapass_price_lock_'.substr(hash('sha256','instapass_testwp_:1:24'),0,48);$GLOBALS['wpdb']->option_rows[$lockName]=wp_generate_uuid4().':'.(time()+60);
check(err(Instapass_Automation_Preview::confirm_operation(new WP_REST_Request(['operation_id'=>$busy['operation_id']])), 'product_busy')&&$GLOBALS['wpdb']->rows[$busy['operation_id']]['state']==='pending','concurrent same-product operation was not held safely');
unset($GLOBALS['wpdb']->option_rows[$lockName]);
check(Instapass_Automation_Preview::confirm_operation(new WP_REST_Request(['operation_id'=>$busy['operation_id']]))['state']==='applied','retry after same-product lock released failed');
check(!isset($GLOBALS['wpdb']->option_rows[$lockName]),'successful confirmation did not release its database lock');
check(Instapass_Automation_Preview::undo_operation(new WP_REST_Request(['operation_id'=>$busy['operation_id']]))['state']==='undone','same-product lock was not released after apply');
$regularChange=Instapass_Automation_Preview::preview_command(new WP_REST_Request(['command'=>'Set chatgpt-plus regular price to 25']));
check(Instapass_Automation_Preview::confirm_operation(new WP_REST_Request(['operation_id'=>$regularChange['operation_id']]))['state']==='applied'&&$GLOBALS['fixture_product']->regular==='25.00','regular-price command did not apply exact amount');
check(Instapass_Automation_Preview::undo_operation(new WP_REST_Request(['operation_id'=>$regularChange['operation_id']]))['state']==='undone'&&$GLOBALS['fixture_product']->regular==='20.00','regular-price undo failed');
$stale=Instapass_Automation_Preview::preview_command(new WP_REST_Request(['command'=>'Set chatgpt-plus sale price to 13.99']));
$GLOBALS['fixture_product']->regular='21.00'; $saves=$GLOBALS['fixture_product']->save_count;
check(err(Instapass_Automation_Preview::confirm_operation(new WP_REST_Request(['operation_id'=>$stale['operation_id']])), 'stale_preview'),'stale preview was accepted');
check($GLOBALS['fixture_product']->save_count===$saves&&$GLOBALS['wpdb']->rows[$stale['operation_id']]['state']==='stale','stale preview changed product or missed audit status');
$GLOBALS['fixture_product']->regular='20.00';
$staleUndo=Instapass_Automation_Preview::preview_command(new WP_REST_Request(['command'=>'Set chatgpt-plus sale price to 12.99']));
check(Instapass_Automation_Preview::confirm_operation(new WP_REST_Request(['operation_id'=>$staleUndo['operation_id']]))['state']==='applied','fixture operation did not apply');
$GLOBALS['fixture_product']->regular='22.00'; $GLOBALS['fixture_product']->save(); $saves=$GLOBALS['fixture_product']->save_count;
check(err(Instapass_Automation_Preview::undo_operation(new WP_REST_Request(['operation_id'=>$staleUndo['operation_id']])), 'undo_stale'),'undo overwrote a later product change');
check($GLOBALS['fixture_product']->save_count===$saves&&$GLOBALS['wpdb']->rows[$staleUndo['operation_id']]['state']==='undo_stale','stale undo mutated product or missed state');
$lockName='_instapass_price_lock_'.substr(hash('sha256','instapass_testwp_:1:24'),0,48);
$staleLock=Instapass_Automation_Preview::preview_command(new WP_REST_Request(['command'=>'Set chatgpt-plus sale price to 12.49']));
$GLOBALS['wpdb']->option_rows[$lockName]=wp_generate_uuid4().':'.(time()-1);
check(Instapass_Automation_Preview::confirm_operation(new WP_REST_Request(['operation_id'=>$staleLock['operation_id']]))['state']==='applied','expired orphaned product lock was not reclaimed safely');
check(!isset($GLOBALS['wpdb']->option_rows[$lockName]),'reclaimed product lock was not released after confirmation');
check(Instapass_Automation_Preview::undo_operation(new WP_REST_Request(['operation_id'=>$staleLock['operation_id']]))['state']==='undone','expired-lock test operation could not be undone');
$expired=Instapass_Automation_Preview::preview_command(new WP_REST_Request(['command'=>'Set chatgpt-plus sale price to 10.99']));
$GLOBALS['wpdb']->update('', ['expires_at'=>gmdate('Y-m-d H:i:s',time()-10)], ['operation_id'=>$expired['operation_id']]);
check(err(Instapass_Automation_Preview::confirm_operation(new WP_REST_Request(['operation_id'=>$expired['operation_id']])), 'preview_expired'),'expired preview was accepted');
$GLOBALS['fixture_product']->throw_after_save=true;
$partial=Instapass_Automation_Preview::preview_command(new WP_REST_Request(['command'=>'Set chatgpt-plus sale price to 9.99']));
check(err(Instapass_Automation_Preview::confirm_operation(new WP_REST_Request(['operation_id'=>$partial['operation_id']])), 'apply_needs_review'),'partial save did not fail closed');
$saves=$GLOBALS['fixture_product']->save_count;
check(err(Instapass_Automation_Preview::confirm_operation(new WP_REST_Request(['operation_id'=>$partial['operation_id']])), 'operation_not_pending')&&$GLOBALS['fixture_product']->save_count===$saves,'partial failure was automatically retried');
$GLOBALS['fixture_product']->throw_after_save=false;
$corrupt=Instapass_Automation_Preview::preview_command(new WP_REST_Request(['command'=>'Set chatgpt-plus sale price to 8.99']));
$GLOBALS['wpdb']->rows[$corrupt['operation_id']]['payload']=json_encode(['operation_id'=>$corrupt['operation_id'],'product_id'=>24,'fingerprint'=>'bad']);
$saves=$GLOBALS['fixture_product']->save_count;
check(err(Instapass_Automation_Preview::confirm_operation(new WP_REST_Request(['operation_id'=>$corrupt['operation_id']])), 'invalid_stored_preview')&&$GLOBALS['fixture_product']->save_count===$saves,'corrupt stored preview reached the product writer');
$history=Instapass_Automation_Preview::operation_history(); $appliedHistory=null; $corruptHistory=null;foreach($history as $item){if($item['operation_id']===$id)$appliedHistory=$item;if($item['operation_id']===$corrupt['operation_id'])$corruptHistory=$item;}
check(count($history)===9&&is_array($appliedHistory)&&count($appliedHistory['audit_log'])>=3,'owner audit history missing operations or events');
check(is_array($corruptHistory)&&in_array($corruptHistory['state'],['manual_review','corrupt_record'],true)&&$corruptHistory['name']==='Needs manual review','malformed but decodable operation payload was hidden or trusted in owner history');
$GLOBALS['current_user']=8;
check(err(Instapass_Automation_Preview::confirm_operation(new WP_REST_Request(['operation_id'=>$id])), 'operation_not_found'),'another account accessed private operation');
echo 'WORKFLOW_HARNESS_OK';
`;

const result = await php.run({ code: harness });
php.exit();
if (!result.text.includes('WORKFLOW_HARNESS_OK')) {
  process.stderr.write(result.text + result.errors);
  process.exit(1);
}
console.log('PASS activation schema, permission gates, saved preview, owner confirm, idempotency, product lock, expiry, corrupt/stale preview refusal, audit history, guarded undo and manual-review failure lock');
