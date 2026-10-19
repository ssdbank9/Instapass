<?php
if (!defined('ABSPATH')) exit;
function instapass_control_pkr_values(...$args){try{return Instapass_Control_GBP::values(...$args);}catch(InvalidArgumentException $e){throw new InvalidArgumentException(str_replace('GBP','PKR',$e->getMessage()));}}

function instapass_control_pkr_fingerprint($product) {
    return hash('sha256',wp_json_encode([(string)$product->get_meta('_instapass_pkr_regular_price'),(string)$product->get_meta('_instapass_pkr_sale_price'),(string)$product->get_meta('_instapass_exact_usd')]));
}
function instapass_control_pkr_fields() {
    global $product_object;
    if (!$product_object || !current_user_can('manage_woocommerce') || !current_user_can('edit_post',$product_object->get_id()))return;
    echo '<div class="options_group show_if_simple"><p class="form-field"><strong>Optional fixed PKR pricing</strong><br>Leave both PKR prices blank to use USD conversion and the saved rate. A fixed PKR price stays unchanged when USD prices or the exchange rate change. Bundle coupons still apply.</p>';
    foreach(['_instapass_pkr_regular_price'=>['PKR regular price (Rs)','Leave blank to calculate PKR from USD.'], '_instapass_pkr_sale_price'=>['PKR sale price (Rs)','Optional fixed PKR sale price; leave blank for no PKR sale.']] as $key=>$info) {
        woocommerce_wp_text_input(['id'=>$key,'label'=>$info[0],'type'=>'text','value'=>(string)$product_object->get_meta($key),'description'=>$info[1],'desc_tip'=>true,'custom_attributes'=>['inputmode'=>'decimal','maxlength'=>'9']]);
    }
    woocommerce_wp_text_input(['id'=>'_instapass_pkr_discount','label'=>'Apply PKR discount (%)','type'=>'number','value'=>'','description'=>'Optional. On Update, this replaces the PKR sale price. Enter 0 to remove the PKR sale. USD/GBP prices are unaffected.','desc_tip'=>false,'custom_attributes'=>['min'=>'0','max'=>'100','step'=>'0.01']]);
    wp_nonce_field('instapass_pkr_product_'.$product_object->get_id(),'instapass_pkr_nonce',false);
    woocommerce_wp_checkbox(['id'=>'_instapass_exact_usd','label'=>'Allow exact USD prices','value'=>$product_object->get_meta('_instapass_exact_usd')==='yes'?'yes':'no','description'=>'Allow cents such as 833.33 or whole-dollar prices in bulk imports for this listing.']);
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
    $product->update_meta_data('_instapass_exact_usd',isset($_POST['_instapass_exact_usd'])?'yes':'no');
    foreach(['regular'=>'_instapass_pkr_regular_price','sale'=>'_instapass_pkr_sale_price'] as $key=>$meta) {
        if($pkr[$key]==='')$product->delete_meta_data($meta);else $product->update_meta_data($meta,$pkr[$key]);
    }
}
add_action('woocommerce_admin_process_product_object','instapass_control_pkr_save');