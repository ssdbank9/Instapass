"""Build the scoped four-currency release, preserving pre-edit source hashes."""
from pathlib import Path
import hashlib,json
ROOT=Path(__file__).resolve().parents[1]
base=ROOT/'plugins/instapass-control-center'
files={}
before={}
def read(rel):
 p=ROOT/rel; s=p.read_text(encoding='utf-8');before[rel]=hashlib.sha256(p.read_bytes()).hexdigest();return s
def replace(s,a,b,n=None):
 assert a in s, a
 if n is not None: assert s.count(a)==n,(a,s.count(a))
 return s.replace(a,b)
for name in ['editor','values']:
 rel=f'plugins/instapass-control-center/includes/eur-{name}.php'
 assert not (ROOT/rel).exists()
 files[rel]=(base/f'includes/gbp-{name}.php').read_text(encoding='utf-8').replace('GBP','EUR').replace('gbp','eur').replace('£','€').replace('pounds','euros')
rel='plugins/instapass-control-center/includes/currencies.php';s=read(rel)
for a,b in [
 ("['USD','GBP','PKR']","['USD','GBP','EUR','PKR']"),
 ("if($currency==='GBP'){","if(in_array($currency,['GBP','EUR'],true)){"),
 ("try{$v=Instapass_Control_GBP::values((string)$product->get_meta('_instapass_gbp_regular_price'),$active?(string)$product->get_meta('_instapass_gbp_sale_price'):'');}","$prefix='_instapass_'.strtolower($currency).'_';$parser=$currency==='EUR'?'Instapass_Control_EUR':'Instapass_Control_GBP';\n  try{$v=$parser::values((string)$product->get_meta($prefix.'regular_price'),$active?(string)$product->get_meta($prefix.'sale_price'):'');}"),
 ("instapass_cc_currency()!=='GBP'&&!", "!in_array(instapass_cc_currency(),['GBP','EUR'],true)&&!"),
 ("'GBP'=>'GBP £','PKR'", "'GBP'=>'GBP £','EUR'=>'EUR €','PKR'"),
 ("instapass_cc_currency()==='GBP'&&$p->get_price()===''", "in_array(instapass_cc_currency(),['GBP','EUR'],true)&&$p->get_price()===''"),
 ("return '<span class=\"ip-gbp-unpriced\">GBP price coming soon.","return '<span class=\"ip-gbp-unpriced\">'.esc_html(instapass_cc_currency()).' price coming soon."),
 ('GBP prices are independent;', 'GBP and EUR prices are independent;'),
 ('independent GBP/PKR sales','independent GBP/EUR/PKR sales')]:s=replace(s,a,b)
files[rel]=s
rel='plugins/instapass-control-center/includes/offer-manager.php';s=read(rel)
for a,b in [
 ("'gbp_discount'=>'',", "'gbp_discount'=>'','eur_regular'=>'','eur_sale'=>'','eur_discount'=>'',"),
 ("'gbp_sale'=>'_instapass_gbp_sale_price'", "'gbp_sale'=>'_instapass_gbp_sale_price','eur_regular'=>'_instapass_eur_regular_price','eur_sale'=>'_instapass_eur_sale_price'"),
 ("$gbp=Instapass_Control_GBP::values($v['gbp_regular'],$v['gbp_sale'],$v['gbp_discount']);", "$gbp=Instapass_Control_GBP::values($v['gbp_regular'],$v['gbp_sale'],$v['gbp_discount']);\n $eur=Instapass_Control_EUR::values($v['eur_regular'],$v['eur_sale'],$v['eur_discount']);"),
 ("['pkr'=>$pkr,'gbp'=>$gbp]", "['pkr'=>$pkr,'gbp'=>$gbp,'eur'=>$eur]"),
 ("['pkr','gbp']", "['pkr','gbp','eur']"),
 ("'gbp_sale','gbp_discount']", "'gbp_sale','gbp_discount','eur_regular','eur_sale','eur_discount']"),
 ("'pkr'=>'PKR','gbp'=>'GBP'", "'pkr'=>'PKR','gbp'=>'GBP','eur'=>'EUR'"),
 ('GBP is always independent.', 'GBP and EUR are always independent.'),
 ("if($currency==='GBP')$h.=' See the independent GBP offer price below.';", "if(in_array($currency,['GBP','EUR'],true))$h.=' See the independent '.esc_html($currency).' offer price below.';")]:s=replace(s,a,b)
files[rel]=s
rel='plugins/instapass-control-center/includes/bulk-pricing.php';s=read(rel)
for a,b in [
 ('USD, PKR or GBP','USD, PKR, GBP or EUR'),('USD/GBP batches','USD/GBP/EUR batches'),('GBP is always independent','GBP and EUR are always independent'),
 ('<option value="GBP">GBP</option>','<option value="GBP">GBP</option><option value="EUR">EUR</option>'),
 ('<th>GBP sale</th>','<th>GBP sale</th><th>EUR regular</th><th>EUR sale</th>'),
 ("['GBP','PKR']","['GBP','EUR','PKR']"),("$currency==='GBP'?'_instapass_gbp_':'_instapass_pkr_'","'_instapass_'.strtolower($currency).'_'"),
 ("'gbp'=>self::snap($p,'GBP')", "'gbp'=>self::snap($p,'GBP'),'eur'=>self::snap($p,'EUR')"),
 ("['USD','PKR','GBP']","['USD','PKR','GBP','EUR']")]:s=replace(s,a,b)
files[rel]=s
rel='plugins/instapass-control-center/assets/bulk-pricing.js';s=read(rel)
for a,b in [
 ("cell(tr,p.gbp.sale||'—');", "cell(tr,p.gbp.sale||'—');cell(tr,p.eur.regular||'Unpriced');cell(tr,p.eur.sale||'—');"),
 ('GBP remains independent.', 'GBP and EUR remain independent.'),('GBP is always independent.', 'GBP and EUR are always independent.'),
 ("['USD','PKR','GBP']","['USD','PKR','GBP','EUR']"),('USD, PKR or GBP','USD, PKR, GBP or EUR'),
 ("['GBP',p.gbp]", "['GBP',p.gbp],['EUR',p.eur]")]:s=replace(s,a,b)
files[rel]=s
rel='plugins/instapass-control-center/assets/offers.js';s=read(rel);files[rel]=replace(s,"['usd','pkr','gbp']","['usd','pkr','gbp','eur']")
rel='plugins/instapass-control-center/instapass-control-center.php';s=read(rel)
s=replace(s,'0.7.3','0.8.0');s=replace(s,"require_once __DIR__ . '/includes/gbp-editor.php';","require_once __DIR__ . '/includes/gbp-editor.php';\nrequire_once __DIR__ . '/includes/eur-editor.php';")
files[rel]=replace(s,'independent GBP prices','independent GBP/EUR prices')
rel='theme-v1.9.0/instapass/inc/promotions.php';s=read(rel)
files[rel]=replace(s,'%d items (including repeats of the same offer)','%d or more items (including repeats of the same offer)')
for rel,s in files.items():(ROOT/rel).write_text(s,encoding='utf-8',newline='\n')
manifest={'before':before,'after':{r:hashlib.sha256((ROOT/r).read_bytes()).hexdigest() for r in files}}
(ROOT/'outputs/four-currency-source-manifest.json').write_text(json.dumps(manifest,indent=2))
print(json.dumps({'files':list(files),'count':len(files)}))
