<?php
defined('ABSPATH')||exit;
$ids=[30,31,138,139,140];$before=[];
foreach($ids as $id){$p=wc_get_product($id);if(!$p)throw new RuntimeException('Expected product missing');$before[]=['id'=>$id,'sku'=>$p->get_sku(),'brief'=>$p->get_meta('_instapass_brief'),'description'=>$p->get_description(),'short_description'=>$p->get_short_description()];}
if(wc_get_product(30)->get_sku()!=='claude-pro'||wc_get_product(31)->get_sku()!=='claude-max-gift-x5')throw new RuntimeException('Claude identities differ');
foreach([138,139,140] as $id)if(wc_get_product($id)->get_meta('_instapass_price_release')!=='20261019-approved-terms-v1')throw new RuntimeException('New release identity differs');
if(getenv('IP_OFFER_COPY_APPLY')!=='yes'){echo wp_json_encode($before,JSON_PRETTY_PRINT);return;}
$notes=[
138=>'One year of access, paid upfront. USD full-term total: $173.33. With the eligible 10% bundle coupon, this item costs about $156 for the year ($13 per month equivalent). The coupon requires four different product/duration selections. Renewal requires a new confirmation and payment.',
139=>'Six months of access, paid upfront. USD full-term total: $432 ($72 per month equivalent). No automatic monthly charge.',
140=>'One year of access, paid upfront. USD full-term total: $840. With the eligible 10% bundle coupon, this item costs about $756 for the year ($63 per month equivalent). The coupon requires four different product/duration selections. No automatic monthly charge.'
];$rows=[];
foreach([30,31,139,140] as $id){$p=wc_get_product($id);$tail=trim(preg_replace('/(?:\s*·\s*)?25% additional limits(?:\s*·\s*)?/u','',(string)$p->get_meta('_instapass_brief')));$p->update_meta_data('_instapass_brief','25% additional limits'.($tail!==''?' · '.$tail:''));$p->save_meta_data();}
foreach($notes as $id=>$note){$p=wc_get_product($id);$description='<p>'.esc_html($p->get_meta('_instapass_brief')).'</p><p>'.esc_html($note).'</p><p>Private access. Activation instructions are sent to the activation email you provide at checkout, or your order email when no separate activation email is provided.</p><p>Different durations can be added separately to the same order. Use one eligible bundle coupon per order.</p><p>For delivery and access issues, see our <a href="'.esc_url(home_url('/refund-policy/')).'">refund policy</a>.</p>';$p->set_short_description('<p>'.esc_html($note).'</p>');$p->set_description($description);$p->save();}
foreach($ids as $id){$p=wc_get_product($id);$rows[]=['id'=>$id,'brief'=>$p->get_meta('_instapass_brief'),'updated_term_copy'=>isset($notes[$id])];}
echo wp_json_encode(['applied'=>true,'products'=>$rows,'excluded_max20_brief'=>wc_get_product(32)->get_meta('_instapass_brief')],JSON_PRETTY_PRINT);