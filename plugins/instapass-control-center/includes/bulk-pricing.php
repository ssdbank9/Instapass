<?php
if (!defined('ABSPATH')) exit;

final class Instapass_Control_Bulk {
    const NS='instapass-control/v1';
    const TTL=900;
    const LOCK='instapass_control_bulk_lock';
    private static $page_hook='';

    public static function init() {
        add_action('admin_menu',[__CLASS__,'menu'],60);
        add_action('admin_enqueue_scripts',[__CLASS__,'assets']);
        add_action('rest_api_init',[__CLASS__,'routes']);
    }
    public static function menu() {
        self::$page_hook=add_submenu_page('instapass-control','Pricing & Discounts','Pricing & Discounts','manage_woocommerce','instapass-control-pricing',[__CLASS__,'page']);
    }
    public static function assets($hook) {
        if(!self::$page_hook||$hook!==self::$page_hook) return;
        wp_enqueue_script('ip-bulk-xlsx',plugins_url('../assets/xlsx.full.min.js',__FILE__),[], '0.20.3',true);
        wp_enqueue_script('ip-bulk-pricing',plugins_url('../assets/bulk-pricing.js',__FILE__),['ip-bulk-xlsx'],'0.4.0.1',true);
        wp_localize_script('ip-bulk-pricing','InstapassBulk',['url'=>rest_url(self::NS.'/'),'nonce'=>wp_create_nonce('wp_rest')]);
        wp_enqueue_style('ip-bulk-pricing',plugins_url('../assets/bulk-pricing.css',__FILE__),[], '0.4.0');
    }
    public static function page() {
        if(!current_user_can('manage_woocommerce')) wp_die('Permission denied.');
        ?>
        <div class="wrap ipcb"><h1>Pricing & Discounts</h1>
        <p>USD changes update the store’s base price. GBP changes update separate GBP prices only. PKR normally follows USD and your saved rate. Fixed PKR overrides are edited under Products > Edit > General and are unaffected by USD/GBP bulk changes.</p>
        <p>Every bulk action starts with a review preview. Prices change only after you choose Apply. Existing prices, stock and product visibility are kept separate.</p>
        <section class="ipcb-controls">
        <label>Currency <select id="ipcb-currency"><option value="USD">USD</option><option value="GBP">GBP</option></select></label>
        <label>Discount % <input id="ipcb-discount" type="number" min="0" max="99.99" step="0.01" value="25"></label>
        <label class="ipcb-usd"><input id="ipcb-round99" type="checkbox" checked disabled> USD sale prices use .99 endings where that keeps the selected discount rule</label>
        <button class="button" id="ipcb-global">Preview global discount</button>
        <button class="button" id="ipcb-random-global">Preview random 25–30% for all</button>
        <button class="button" id="ipcb-selected">Preview selected products</button>
        <button class="button" id="ipcb-random-selected">Preview random 25–30% for selected</button>
        </section>
        <p><button class="button" id="ipcb-template">Download Excel template</button> <label>Upload updated prices <input id="ipcb-file" type="file" accept=".xlsx,.csv"></label></p>
        <p class="description">Excel columns: SKU, Currency, Regular price, Discount %, Sale price. Currency must be USD or GBP. Enter a discount or sale price per row, not both; leave unmodified rows blank. Set Discount % to 0 to remove a sale. Formulas, duplicate SKU/currency pairs and unsupported currencies are rejected. Fixed PKR overrides remain unchanged; otherwise PKR follows USD. Exact USD prices are accepted for listings with Allow exact USD prices enabled.</p>
        <label>Find product <input type="search" id="ipcb-search"></label> <button class="button" id="ipcb-select-visible">Select visible</button> <button class="button" id="ipcb-clear">Clear selection</button>
        <p id="ipcb-status" role="status" aria-live="polite">Loading products…</p>
        <div class="ipcb-scroll"><table class="widefat striped"><thead><tr><th>Select</th><th>Product / SKU</th><th>USD regular</th><th>USD sale</th><th>GBP regular</th><th>GBP sale</th><th>Stock</th></tr></thead><tbody id="ipcb-catalog"></tbody></table></div>
        <section id="ipcb-preview" hidden><h2>Review proposed changes</h2><p id="ipcb-summary"></p><div class="ipcb-scroll"><table class="widefat striped"><thead><tr><th>Product</th><th>Currency</th><th>Before</th><th>After</th><th>Discount</th></tr></thead><tbody id="ipcb-changes"></tbody></table></div><p><button class="button button-primary" id="ipcb-apply">Apply preview</button> <button class="button" id="ipcb-cancel">Cancel</button></p></section>
        <section id="ipcb-history"><h2>Recent changes</h2></section></div>
        <?php
    }
    public static function routes() {
        foreach(['catalog'=>'GET','preview'=>'POST','apply'=>'POST','history'=>'GET','undo'=>'POST'] as $name=>$method)
            register_rest_route(self::NS,'/'.$name,['methods'=>$method,'callback'=>[__CLASS__,$name],'permission_callback'=>static function(){return current_user_can('manage_woocommerce');}]);
    }
    private static function products() {
        $items=wc_get_products(['limit'=>501,'status'=>['publish','draft','pending','private','future'],'type'=>['simple'],'orderby'=>'title','order'=>'ASC']);
        if(count($items)>500) return new WP_Error('catalog_limit','Catalog exceeds 500 simple products. No change was made.',['status'=>400]);
        return array_values(array_filter($items,static fn($p)=>current_user_can('edit_post',$p->get_id())));
    }
    private static function snap($p,$currency) {
        if($currency==='GBP') return ['regular'=>(string)$p->get_meta('_instapass_gbp_regular_price',true,'edit'),'sale'=>(string)$p->get_meta('_instapass_gbp_sale_price',true,'edit')];
        return ['regular'=>(string)$p->get_regular_price('edit'),'sale'=>(string)$p->get_sale_price('edit')];
    }
    private static function fp($p,$currency) { return hash('sha256',wp_json_encode([$currency,self::snap($p,$currency)])); }
    private static function amount($v,$label,$allow_blank=true) {
        if(!is_string($v)) return new WP_Error('invalid_amount',"$label must be a plain amount.");
        $v=trim($v);
        if($v===''&&$allow_blank) return '';
        if(!preg_match('/^(0|[1-9][0-9]{0,5})(?:\.([0-9]{1,2}))?$/D',$v)) return new WP_Error('invalid_amount',"$label must have at most two decimal places and no currency symbol.");
        return number_format((float)$v,2,'.','');
    }
    private static function random_values($currency,$regular,$round99) {
        $base=(int)round((float)$regular*100,0,PHP_ROUND_HALF_UP);
        $low=(int)ceil($base*0.70-0.000001);$high=(int)floor($base*0.75+0.000001);
        if($base<=0||$low>$high)return new WP_Error('random_unavailable','This price cannot represent a 25–30% discount at cents precision.');
        $target=(int)round($base*(1-random_int(25,30)/100),0,PHP_ROUND_HALF_UP);
        if($currency==='USD'&&$round99){$first=(int)ceil(($low-99)/100);$last=(int)floor(($high-99)/100);if($first<=$last){$n=(int)round(($target-99)/100);$target=max($first,min($last,$n))*100+99;}}
        $target=max($low,min($high,$target));
        return ['regular'=>number_format($base/100,2,'.',''),'sale'=>number_format($target/100,2,'.','')];
    }
    private static function values($currency,$before,$row,$round99,$exact_usd=false) {
        foreach(['regular','sale','discount'] as $k) if(isset($row[$k])&&!is_string($row[$k])) return new WP_Error('invalid_row','Price fields must be text values.');
        if(trim($row['discount']??'')!==''&&trim($row['sale']??'')!=='') return new WP_Error('conflicting_price','Enter a discount or sale price, not both.');
        $regular=self::amount((string)($row['regular']??$before['regular']),'Regular price');
        if(is_wp_error($regular)) return $regular;
        $sale=self::amount((string)($row['sale']??$before['sale']),'Sale price');
        if(is_wp_error($sale)) return $sale;
        if(!empty($row['random'])) return self::random_values($currency,$regular,$round99);
        $discount=trim((string)($row['discount']??''));
        if($discount==='0'||$discount==='0.0'||$discount==='0.00'){$sale='';$discount='';}
        if($discount!=='') {
            if(!preg_match('/^(?:[0-9]{1,2})(?:\.[0-9]{1,2})?$/D',$discount)||(float)$discount<=0||(float)$discount>=100) return new WP_Error('invalid_discount','Discount must be above 0% and below 100%.');
            if($regular===''||(float)$regular<=0) return new WP_Error('missing_regular','Set a positive regular price before applying a discount.');
            $cents=(int)round((float)$regular*100*(1-(float)$discount/100),0,PHP_ROUND_HALF_UP);
            if($currency==='USD'&&$round99&&$cents>=99) {
                $target=$cents; $candidate=(int)(floor($target/100)*100+99);
                if($candidate>$target)$candidate-=100;
                if($candidate>0&&$candidate<(float)$regular*100)$cents=$candidate;
            }
            $sale=number_format($cents/100,2,'.','');
        }
        if($regular!==''&&(float)$regular<=0) return new WP_Error('invalid_regular','Regular price must be positive; leave it blank to preserve an unavailable price.');
        if($currency==='USD'&&!$exact_usd&&isset($row['regular'])&&$row['regular']!==''&&substr($regular,-3)!=='.99') return new WP_Error('usd_ending','Entered USD regular prices must end in .99.');
        if($currency==='USD'&&!$exact_usd&&isset($row['sale'])&&$row['sale']!==''&&$discount===''&&substr($sale,-3)!=='.99') return new WP_Error('usd_ending','Entered USD sale prices must end in .99.');
        if($sale!==''&&($regular===''||(float)$sale>=(float)$regular)) return new WP_Error('invalid_sale','Sale price must be below a positive regular price.');
        if($sale==='0.00') return new WP_Error('free_sale','Zero-priced sales are not allowed.');
        return ['regular'=>$regular,'sale'=>$sale];
    }
    public static function catalog() {
        $products=self::products(); if(is_wp_error($products)) return $products;
        $rows=[];
        foreach($products as $p) $rows[]=['id'=>$p->get_id(),'sku'=>$p->get_sku(),'name'=>$p->get_name(),'usd'=>self::snap($p,'USD'),'gbp'=>self::snap($p,'GBP'),'stock'=>$p->get_stock_status()];
        return ['rows'=>$rows,'count'=>count($rows)];
    }
    public static function preview($request) {
        $body=$request->get_json_params(); if(!is_array($body)) return new WP_Error('invalid_request','Send a valid request.',['status'=>400]);
        $mode=$body['mode']??''; $currency=$body['currency']??'USD';
        if(!is_string($mode)||!is_string($currency)||(array_key_exists('discount',$body)&&!is_string($body['discount']))) return new WP_Error('invalid_request','Operation, currency and discount must be text values.',['status'=>400]);
        $currency=strtoupper($currency);
        if(!in_array($mode,['global','selected','random_global','random_selected','import'],true)||!in_array($currency,['USD','GBP'],true)) return new WP_Error('invalid_request','Choose USD or GBP and a supported operation. PKR is calculated from USD.',['status'=>400]);
        $products=self::products(); if(is_wp_error($products)) return $products;
        $bySku=[]; foreach($products as $p) if($p->get_sku()!=='') $bySku[strtolower($p->get_sku())]=$p;
        $targets=[];
        if($mode==='import') {
            $rows=$body['rows']??null; if(!is_array($rows)||!count($rows)||count($rows)>500) return new WP_Error('invalid_rows','Import between 1 and 500 rows.',['status'=>400]);
            $seen=[];
            foreach($rows as $i=>$row) {
                if(!is_array($row)||!isset($row['sku'])||!is_string($row['sku'])) return new WP_Error('invalid_row','Row '.($i+2).': SKU is required.',['status'=>400]);
                foreach(['sku','currency','regular','sale','discount'] as $field) if(array_key_exists($field,$row)&&!is_string($row[$field])) return new WP_Error('invalid_row','Row '.($i+2).': fields must be text values.',['status'=>400]);
                if(array_diff(array_keys($row),['sku','currency','regular','sale','discount'])) return new WP_Error('invalid_row','Row '.($i+2).': unsupported field.',['status'=>400]);
                $sku=strtolower(trim($row['sku']));
                $cur=strtoupper((string)($row['currency']??'')); if(!in_array($cur,['USD','GBP'],true)) return new WP_Error('unsupported_currency','Row '.($i+2).': use USD or GBP. PKR is calculated, not imported.',['status'=>400]);
                $key=$cur.':'.$sku; if(isset($seen[$key])) return new WP_Error('duplicate_sku','Row '.($i+2).': duplicate SKU and currency.',['status'=>400]); $seen[$key]=true;
                $p=$bySku[$sku]??null; if(!$p) return new WP_Error('unknown_sku','Row '.($i+2).': SKU not found.',['status'=>400]);
                $targets[]=['product'=>$p,'currency'=>$cur,'row'=>$row];
            }
        } else {
            $ids=$body['ids']??[];
            if(in_array($mode,['selected','random_selected'],true)&&(!is_array($ids)||!count($ids)||count($ids)>500)) return new WP_Error('empty_selection','Select one or more products.',['status'=>400]);
            if(in_array($mode,['selected','random_selected'],true)) foreach($ids as $id) if((!is_int($id)&&!(is_string($id)&&ctype_digit($id)))||(int)$id<=0) return new WP_Error('invalid_selection','Product IDs must be positive integers.',['status'=>400]);
            $ids=array_map('intval',(array)$ids); $found=[];
            foreach($products as $p) {
                if(in_array($mode,['selected','random_selected'],true)&&!in_array($p->get_id(),$ids,true)) continue;
                $found[]=$p->get_id(); $targets[]=['product'=>$p,'currency'=>$currency,'row'=>[]];
            }
            if(in_array($mode,['selected','random_selected'],true)&&count(array_unique($ids))!==count($found)) return new WP_Error('missing_product','A selected product is missing or not editable. Reload.',['status'=>400]);
        }
        $changes=[];$skipped=[];
        foreach($targets as $target) {
            $p=$target['product'];$cur=$target['currency'];$row=$target['row'];$before=self::snap($p,$cur);
            if($mode!=='import') {
                if($before['regular']===''||(float)$before['regular']<=0){$skipped[]=$p->get_name().' ('.$cur.'): no regular price.';continue;}
                $row=['discount'=>(string)($body['discount']??'')];
                if($mode==='random_global'||$mode==='random_selected')$row['random']=true;
            } elseif(trim((string)($row['regular']??''))===''&&trim((string)($row['sale']??''))===''&&trim((string)($row['discount']??''))==='') continue;
            if(isset($row['regular'])&&$row['regular']==='') unset($row['regular']);
            $after=self::values($cur,$before,$row,$cur==='USD',$p->get_meta('_instapass_exact_usd')==='yes');
            if(is_wp_error($after)) return new WP_Error('invalid_price_row',$p->get_name().' ('.$cur.'): '.$after->get_error_message(),['status'=>400]);
            $changes[]=['id'=>$p->get_id(),'sku'=>$p->get_sku(),'name'=>$p->get_name(),'currency'=>$cur,'fingerprint'=>self::fp($p,$cur),'before'=>$before,'after'=>$after];
        }
        if(!$changes) return new WP_Error('no_changes','No priced products were eligible for this preview.',['status'=>400]);
        $token=wp_generate_uuid4();$op=['token'=>$token,'user'=>get_current_user_id(),'created'=>time(),'state'=>'preview','changes'=>$changes,'skipped'=>$skipped];
        if(!set_transient('ipcb_preview_'.$token,$op,self::TTL)) return new WP_Error('preview_store','Preview could not be saved. No product changed.',['status'=>500]);
        return $op;
    }
    private static function write($p,$currency,$values) {
        if($currency==='GBP') {
            foreach(['regular'=>'_instapass_gbp_regular_price','sale'=>'_instapass_gbp_sale_price'] as $key=>$meta) {
                if($values[$key]==='') $p->delete_meta_data($meta); else $p->update_meta_data($meta,$values[$key]);
            }
        } else {
            $p->set_regular_price($values['regular']);$p->set_sale_price($values['sale']);
            $p->set_price($p->is_on_sale('edit')&&$values['sale']!==''?$values['sale']:$values['regular']);
        }
        if(!$p->save()) throw new RuntimeException('WooCommerce did not confirm the save.');
    }
    private static function audit($token) { return get_option('ipcb_audit_'.$token,false); }
    private static function history_tokens() { $h=get_option('ipcb_history',[]);return is_array($h)?$h:[]; }
    public static function apply($request) {
        $token=$request->get_param('token'); if(!is_string($token)||!preg_match('/^[a-f0-9-]{36}$/i',$token)) return new WP_Error('invalid_token','Invalid preview token.',['status'=>400]);
        if(!add_option(self::LOCK,['token'=>$token,'time'=>time()],'','no')) return new WP_Error('busy','Another price batch is running. Check history before retrying.',['status'=>409]);
        try {
            $op=get_transient('ipcb_preview_'.$token);
            if(!is_array($op)||$op['user']!==get_current_user_id()||time()-$op['created']>self::TTL) return new WP_Error('expired','Preview expired or already applied. Create a new preview.',['status'=>409]);
            foreach($op['changes'] as $c){$p=wc_get_product($c['id']);if(!$p||!current_user_can('edit_post',$c['id'])||!hash_equals($c['fingerprint'],self::fp($p,$c['currency'])))return new WP_Error('stale',$c['name'].' changed after preview. No prices were saved.',['status'=>409]);}
            $audit=['token'=>$token,'user'=>$op['user'],'created'=>$op['created'],'state'=>'applying','changes'=>$op['changes'],'saved'=>[],'errors'=>[],'inflight'=>null];
            if(!add_option('ipcb_audit_'.$token,$audit,'','no'))return new WP_Error('duplicate','This operation already exists in history.',['status'=>409]);
            $history=self::history_tokens();array_unshift($history,$token);
            if(!update_option('ipcb_history',array_slice(array_unique($history),0,30),false)){delete_option('ipcb_audit_'.$token);return new WP_Error('audit_failed','Audit history could not be saved. No prices changed.',['status'=>500]);}
            delete_transient('ipcb_preview_'.$token);
            foreach($op['changes'] as $c) {
                try {
                    $p=wc_get_product($c['id']);
                    if(!$p||!current_user_can('edit_post',$c['id'])||!hash_equals($c['fingerprint'],self::fp($p,$c['currency'])))throw new RuntimeException('Changed during save; stopped.');
                    $audit['inflight']=$c;
                    if(!update_option('ipcb_audit_'.$token,$audit,false))throw new RuntimeException('Could not journal the next product. No write was attempted for it.');
                    self::write($p,$c['currency'],$c['after']);
                    $fresh=wc_get_product($c['id']);
                    if(!$fresh||self::snap($fresh,$c['currency'])!==$c['after'])throw new RuntimeException('Saved values did not match the preview.');
                    $audit['saved'][$c['id'].':'.$c['currency']]=['id'=>$c['id'],'name'=>$c['name'],'currency'=>$c['currency'],'before'=>$c['before'],'after'=>$c['after'],'after_fingerprint'=>self::fp($fresh,$c['currency'])];
                    $audit['inflight']=null;
                    if(!update_option('ipcb_audit_'.$token,$audit,false)){$audit['inflight']=$c;throw new RuntimeException('The product saved but its audit update failed. Inspect this operation; do not retry it.');}
                } catch(Throwable $e) {
                    if(is_array($audit['inflight'])) {
                        $fresh=wc_get_product($c['id']);$now=$fresh?self::snap($fresh,$c['currency']):null;
                        if($fresh&&$now===$c['after']) {$audit['saved'][$c['id'].':'.$c['currency']]=['id'=>$c['id'],'name'=>$c['name'],'currency'=>$c['currency'],'before'=>$c['before'],'after'=>$c['after'],'after_fingerprint'=>self::fp($fresh,$c['currency'])];$audit['inflight']=null;}
                        elseif($fresh&&$now===$c['before']) $audit['inflight']=null;
                        else $audit['state']='needs_review';
                    }
                    $audit['errors'][]=$c['name'].' ('.$c['currency'].'): '.$e->getMessage();
                    update_option('ipcb_audit_'.$token,$audit,false);break;
                }
            }
            if($audit['state']!=='needs_review')$audit['state']=$audit['errors']?'partial':'applied';
            $audit['finished']=time();
            if(!update_option('ipcb_audit_'.$token,$audit,false))return new WP_Error('audit_finalize','Prices may have changed; inspect the audit before retrying.',['status'=>500]);
            return ['token'=>$token,'state'=>$audit['state'],'saved'=>count($audit['saved']),'requested'=>count($op['changes']),'errors'=>$audit['errors']];
        } finally { $lock=get_option(self::LOCK);if(is_array($lock)&&($lock['token']??'')===$token)delete_option(self::LOCK); }
    }
    public static function history() {
        $out=[];foreach(self::history_tokens() as $token){$a=self::audit($token);if(is_array($a)&&($a['user']??0)===get_current_user_id())$out[]=['token'=>$token,'state'=>$a['state'],'created'=>$a['created'],'saved'=>count($a['saved']??[]),'errors'=>$a['errors']??[],'undo_errors'=>$a['undo_errors']??[],'changes'=>array_map(static function($c){return ['name'=>$c['name'],'sku'=>$c['sku'],'currency'=>$c['currency'],'before'=>$c['before'],'after'=>$c['after']];},$a['changes']??[])];}return $out;
    }
    public static function undo($request) {
        $token=$request->get_param('token');if(!is_string($token)||!preg_match('/^[a-f0-9-]{36}$/i',$token))return new WP_Error('invalid_token','Invalid operation token.',['status'=>400]);$a=self::audit($token);
        if(!preg_match('/^[a-f0-9-]{36}$/i',$token)||!is_array($a)||$a['user']!==get_current_user_id())return new WP_Error('not_found','Operation not found.',['status'=>404]);
        if($a['state']!=='applied'&&$a['state']!=='partial')return new WP_Error('undo_state','Only completed or partial batches can be undone.',['status'=>409]);
        if(!add_option(self::LOCK,['token'=>'undo-'.$token,'time'=>time()],'','no'))return new WP_Error('busy','Another price batch is running.',['status'=>409]);
        try {
            foreach($a['saved'] as $s){$p=wc_get_product($s['id']);if(!$p||!current_user_can('edit_post',$s['id'])||!hash_equals($s['after_fingerprint'],self::fp($p,$s['currency'])))return new WP_Error('undo_stale',$s['name'].' changed since the batch. Undo was refused.',['status'=>409]);}
            $a['state']='undoing';$a['restored_rows']=[];$a['undo_errors']=[];$a['inflight']=null;
            if(!update_option('ipcb_audit_'.$token,$a,false))return new WP_Error('undo_audit','Undo could not be recorded. No price was changed.',['status'=>500]);
            foreach(array_reverse($a['saved']) as $s){
                try {
                    $p=wc_get_product($s['id']);if(!$p||!current_user_can('edit_post',$s['id'])||!hash_equals($s['after_fingerprint'],self::fp($p,$s['currency'])))throw new RuntimeException('Changed during undo; stopped.');
                    $a['inflight']=$s;if(!update_option('ipcb_audit_'.$token,$a,false))throw new RuntimeException('Undo journal could not be saved; no write was attempted for this row.');
                    self::write($p,$s['currency'],$s['before']);$fresh=wc_get_product($s['id']);if(!$fresh||self::snap($fresh,$s['currency'])!==$s['before'])throw new RuntimeException('Restored values did not match the saved originals.');
                    $a['restored_rows'][]=['id'=>$s['id'],'currency'=>$s['currency'],'fingerprint'=>self::fp($fresh,$s['currency'])];$a['inflight']=null;
                    if(!update_option('ipcb_audit_'.$token,$a,false)){$a['inflight']=$s;throw new RuntimeException('Restored price but the audit update failed. Inspect manually.');}
                } catch(Throwable $e) {$a['state']='needs_review';$a['undo_errors'][]=$s['name'].' ('.$s['currency'].'): '.$e->getMessage();update_option('ipcb_audit_'.$token,$a,false);return new WP_Error('undo_needs_review','Undo stopped safely. Inspect this batch history before making another change.',['status'=>500]);}
            }
            $a['state']='undone';$a['undone']=time();$a['restored']=count($a['restored_rows']);$a['inflight']=null;
            if(!update_option('ipcb_audit_'.$token,$a,false))return new WP_Error('undo_audit_finalize','Undo completed, but its final audit update failed. Inspect the batch history.',['status'=>500]);
            return ['state'=>'undone','restored'=>$a['restored']];
        } finally { $lock=get_option(self::LOCK);if(is_array($lock)&&($lock['token']??'')==='undo-'.$token)delete_option(self::LOCK); }
    }
}
Instapass_Control_Bulk::init();