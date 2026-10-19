<?php
if (!defined('ABSPATH')) exit;
final class Instapass_Control_PKR {
    private static function cents($value) {
        if (!is_string($value) || !preg_match('/^(0|[1-9][0-9]{0,8})(?:\.([0-9]{1,2}))?$/D',$value,$m)) {
            throw new InvalidArgumentException('Enter PKR amounts with at most two decimal places, without commas or currency symbols.');
        }
        return (int)$m[1]*100+(int)str_pad($m[2]??'',2,'0');
    }
    private static function decimal($cents) { return intdiv($cents,100).'.'.str_pad((string)($cents%100),2,'0',STR_PAD_LEFT); }
    public static function values($regular,$sale,$discount='') {
        foreach ([$regular,$sale,$discount] as $v) if(!is_string($v)) throw new InvalidArgumentException('Invalid PKR input.');
        $regular=trim($regular);$sale=trim($sale);$discount=trim($discount);
        if($regular==='') {
            if($sale!==''||$discount!=='')throw new InvalidArgumentException('Enter a PKR regular price before adding a sale or discount.');
            return ['regular'=>'','sale'=>''];
        }
        $r=self::cents($regular);
        if($r<=0)throw new InvalidArgumentException('PKR regular price must be greater than zero. Leave it blank to use automatic USD conversion.');
        if($discount!=='') {
            if(!preg_match('/^(0|[1-9][0-9]?|100)(?:\.([0-9]{1,2}))?$/D',$discount,$m))throw new InvalidArgumentException('PKR discount must be between 0 and 100%.');
            $bps=(int)$m[1]*100+(int)str_pad($m[2]??'',2,'0');
            if($bps>10000)throw new InvalidArgumentException('PKR discount must be between 0 and 100%.');
            $sale=$bps===0?'':self::decimal(intdiv($r*(10000-$bps)+5000,10000));
        }
        if($sale!=='') {
            $s=self::cents($sale);
            if($s>=$r)throw new InvalidArgumentException('PKR sale price must be lower than its PKR regular price.');
            $sale=self::decimal($s);
        }
        return ['regular'=>self::decimal($r),'sale'=>$sale];
    }
}
function instapass_control_pkr_values(...$args){return Instapass_Control_PKR::values(...$args);}

function instapass_control_pkr_fingerprint($product) {
    return hash('sha256',wp_json_encode([(string)$product->get_meta('_instapass_pkr_regular_price'),(string)$product->get_meta('_instapass_pkr_sale_price')]));
}
function instapass_control_pkr_fields() {
    global $product_object;
    if (!$product_object || !current_user_can('manage_woocommerce') || !current_user_can('edit_post',$product_object->get_id()))return;
    echo '<div class="options_group show_if_simple"><p class="form-field"><strong>Optional fixed PKR pricing</strong><br>Leave both PKR prices blank to use USD conversion and the saved rate. A fixed PKR price stays unchanged when USD prices or the exchange rate change. Bundle coupons still apply.</p>';
    foreach(['_instapass_pkr_regular_price'=>['PKR regular price (Rs)','Leave blank to calculate PKR from USD.'], '_instapass_pkr_sale_price'=>['PKR sale price (Rs)','Optional fixed PKR sale price; leave blank for no PKR sale.']] as $key=>$info) {
        woocommerce_wp_text_input(['id'=>$key,'label'=>$info[0],'type'=>'text','value'=>(string)$product_object->get_meta($key),'description'=>$info[1],'desc_tip'=>true,'custom_attributes'=>['inputmode'=>'decimal','maxlength'=>'12']]);
    }
    woocommerce_wp_text_input(['id'=>'_instapass_pkr_discount','label'=>'Apply PKR discount (%)','type'=>'number','value'=>'','description'=>'Optional. On Update, this replaces the PKR sale price. Enter 0 to remove the PKR sale. USD/GBP prices are unaffected.','desc_tip'=>false,'custom_attributes'=>['min'=>'0','max'=>'100','step'=>'0.01']]);
    wp_nonce_field('instapass_pkr_product_'.$product_object->get_id(),'instapass_pkr_nonce',false);
    echo '<input type="hidden" name="instapass_pkr_fingerprint" value="'.esc_attr(instapass_control_pkr_fingerprint($product_object)).'"></div>';
}
add_action('woocommerce_product_options_pricing','instapass_control_pkr_fields');

function instapass_control_pkr_save($product) {
    // Absent fields (quick edits/imports/REST/new products) must not erase PKR values.
    if(!isset($_POST['instapass_pkr_nonce']) || !$product->is_type('simple'))return;
    if(!current_user_can('manage_woocommerce') || !current_user_can('edit_post',$product->get_id()))return;
    $nonce=$_POST['instapass_pkr_nonce'];
    if(!is_string($nonce)||!wp_verify_nonce(wp_unslash($nonce),'instapass_pkr_product_'.$product->get_id())) {
        WC_Admin_Meta_Boxes::add_error('PKR prices were not saved: please reload the product editor and try again.');return;
    }
    $expected=$_POST['instapass_pkr_fingerprint']??null;
    $fresh=wc_get_product($product->get_id());
    if(!$fresh || !is_string($expected) || !hash_equals(instapass_control_pkr_fingerprint($fresh),$expected)) {
        WC_Admin_Meta_Boxes::add_error('PKR prices changed in another session. Your PKR changes were not saved; reload before editing again.');return;
    }
    try {
        $values=[];
        foreach(['_instapass_pkr_regular_price','_instapass_pkr_sale_price','_instapass_pkr_discount'] as $key) {
            if(!isset($_POST[$key])||!is_string($_POST[$key]))throw new InvalidArgumentException('PKR fields were incomplete. Reload the product editor.');
            $values[]=wp_unslash($_POST[$key]);
        }
        $pkr=instapass_control_pkr_values(...$values);
    }catch(InvalidArgumentException $e){WC_Admin_Meta_Boxes::add_error('PKR prices were not saved: '.$e->getMessage());return;}
    foreach(['regular'=>'_instapass_pkr_regular_price','sale'=>'_instapass_pkr_sale_price'] as $key=>$meta) {
        if($pkr[$key]==='')$product->delete_meta_data($meta);else $product->update_meta_data($meta,$pkr[$key]);
    }
}
add_action('woocommerce_admin_process_product_object','instapass_control_pkr_save');
