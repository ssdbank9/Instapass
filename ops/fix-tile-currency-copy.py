from pathlib import Path
import hashlib,json
r=Path(__file__).resolve().parents[1];p=r/'plugins/instapass-control-center/includes/offer-manager.php';s=p.read_text(encoding='utf-8');a="  $rates=function_exists('ip_tenor_bundle_rates')?ip_tenor_bundle_rates($p,['full'=>$current,'months'=>$months,'currency'=>$currency]):[];";start=s.index(a);end=s.index(" $has_saving=$h!=='';",start);old=s[start:end];assert "if($best&&$best['full']<$baseline)" in old
new=old.replace("  if($best&&$best['full']<$baseline)"," if($best&&$best['full']<$current)")
# Close the USD/PKR comparison first; the percentage coupon is valid in every priced currency.
assert new.endswith(" }\n");new=" }\n"+new[:-3];new=new.replace("$rates=function_exists('ip_tenor_bundle_rates')?", "$rates=$priced&&function_exists('ip_tenor_bundle_rates')?")
s=s[:start]+new+s[end:];p.write_text(s,encoding='utf-8',newline='\n');m=r/'outputs/tile-copy-source-manifest.json';v=json.loads(m.read_text());v['after']['plugins/instapass-control-center/includes/offer-manager.php']=hashlib.sha256(p.read_bytes()).hexdigest();m.write_text(json.dumps(v,indent=2));print('Compact coupon message now covers GBP/EUR as well')
