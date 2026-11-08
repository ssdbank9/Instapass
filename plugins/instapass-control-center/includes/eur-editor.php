<?php
if (!defined('ABSPATH')) exit;
require_once __DIR__.'/eur-values.php';

function instapass_control_eur_fingerprint($product) {
    return hash('sha256',wp_json_encode([(string)$product->get_meta('_instapass_eur_regular_price'),(string)$product->get_meta('_instapass_eur_sale_price')]));
}
function instapass_control_eur_fields() {
    global $product_object;
    if (!$product_object || !current_user_can('manage_woocommerce') || !current_user_can('edit_post',$product_object->get_id()))return;
    echo '<div class="options_group show_if_simple"><p class="form-field"><strong>Independent EUR pricing</strong><br>Enter EUR prices manually. These do not convert from USD. EUR payment availability depends on your configured payment methods.</p>';
    foreach(['_instapass_eur_regular_price'=>['EUR regular price (€)','Leave blank until ready to price this product in EUR.'], '_instapass_eur_sale_price'=>['EUR sale price (€)','Optional sale price in euros; leave blank for no EUR sale.']] as $key=>$info) {
        woocommerce_wp_text_input(['id'=>$key,'label'=>$info[0],'type'=>'text','value'=>(string)$product_object->get_meta($key),'description'=>$info[1],'desc_tip'=>true,'custom_attributes'=>['inputmode'=>'decimal','maxlength'=>'9']]);
    }
    woocommerce_wp_text_input(['id'=>'_instapass_eur_discount','label'=>'Apply EUR discount (%)','type'=>'number','value'=>'','description'=>'Optional. On Update, this replaces the EUR sale price. Enter 0 to remove the EUR sale. USD/PKR prices are unaffected.','desc_tip'=>false,'custom_attributes'=>['min'=>'0','max'=>'100','step'=>'0.01']]);
    wp_nonce_field('instapass_eur_product_'.$product_object->get_id(),'instapass_eur_nonce',false);
    echo '<input type="hidden" name="instapass_eur_fingerprint" value="'.esc_attr(instapass_control_eur_fingerprint($product_object)).'"></div>';
}
add_action('woocommerce_product_options_pricing','instapass_control_eur_fields');

function instapass_control_eur_save($product) {
    // Absent fields (quick edits/imports/REST/new products) must not erase EUR values.
    if(!isset($_POST['instapass_eur_nonce']) || !$product->is_type('simple'))return;
    if(!current_user_can('manage_woocommerce') || !current_user_can('edit_post',$product->get_id()))return;
    $nonce=$_POST['instapass_eur_nonce'];
    if(!is_string($nonce)||!wp_verify_nonce(wp_unslash($nonce),'instapass_eur_product_'.$product->get_id())) {
        WC_Admin_Meta_Boxes::add_error('EUR prices were not saved: please reload the product editor and try again.');return;
    }
    $expected=$_POST['instapass_eur_fingerprint']??null;
    $fresh=wc_get_product($product->get_id());
    if(!$fresh || !is_string($expected) || !hash_equals(instapass_control_eur_fingerprint($fresh),$expected)) {
        WC_Admin_Meta_Boxes::add_error('EUR prices changed in another session. Your EUR changes were not saved; reload before editing again.');return;
    }
    try {
        $values=[];
        foreach(['_instapass_eur_regular_price','_instapass_eur_sale_price','_instapass_eur_discount'] as $key) {
            if(!isset($_POST[$key])||!is_string($_POST[$key]))throw new InvalidArgumentException('EUR fields were incomplete. Reload the product editor.');
            $values[]=wp_unslash($_POST[$key]);
        }
        $eur=Instapass_Control_EUR::values(...$values);
    }catch(InvalidArgumentException $e){WC_Admin_Meta_Boxes::add_error('EUR prices were not saved: '.$e->getMessage());return;}
    foreach(['regular'=>'_instapass_eur_regular_price','sale'=>'_instapass_eur_sale_price'] as $key=>$meta) {
        if($eur[$key]==='')$product->delete_meta_data($meta);else $product->update_meta_data($meta,$eur[$key]);
    }
}
add_action('woocommerce_admin_process_product_object','instapass_control_eur_save');
