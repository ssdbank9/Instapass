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
define('ABSPATH', '/');
class WP_REST_Server { const CREATABLE = 'POST'; const READABLE = 'GET'; }
class WP_Error { public $code; public $message; public $data; function __construct($c,$m,$d=[]){$this->code=$c;$this->message=$m;$this->data=$d;} function get_error_message(){return $this->message;} }
class WP_REST_Request { private $params; function __construct($p){$this->params=$p;} function get_param($k){return $this->params[$k]??null;} }
class Fixture_Product {
  function get_id(){return 24;} function get_sku(){return 'chatgpt-plus';} function get_name(){return 'ChatGPT Plus';}
  function get_regular_price($context='view'){return '20.00';} function get_sale_price($context='view'){return '';}
  function is_type($types){return in_array('simple',(array)$types,true);} function get_date_on_sale_from(){return null;} function get_date_on_sale_to(){return null;}
  function get_status(){return 'publish';} function get_stock_status(){return 'instock';} function get_stock_quantity(){return null;} function get_manage_stock(){return false;}
}
$GLOBALS['fixture_product'] = new Fixture_Product(); $GLOBALS['authorized'] = false; $GLOBALS['routes'] = []; $GLOBALS['actions'] = []; $GLOBALS['submenu'] = null; $GLOBALS['assets'] = [];
class Preview_DB { public $prefix='wp_'; public $rows=[]; function insert($t,$d,$f=[]){$this->rows[$d['operation_id']]=$d;return 1;} function get_charset_collate(){return '';} }
$GLOBALS['wpdb']=new Preview_DB();
function register_rest_route($ns,$route,$args){$GLOBALS['routes'][$ns.$route]=$args;}
function add_action($hook,$callback){$GLOBALS['actions'][$hook]=$callback;}
function register_activation_hook($file,$callback){$GLOBALS['activation_callback']=$callback;}
function add_submenu_page(...$args){$GLOBALS['submenu']=$args;}
function current_user_can($cap,$id=null){return $GLOBALS['authorized'] && ('manage_woocommerce'===$cap || ('edit_post'===$cap && (int)$id===24));}
function is_wp_error($v){return $v instanceof WP_Error;} function __( $s, $domain=null ){return $s;}
function esc_html__($s,$domain=null){return htmlspecialchars($s,ENT_QUOTES,'UTF-8');} function esc_html($s){return htmlspecialchars($s,ENT_QUOTES,'UTF-8');}
function esc_url_raw($s){return $s;} function wp_create_nonce($action){return 'fixture-nonce';} function rest_url($path){return '/wp-json/'.$path;}
function current_time($type,$gmt=false){return 'timestamp'===$type?time():gmdate('Y-m-d H:i:s');} function get_current_user_id(){return 7;} function wp_generate_uuid4(){return '00000000-0000-4000-8000-000000000001';}
function plugin_dir_url($file){return '/plugins/instapass-automation/';}
function wp_enqueue_script(...$args){$GLOBALS['assets']['script']=$args;} function wp_enqueue_style(...$args){$GLOBALS['assets']['style']=$args;}
function wp_localize_script(...$args){$GLOBALS['assets']['localized']=$args;}
function wp_die($message){throw new Exception($message);}
function sanitize_text_field($v){return trim(strip_tags($v));} function wp_strip_all_tags($v){return strip_tags($v);}
function wc_get_product_id_by_sku($sku){return $sku==='chatgpt-plus'?24:0;} function wc_get_product($id){return $id===24?$GLOBALS['fixture_product']:false;}
function wc_get_products($args){return $args['s']==='ChatGPT Plus'?[$GLOBALS['fixture_product']]:[];}
function wc_get_price_decimals(){return 2;} function wc_format_decimal($n,$dp=2){return number_format((float)$n,$dp,'.','');}
function get_woocommerce_currency(){return 'USD';} function wp_json_encode($v){return json_encode($v);} function rest_ensure_response($v){return $v;}
function check($ok,$message){if(!$ok)throw new Exception($message);}
include '/pricing.php'; include '/automation.php';
Instapass_Automation_Preview::register_routes();
check(isset($GLOBALS['routes']['instapass-automation/v1/commands/preview']), 'preview route missing');
check(isset($GLOBALS['actions']['admin_menu']) && isset($GLOBALS['actions']['admin_enqueue_scripts']) && isset($GLOBALS['activation_callback']), 'admin UI/database hooks missing');
Instapass_Automation_Preview::register_admin_page();
check($GLOBALS['submenu'][0]==='woocommerce' && $GLOBALS['submenu'][3]==='manage_woocommerce', 'admin page capability/placement incorrect');
try { Instapass_Automation_Preview::render_admin_page(); throw new Exception('unauthorized admin page rendered'); } catch (Exception $e) { check($e->getMessage()==='You do not have permission to view this page.', 'unexpected unauthorized page result'); }
check(!Instapass_Automation_Preview::can_manage(), 'anonymous capability accepted');
$GLOBALS['authorized']=true; check(Instapass_Automation_Preview::can_manage(), 'authorized owner denied');
ob_start(); Instapass_Automation_Preview::render_admin_page(); $page=ob_get_clean();
check(str_contains($page,'instapass-preview-form') && str_contains($page,'Preview change') && str_contains($page,'explicit confirmation') && str_contains($page,'instapass-operation-history'), 'admin pricing workflow form incomplete');
check(!str_contains($page,'Apply change'), 'admin form unexpectedly offers product writes');
Instapass_Automation_Preview::enqueue_admin_assets('woocommerce_page_instapass-pricing-preview');
check($GLOBALS['assets']['localized'][2]['nonce']==='fixture-nonce' && str_contains($GLOBALS['assets']['localized'][2]['urls']['preview'],'/commands/preview') && str_contains($GLOBALS['assets']['localized'][2]['urls']['undo'],'/commands/undo'), 'admin REST nonce/URLs missing');
$GLOBALS['assets']=[]; Instapass_Automation_Preview::enqueue_admin_assets('dashboard_page_instapass-pricing-preview');
check($GLOBALS['assets']===[], 'preview assets loaded outside the plugin page');
$response=Instapass_Automation_Preview::preview_command(new WP_REST_Request(['command'=>'Give chatgpt-plus 25% off rounded']));
check(is_array($response), 'valid preview rejected');
check($response['state']==='pending_confirmation' && $response['requires_owner_review']===true && isset($response['operation_id']), 'saved preview state missing');
check($response['product_id']===24 && $response['before']['regular_price']==='20.00', 'wrong product/before values');
check($response['after']['regular_price']==='20.00' && $response['after']['sale_price']==='14.99', 'wrong proposed prices');
check(isset($GLOBALS['wpdb']->rows[$response['operation_id']]) && $GLOBALS['wpdb']->rows[$response['operation_id']]['state']==='pending', 'server-side preview was not persisted');
check(abs($response['actual_discount_percent']-25.05)<0.001, 'actual discount missing');
$invalid=Instapass_Automation_Preview::preview_command(new WP_REST_Request(['command'=>'Give chatgpt-plus 100% off']));
check(is_wp_error($invalid) && $invalid->code==='invalid_discount', 'invalid discount accepted');
echo 'PREVIEW_HARNESS_OK';
`;

const result = await php.run({ code: harness });
php.exit();
if (!result.text.includes('PREVIEW_HARNESS_OK')) {
  process.stderr.write(result.text + result.errors);
  process.exit(1);
}
console.log('PASS admin menu/page access, scoped assets, REST nonce, owner confirmation/history controls, saved preview, capability gate, 25% .99 calculation and invalid-discount rejection');
