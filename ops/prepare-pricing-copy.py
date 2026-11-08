from pathlib import Path
import hashlib,json
r=Path(__file__).resolve().parents[1];m={'before':{},'after':{}}
def edit(rel,a,b):
 p=r/rel;s=p.read_text(encoding='utf-8');assert a in s
 m['before'][rel]=hashlib.sha256(p.read_bytes()).hexdigest();p.write_text(s.replace(a,b),encoding='utf-8',newline='\n');m['after'][rel]=hashlib.sha256(p.read_bytes()).hexdigest()
edit('theme-v1.9.0/instapass/patterns/page-faq.php',', including the 7.5% bundle code','')
edit('plugins/instapass-guest-orders/instapass-guest-orders.php','Use TOOLKIT5 for 5% off two or more eligible items, TOOLKIT75 for 7.5% off three or more, or TOOLKIT10 for 10% off four or more in one order.','Current enabled codes, discounts and minimum quantities are listed under Products &rarr; Promotions.')
edit('plugins/instapass-control-center/instapass-control-center.php','Customers can choose USD, GBP or PKR;','Customers can choose USD, GBP, EUR or PKR;')
# Second edit of same file preserves its original pre-copy hash.
rel='plugins/instapass-control-center/instapass-control-center.php';p=r/rel;s=p.read_text(encoding='utf-8');s=s.replace('<strong>GBP (£):</strong> Set independent','<strong>GBP (£) and EUR (€):</strong> Set independent').replace('Leave GBP blank until ready;','Leave a GBP or EUR price blank until ready;');p.write_text(s,encoding='utf-8',newline='\n');m['after'][rel]=hashlib.sha256(p.read_bytes()).hexdigest()
edit('plugins/instapass-control-center/includes/rate-settings.php','GBP uses the separate prices you enter on each product. It is never converted from USD.','GBP and EUR use the separate prices you enter on each product. They are never converted from USD.')
(r/'outputs/four-currency-copy-manifest.json').write_text(json.dumps(m,indent=2));print('Four copy-only source updates prepared')
