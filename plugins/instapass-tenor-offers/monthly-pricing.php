<?php
/** Presentation only: actual cart and order amounts always remain full-term prices. */
defined('ABSPATH')||exit;
function ip_tenor_months($p){
 if(!$p->is_type('simple')||$p->get_meta('_instapass_tenor_group')==='')return 0;
 return preg_match('/^([1-9][0-9]*):month$/D',ip_tenor_duration_key(ip_tenor_duration($p)),$m)?(int)$m[1]:0;
}
function ip_tenor_price_view($p){
 $months=ip_tenor_months($p);$raw=$p->get_price();if(!$months||$raw===''||!is_numeric($raw))return null;
 $full=(int)round(wc_get_price_to_display($p,['price'=>(float)$raw])*100,0,PHP_ROUND_HALF_UP);
 return ['months'=>$months,'full'=>$full,'monthly'=>(int)round($full/$months,0,PHP_ROUND_HALF_UP),'currency'=>get_woocommerce_currency(),'duration'=>ip_tenor_duration($p)];
}
function ip_tenor_money($cents,$currency){return wc_price($cents/100,['currency'=>$currency]);}
function ip_tenor_bundle_rates($p,$view){
 if(!$view||!wc_coupons_enabled()||!function_exists('instapass_promo_config')||(function_exists('ip_offer_coupon_allowed')&&!ip_offer_coupon_allowed($p)))return [];
 $rates=[];foreach([0,2,1] as $slot){
  $c=instapass_promo_config($slot);if(!$c['enabled']||$c['mode']!=='quantity'||empty($c['coupon_id']))continue;
  if((function_exists('instapass_promo_active')&&!instapass_promo_active($c))||(function_exists('instapass_promo_product_allowed')&&!instapass_promo_product_allowed($p,$c)))continue;
  $coupon=new WC_Coupon($c['coupon_id']);$pct=(float)$c['percent'];$expiry=$coupon->get_date_expires();
  if(!$coupon->is_type('percent')||abs((float)$coupon->get_amount()-$pct)>0.00001||$pct<=0||$pct>100||($coupon->get_exclude_sale_items()&&$p->is_on_sale())||($coupon->get_product_ids()&&!in_array($p->get_id(),$coupon->get_product_ids(),true))||in_array($p->get_id(),$coupon->get_excluded_product_ids(),true)||$coupon->get_product_categories()||$coupon->get_excluded_product_categories()||$coupon->get_email_restrictions()||(float)$coupon->get_minimum_amount()>0||(float)$coupon->get_maximum_amount()>0||($expiry&&$expiry->getTimestamp()<time())||($coupon->get_usage_limit()&&$coupon->get_usage_count()>=$coupon->get_usage_limit()))continue;
  $bps=(int)round($pct*100);$full=(int)floor(($view['full']*(10000-$bps)+5000)/10000);
  $rates[]=['code'=>strtoupper($c['code']),'percent'=>$pct,'tools'=>(int)$c['tools'],'full'=>$full,'monthly'=>(int)round($full/$view['months'],0,PHP_ROUND_HALF_UP)];
 }return $rates;
}
function ip_tenor_saving($p,$view){
 if($view['months']<2)return '';
 foreach(ip_tenor_offers($p) as $other){if(ip_tenor_months($other)!==1)continue;$v=ip_tenor_price_view($other);if(!$v||$v['full']<=0)continue;
  $baseline=$v['full']*$view['months'];if($baseline>$view['full'])return 'Save '.number_format(100*($baseline-$view['full'])/$baseline,0).'% vs buying one month at a time';
 }return '';
}
function ip_tenor_monthly_html($p,$original=''){
 $v=ip_tenor_price_view($p);if(!$v)return $original;
 $h='<span class="ip-term-pricing" data-term-months="'.$v['months'].'" data-upfront-cents="'.$v['full'].'"><span class="ip-monthly-primary">'.ip_tenor_money($v['monthly'],$v['currency']).'<span class="ip-monthly-unit"> /month'.($v['months']>1?' equivalent':'').'</span></span><span class="ip-term-upfront">'.ip_tenor_money($v['full'],$v['currency']).' paid upfront for '.esc_html($v['duration']).'</span>';
 if(!function_exists('ip_offer_savings_html')){$saving=ip_tenor_saving($p,$v);if($saving!=='')$h.='<span class="ip-term-saving">'.esc_html($saving).'</span>';}
 return $h.'</span>';
}
function ip_tenor_presentation_context(){
 return function_exists('instapass_cc_front')&&instapass_cc_front()&&!is_cart()&&!is_checkout()&&!is_account_page();
}
add_filter('woocommerce_get_price_html',function($html,$p){return ip_tenor_presentation_context()?ip_tenor_monthly_html($p,$html):$html;},150,2);
function ip_tenor_bundle_breakdown($p){
 $v=ip_tenor_price_view($p);$rates=ip_tenor_bundle_rates($p,$v);if(!$rates)return '';
 $h='<section class="ip-bundle-breakdown" aria-label="Monthly equivalents and full-term payments"><h3>Build your bundle, save more</h3><p>Monthly equivalents for this '.esc_html($v['duration']).' offer. Pay the full term upfront.</p><div class="ip-bundle-rate"><span>Without a coupon</span><strong>'.ip_tenor_money($v['monthly'],$v['currency']).'/month</strong><span>'.ip_tenor_money($v['full'],$v['currency']).' upfront</span></div>';
 foreach($rates as $rate)$h.='<div class="ip-bundle-rate"><span>'.esc_html($rate['tools'].' offers · '.$rate['percent'].'% off').'<code>'.esc_html($rate['code']).'</code></span><strong>'.ip_tenor_money($rate['monthly'],$v['currency']).'/month</strong><span>'.ip_tenor_money($rate['full'],$v['currency']).' upfront</span></div>';
 return $h.'<p class="ip-bundle-disclosure">Each code needs the stated number of eligible items in one order. Repeated quantities of the same offer count. One coupon per order. Native basket rounding can vary an item total by one cent. No automatic monthly charge.</p></section>';
}
add_filter('render_block_core/post-excerpt',function($html,$parsed,$block){
 if(!function_exists('is_product')||!is_product()||!function_exists('instapass_block_product'))return $html;$p=instapass_block_product($block);return $p?ip_tenor_refresh_generated_copy($html,$p):$html;
},40,3);
function ip_tenor_tax_message(){return 'We absorb applicable taxes—no extra tax at checkout. Any tax saving is additional to the discount percentage shown.';}
add_action('wp_body_open',function(){
 if(function_exists('instapass_cc_front')&&instapass_cc_front()&&!instapass_cc_order_currency())echo '<div class="ip-tax-assurance"><span aria-hidden="true">✓</span> '.esc_html(ip_tenor_tax_message()).'</div>';
},7);
add_action('woocommerce_checkout_before_order_review',function(){
 if(!WC()->cart)return;foreach(WC()->cart->get_cart() as $item)if(ip_tenor_months($item['data'])>1){echo '<p class="ip-prepaid-checkout-note">Your total below covers the full selected term, paid upfront. Monthly figures on product pages are equivalents.</p>';break;}
});
/** Refresh only the original generated price paragraph; owner-written copy stays editable. */
function ip_tenor_refresh_generated_copy($html,$p){
 if(!$p||$p->get_meta('_instapass_price_release')!=='20261019-approved-terms-v1')return $html;
 $pattern='#(<p(?:\\s[^>]*)?>)(?:One year|Six months) of access, paid upfront\\. USD full-term total: [^<]*</p>#';
 if(!preg_match($pattern,$html))return $html;
 $v=ip_tenor_price_view($p);$note=esc_html(ip_tenor_duration($p)).' of access, paid upfront. ';
 $note.=$v?'Full-term total: '.ip_tenor_money($v['full'],$v['currency']).'. See the current eligible bundle amounts below. ':'Choose a priced currency to see the current total. ';
 $note.='No automatic monthly charge. Renewal requires a new confirmation and payment.';
 return preg_replace_callback($pattern,static fn($m)=>$m[1].$note.'</p>',$html);
}
function ip_tenor_refresh_product_copy($html){
 if(!ip_tenor_presentation_context()||!is_product())return $html;
 return ip_tenor_refresh_generated_copy($html,wc_get_product(get_queried_object_id()));
}
add_filter('woocommerce_short_description','ip_tenor_refresh_product_copy',30);
add_filter('the_content','ip_tenor_refresh_product_copy',30);
