from pathlib import Path
import hashlib,json
r=Path(__file__).resolve().parents[1];m={'before':{},'after':{}}
rel='mu-plugins/instapass-launch-catalog.php';p=r/rel;raw=p.read_bytes();s=raw.decode('utf-8');assert 'function ipl_credit_price_html' not in s;m['before'][rel]=hashlib.sha256(raw).hexdigest()
s=s.replace('Version: 1.1.0','Version: 1.2.0')
anchor="function ipl_choice_label($g,$key){return ipl_credit($g)?'$'.(int)$key.' credits':ipl_label($key);}\n"
assert anchor in s
addition=r'''
// Credits are one-time balance purchases; do not describe their price as a monthly subscription.
function ipl_credit_price_html($p,$face){
 $currency=get_woocommerce_currency();$full=(int)round(wc_get_price_to_display($p)*100,0,PHP_ROUND_HALF_UP);$money=static fn($n)=>wc_price($n/100,['currency'=>$currency]);
 $h='<span class="ipl-credit-pricing" data-credit-face="'.(int)$face.'" data-credit-price-cents="'.$full.'">';
 if(in_array($currency,['USD','PKR'],true)){
  $baseline=$currency==='USD'?(int)$face*100:Instapass_Control_Money::pkr_unit((int)$face*100,instapass_control_rate())*100;
  if($baseline>$full)$h.='<span class="ip-offer-saving-now">Save '.(int)round(100*($baseline-$full)/$baseline).'% vs credit face value</span>';
 }
 $h.='<span class="ipl-credit-primary">'.$money($full).'</span><span class="ipl-credit-payment">One-time credit purchase</span><span class="ip-term-upfront">For $'.(int)$face.' in credits</span>';
 $rates=function_exists('ip_tenor_bundle_rates')?ip_tenor_bundle_rates($p,['full'=>$full,'months'=>1,'currency'=>$currency]):[];$best=null;foreach($rates as $rate)if(!$best||$rate['full']<$best['full'])$best=$rate;
 if($best)$h.='<span class="ip-term-bundle">With '.esc_html($best['code']).': '.$money($best['full']).'<span class="ip-bundle-condition">'.esc_html($best['percent'].'% off with '.$best['tools'].' or more eligible items; repeats count. One code per order.').'</span></span>';
 return $h.'</span>';
}
add_filter('woocommerce_get_price_html',function($html,$p){
 if(!ipl_public()||!function_exists('ip_tenor_presentation_context')||!ip_tenor_presentation_context())return $html;
 $o=ipl_offer($p->get_id());return $o&&ipl_credit($o['group'])&&ipl_ready($p)&&$p->get_price()!==''?ipl_credit_price_html($p,$o['months']):$html;
},980,2);
'''
s=s.replace(anchor,anchor+addition);p.write_text(s,encoding='utf-8',newline='\n');m['after'][rel]=hashlib.sha256(p.read_bytes()).hexdigest()
rel='mu-plugins/instapass-launch-catalog.css';p=r/rel;raw=p.read_bytes();m['before'][rel]=hashlib.sha256(raw).hexdigest();s=raw.decode('utf-8')+'''
.ipl-card[data-launch-family=cursor-pro]{--ipl-accent:#253d49}.ipl-card[data-launch-family=cursor-pro]>.ipl-visual{background:#eef3f5}
.ipl-credit-pricing{display:block;line-height:1.5}.ipl-credit-pricing>.ip-offer-saving-now{font-size:12px;font-weight:650;margin-bottom:10px}.ipl-credit-primary{display:block;font-size:30px;font-weight:750;letter-spacing:-.025em;line-height:1.2}.ipl-credit-payment{display:block;font-size:12px;font-weight:500;margin-top:4px;color:#546775}
''';p.write_text(s,encoding='utf-8',newline='\n');m['after'][rel]=hashlib.sha256(p.read_bytes()).hexdigest();(r/'outputs/cursor-credits-source-manifest.json').write_text(json.dumps(m,indent=2));print('Two scoped presentation sources prepared')
