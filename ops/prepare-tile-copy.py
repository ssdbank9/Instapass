from pathlib import Path
import json,hashlib
r=Path(__file__).resolve().parents[1];m={'before':{},'after':{}}
def read(rel):
 p=r/rel;m['before'][rel]=hashlib.sha256(p.read_bytes()).hexdigest();return p.read_text(encoding='utf-8')
def save(rel,s):
 p=r/rel;p.write_text(s,encoding='utf-8',newline='\n');m['after'][rel]=hashlib.sha256(p.read_bytes()).hexdigest()
rel='plugins/instapass-control-center/includes/offer-manager.php';s=read(rel);lines=s.splitlines();found=0
for i,line in enumerate(lines):
 if "<span class=\"ip-offer-saving-total\">With " in line:
  prefix=line.split("$h.='<span",1)[0];lines[i]=prefix+"$h.='<span class=\"ip-offer-saving-total\">Additional '.esc_html($best['percent']).'% off with '.esc_html($best['code']).'<small>'.esc_html($best['tools'].' eligible items required.').'</small></span>';";found+=1
assert found==2;save(rel,'\n'.join(lines)+'\n')
rel='plugins/instapass-tenor-offers/monthly-pricing.php';s=read(rel);lines=s.splitlines();removed=[x for x in lines if "Bundle from '.ip_tenor_money" in x];assert len(removed)==1;s='\n'.join(x for x in lines if x not in removed)+'\n';assert '.ip_tenor_bundle_breakdown($p)' in s;s=s.replace('.ip_tenor_bundle_breakdown($p)','');save(rel,s)
rel='theme-v1.9.0/instapass/inc/promotions.php';s=read(rel);assert '%d or more items (including repeats of the same offer)' in s;save(rel,s.replace('%d or more items (including repeats of the same offer)','%d eligible items'))
rel='mu-plugins/instapass-launch-catalog.php';s=read(rel);s=s.replace('Version: 1.3.0','Version: 1.3.1');a='Choose your duration</legend>';assert a in s;s=s.replace(a,"'.(count($offers)===1&&isset($offers[12])?'Annual plan':'Choose your duration').'</legend>")
a=" $h.='<span class=\"ipl-credit-primary\">";start=s.index(a);end=s.index(" $h.=apply_filters('ipl_credit_price_note'",start)
block=''' $rates=function_exists('ip_tenor_bundle_rates')?ip_tenor_bundle_rates($p,['full'=>$full,'months'=>1,'currency'=>$currency]):[];$best=null;foreach($rates as $rate)if(!$best||$rate['full']<$best['full'])$best=$rate;
 if($best)$h.='<span class="ip-offer-savings"><span class="ip-offer-saving-total">Additional '.esc_html($best['percent']).'% off with '.esc_html($best['code']).'<small>'.esc_html($best['tools'].' eligible items required.').'</small></span></span>';
 $h.='<span class="ipl-credit-primary">'.$money($full).'</span><span class="ipl-credit-payment">One-time credit purchase</span><span class="ip-term-upfront">For $'.(int)$face.' in credits</span>';
''';s=s[:start]+block+s[end:];save(rel,s)
(r/'outputs/tile-copy-source-manifest.json').write_text(json.dumps(m,indent=2));print('Prepared four scoped display-only source changes')
