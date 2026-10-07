import { PHP } from '@php-wasm/universal';
import { loadNodeRuntime } from '@php-wasm/node';
import fs from 'node:fs';

const php = new PHP(await loadNodeRuntime('8.3', { emscriptenOptions: { processId: 1 } }));
php.writeFile('/pricing.php', fs.readFileSync('../theme-v1.9.0/instapass/inc/pricing.php', 'utf8'));
php.writeFile('/automation.php', fs.readFileSync('../plugins/instapass-automation/instapass-automation.php', 'utf8'));

const harness = `<?php
define('ABSPATH', '/');
class WP_REST_Server { const CREATABLE = 'POST'; }
class WP_Error { public $code; public $message; public $data; function __construct($c,$m,$d=[]){$this->code=$c;$this->message=$m;$this->data=$d;} function get_error_message(){return $this->message;} }
class WP_REST_Request { private $params; function __construct($p){$this->params=$p;} function get_param($k){return $this->params[$k]??null;} }
class Fixture_Product {
  function get_id(){return 24;} function get_sku(){return 'chatgpt-plus';} function get_name(){return 'ChatGPT Plus';}
  function get_regular_price($context='view'){return '20.00';} function get_sale_price($context='view'){return '';}
  function is_type($types){return in_array('simple',(array)$types,true);} function get_date_on_sale_from(){return null;} function get_date_on_sale_to(){return null;}
  function get_status(){return 'publish';} function get_stock_status(){return 'instock';} function get_stock_quantity(){return null;} function get_manage_stock(){return false;}
}
$GLOBALS['fixture_product'] = new Fixture_Product(); $GLOBALS['authorized'] = false; $GLOBALS['routes'] = [];
function register_rest_route($ns,$route,$args){$GLOBALS['routes'][$ns.$route]=$args;}
function add_action(...$args){} function current_user_can($cap){return $GLOBALS['authorized'];}
function is_wp_error($v){return $v instanceof WP_Error;} function __( $s, $domain=null ){return $s;}
function sanitize_text_field($v){return trim(strip_tags($v));} function wp_strip_all_tags($v){return strip_tags($v);}
function wc_get_product_id_by_sku($sku){return $sku==='chatgpt-plus'?24:0;} function wc_get_product($id){return $id===24?$GLOBALS['fixture_product']:false;}
function wc_get_products($args){return $args['s']==='ChatGPT Plus'?[$GLOBALS['fixture_product']]:[];}
function wc_get_price_decimals(){return 2;} function wc_format_decimal($n,$dp=2){return number_format((float)$n,$dp,'.','');}
function get_woocommerce_currency(){return 'USD';} function wp_json_encode($v){return json_encode($v);} function rest_ensure_response($v){return $v;}
function check($ok,$message){if(!$ok)throw new Exception($message);}
include '/pricing.php'; include '/automation.php';
Instapass_Automation_Preview::register_routes();
check(isset($GLOBALS['routes']['instapass-automation/v1/commands/preview']), 'preview route missing');
check(!Instapass_Automation_Preview::can_manage(), 'anonymous capability accepted');
$GLOBALS['authorized']=true; check(Instapass_Automation_Preview::can_manage(), 'authorized owner denied');
$response=Instapass_Automation_Preview::preview_command(new WP_REST_Request(['command'=>'Give chatgpt-plus 25% off rounded']));
check(is_array($response), 'valid preview rejected');
check($response['state']==='preview_only' && $response['requires_owner_review']===true, 'preview state missing');
check($response['product_id']===24 && $response['before']['regular_price']==='20.00', 'wrong product/before values');
check($response['after']['regular_price']==='20.00' && $response['after']['sale_price']==='14.99', 'wrong proposed prices');
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
console.log('PASS preview route registration, capability gate, 25% .99 calculation, output and invalid-discount rejection');
