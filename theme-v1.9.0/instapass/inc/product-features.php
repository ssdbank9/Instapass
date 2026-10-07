<?php
/** Editable source details; visibility and stock remain independent. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
function instapass_source_features() {
 static $data; if ( null === $data ) { $data = json_decode( file_get_contents( get_template_directory() . '/data/catalogue-features.json' ), true ) ?: array(); } return $data;
}
function instapass_product_features( $product ) {
 $source = instapass_source_features()[$product->get_sku()] ?? array(); $values = array();
 foreach ( array( 'plan', 'validity', 'access_type', 'activation', 'renewable' ) as $key ) {
  $values[$key] = $product->meta_exists( '_instapass_' . $key ) ? $product->get_meta( '_instapass_' . $key ) : ( $source[$key] ?? '' );
 }
 $values['access_type'] = $values['access_type'] ?: 'Private';
 // Only legacy offerings absent from the Excel inherit the owner's default.
 if (!$values['activation'] && !array_key_exists('activation',$source)) { $values['activation'] = 'Private'===$values['access_type'] ? 'On customer email':'Activation instructions by email'; }
 if ( ! $product->meta_exists( '_instapass_renewable' ) && ! isset( $source['renewable'] ) ) { $values['renewable'] = (bool) preg_match( '/^(chatgpt-plus|claude-pro|claude-max|tools-canva)/', $product->get_sku() ); }
 $values['renewable'] = in_array( $values['renewable'], array( true, 1, '1', 'yes' ), true ); return $values;
}
function instapass_product_feature_fields() {
 global $product_object; if ( ! $product_object ) { return; } $v = instapass_product_features( $product_object ); echo '<div class="options_group">';
 foreach ( array( 'plan'=>'Plan details', 'validity'=>'Access duration', 'activation'=>'Activation / delivery' ) as $key => $label ) { woocommerce_wp_text_input( array( 'id'=>'_instapass_' . $key,'label'=>$label,'value'=>$v[$key] ) ); }
 woocommerce_wp_select( array( 'id'=>'_instapass_access_type','label'=>'Access type','options'=>array('Private'=>'Private access','Shared'=>'Shared access (explicit exception)'),'value'=>$v['access_type'] ) );
 woocommerce_wp_checkbox( array( 'id'=>'_instapass_renewable','label'=>'Renewal available','value'=>$v['renewable']?'yes':'no','description'=>'Customer confirms a new term and pays by transfer or Binance. No automatic charge.' ) ); echo '</div>';
}
add_action( 'woocommerce_product_options_general_product_data', 'instapass_product_feature_fields', 20 );
function instapass_save_product_features( $product ) {
 foreach ( array( 'plan','validity','activation' ) as $key ) { if ( isset( $_POST['_instapass_' . $key] ) && is_scalar( $_POST['_instapass_' . $key] ) ) { $product->update_meta_data( '_instapass_' . $key, sanitize_text_field( wp_unslash( $_POST['_instapass_' . $key] ) ) ); } }
 if ( isset( $_POST['_instapass_access_type'] ) && in_array( $_POST['_instapass_access_type'], array('Private','Shared'),true ) ) { $product->update_meta_data( '_instapass_access_type', $_POST['_instapass_access_type'] ); }
 // Native product editor supplies the fields; unrelated API saves do not clear them.
 if ( isset( $_POST['_instapass_access_type'] ) ) { $product->update_meta_data( '_instapass_renewable', isset( $_POST['_instapass_renewable'] ) ? 'yes':'no' ); }
}
add_action( 'woocommerce_admin_process_product_object', 'instapass_save_product_features', 20 );
function instapass_feature_markup( $product, $detail = false ) {
 $v = instapass_product_features( $product ); $shared = 'shared' === strtolower( $v['access_type'] );
 $html = '<div class="ip-access-facts' . ( $detail ? ' ip-access-facts--detail':'') . '"><div class="ip-access-chips"><span class="' . ( $shared?'ip-access-shared':'ip-access-private' ) . '">' . ( $shared?'Shared access':'Private access' ) . '</span>';
 if ( $v['validity'] ) { $html .= '<span>' . esc_html( $v['validity'] ) . '</span>'; }
 $html .= '<span class="ip-stock-' . ( $product->is_in_stock()?'in':'out' ) . '">' . ( $product->is_in_stock()?'In stock':'Currently out of stock' ) . '</span></div>';
 if ( $v['plan'] ) { $html .= '<p><strong>Plan:</strong> ' . esc_html( $v['plan'] ) . '</p>'; }
 if ($v['activation']) { $html .= '<p>' . ( 'On customer email' === $v['activation'] ? 'Activated on your own email':esc_html( $v['activation'] ) ) . '</p>'; }
 if ( $v['renewable'] ) { $html .= '<p class="ip-renewable">Renewal available · Confirm &amp; pay each term</p>'; }
 if ( $detail ) { $html .= '<p class="ip-access-note">' . ( $shared ? 'This offering uses shared access, as stated in the product details.':'Private access for your use. Your activation link or instructions are sent to the email you provide. We never ask for your tool-account password at checkout.' ) . '</p><p>Money-back guarantee if your order has an issue. <a href="' . esc_url(home_url('/refund-policy/')) . '">Refund policy</a></p>'; }
 return $html . '</div>';
}
function instapass_card_features( $html, $parsed, $block ) {
 if ( ( $parsed['attrs']['__woocommerceNamespace'] ?? '' ) !== 'woocommerce/product-collection/product-title' ) { return $html; }
 $p = instapass_block_product( $block ); if ( ! $p ) { return $html; }
 $title = explode( ' — ', $p->get_name() )[0]; $level = isset($parsed['attrs']['level']) ? absint($parsed['attrs']['level']):2; $level=max(1,min(6,$level));
 $html = '<h' . $level . ' class="wp-block-post-title"><a href="' . esc_url($p->get_permalink()) . '">' . esc_html($title) . '</a></h' . $level . '>';
 $brief=$p->get_meta('_instapass_brief'); if ($brief) {$html.='<p class="ip-tool-brief">'.esc_html($brief).'</p>';}
 return $html . instapass_feature_markup( $p );
}
add_filter( 'render_block_core/post-title', 'instapass_card_features', 30, 3 );
function instapass_detail_features( $html, $parsed, $block ) { $p=instapass_block_product($block); return $p ? $html . instapass_feature_markup($p,true):$html; }
add_filter( 'render_block_core/post-excerpt', 'instapass_detail_features', 20, 3 );
// Existing custom stock blocks are replaced by the clearer facts panel.
add_filter( 'render_block_instapass/product-signals', '__return_empty_string', 30 );
function instapass_catalogue_setup() {
 if ( ! current_user_can('manage_woocommerce') || !function_exists('wc_get_product_id_by_sku') || get_option('instapass_features_seeded')==='1.9.0' ) {return;}
 foreach(instapass_source_features() as $sku=>$values){$p=wc_get_product(wc_get_product_id_by_sku($sku));if(!$p){continue;}foreach($values as $key=>$value){if(!$p->meta_exists('_instapass_'.$key)){$p->update_meta_data('_instapass_'.$key, is_bool($value)?($value?'yes':'no'):$value);}}$p->save_meta_data();}
 // Keep sold-out listings in the catalogue; this never changes publication status.
 update_option('woocommerce_hide_out_of_stock_items','no');
 update_option('woocommerce_enable_guest_checkout','yes'); update_option('woocommerce_enable_checkout_login_reminder','no');
 update_option('woocommerce_enable_signup_and_login_from_checkout','no'); update_option('woocommerce_enable_myaccount_registration','no');
 update_option('woocommerce_enable_coupons','yes'); update_option('woocommerce_feature_order_attribution_enabled','no');
 foreach(array('faq'=>'instapass/page-faq','privacy-policy'=>'instapass/page-privacy') as $slug=>$pattern){$page=get_page_by_path($slug,OBJECT,'page');if($page && current_user_can('edit_post',$page->ID)){if(!metadata_exists('post',$page->ID,'_instapass_pre_v19_content')){update_post_meta($page->ID,'_instapass_pre_v19_content',$page->post_content);}wp_update_post(array('ID'=>$page->ID,'post_content'=>'<!-- wp:pattern '.wp_json_encode(array('slug'=>$pattern)).' /-->'));}}
 update_option('instapass_features_seeded','1.9.0',false);
}
add_action('admin_init','instapass_catalogue_setup',40);
