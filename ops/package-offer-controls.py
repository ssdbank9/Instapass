from pathlib import Path
import zipfile, hashlib

root = Path(__file__).resolve().parents[1]
out = root / 'outputs'
out.mkdir(exist_ok=True)
targets = [
 ('plugins/instapass-control-center/instapass-control-center.php','wp-content/plugins/instapass-control-center/instapass-control-center.php'),
 ('plugins/instapass-control-center/includes/currencies.php','wp-content/plugins/instapass-control-center/includes/currencies.php'),
 ('plugins/instapass-control-center/includes/offer-manager.php','wp-content/plugins/instapass-control-center/includes/offer-manager.php'),
 ('plugins/instapass-control-center/assets/offers.css','wp-content/plugins/instapass-control-center/assets/offers.css'),
 ('plugins/instapass-control-center/assets/offers.js','wp-content/plugins/instapass-control-center/assets/offers.js'),
 ('plugins/instapass-control-center/assets/offer-savings.css','wp-content/plugins/instapass-control-center/assets/offer-savings.css'),
 ('plugins/instapass-tenor-offers/instapass-tenor-offers.php','wp-content/plugins/instapass-tenor-offers/instapass-tenor-offers.php'),
 ('plugins/instapass-tenor-offers/monthly-pricing.php','wp-content/plugins/instapass-tenor-offers/monthly-pricing.php'),
 ('plugins/instapass-guest-orders/instapass-guest-orders.php','wp-content/plugins/instapass-guest-orders/instapass-guest-orders.php'),
 ('theme-v1.9.0/instapass/inc/promotions.php','wp-content/themes/instapass/inc/promotions.php'),
 ('theme-v1.9.0/instapass/inc/product-art.php','wp-content/themes/instapass/inc/product-art.php'),
 ('theme-v1.9.0/instapass/patterns/page-faq.php','wp-content/themes/instapass/patterns/page-faq.php'),
]
with zipfile.ZipFile(out/'offer-controls-next.zip','w',zipfile.ZIP_DEFLATED) as z:
 for name in ('instapass-control-center','instapass-tenor-offers','instapass-guest-orders'):
  for p in (root/'plugins'/name).rglob('*'):
   if p.is_file(): z.write(p,p.relative_to(root).as_posix())
 manifest=[]
 for src, dest in targets:
  stage = src if src.startswith('plugins/') else 'theme/'+Path(src).name
  if src.startswith('theme-'): z.write(root/src,stage)
  manifest.append('\t'.join((stage,dest,hashlib.sha256((root/src).read_bytes()).hexdigest())))
 z.writestr('deploy.tsv','\n'.join(manifest)+'\n')
 for src,dest in [('offer-controls-integration.php','integration.php'),('offer-controls-mu.php','mu/fixture.php'),('test-offer-controls.sh','test.sh'),('cleanup-offer-controls.sh','cleanup.sh'),('owner-pricing-audit.php','audit.php'),('deploy-offer-controls.sh','deploy.sh'),('control-center-currency-integration.php','currency.php')]:z.write(root/'ops'/src,dest)
print('Packaged 12 runtime targets; source archive SHA256',hashlib.sha256((out/'offer-controls-next.zip').read_bytes()).hexdigest())
