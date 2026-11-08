from pathlib import Path
import zipfile,hashlib
root=Path(__file__).resolve().parents[1]
targets=[
 'plugins/instapass-control-center/instapass-control-center.php',
 'plugins/instapass-control-center/includes/offer-manager.php',
 'plugins/instapass-guest-orders/account-allocation.php',
 'plugins/instapass-guest-orders/account-allocation.js',
 'plugins/instapass-guest-orders/account-allocation.css',
 'plugins/instapass-guest-orders/instapass-guest-orders.php',
 'theme-v1.9.0/instapass/patterns/page-faq.php',
 'theme-v1.9.0/instapass/inc/payment-confirmation.php']
with zipfile.ZipFile(root/'outputs/account-allocation-next.zip','w',zipfile.ZIP_DEFLATED) as z:
 for name in ('instapass-control-center','instapass-tenor-offers','instapass-guest-orders'):
  for p in (root/'plugins'/name).rglob('*'):
   if p.is_file():z.write(p,p.relative_to(root).as_posix())
 for f in ('inc/promotions.php','inc/product-art.php','patterns/page-faq.php','inc/payment-confirmation.php'):z.write(root/'theme-v1.9.0/instapass'/f,'theme/'+Path(f).name)
 manifest=[]
 for src in targets:
  stage=src if src.startswith('plugins/') else 'theme/'+Path(src).name
  dest='wp-content/'+src if src.startswith('plugins/') else 'wp-content/themes/instapass/'+src.split('/instapass/')[1]
  manifest.append('\t'.join([stage,dest,hashlib.sha256((root/src).read_bytes()).hexdigest()]))
 z.writestr('deploy.tsv','\n'.join(manifest)+'\n')
 for src,dest in [('account-allocation-integration.php','integration.php'),('offer-controls-mu.php','mu/fixture.php'),('owner-pricing-audit.php','audit.php'),('control-center-currency-integration.php','currency.php')]:z.write(root/'ops'/src,dest)
 for src,dest in [('test-offer-controls.sh','test.sh'),('cleanup-offer-controls.sh','cleanup.sh'),('deploy-offer-controls.sh','deploy.sh')]:
  text=(root/'ops'/src).read_text().replace('offer-controls','account-allocation').replace('ip-offer-','ip-alloc-').replace('19061','19062')
  if dest=='test.sh':text=text.replace(' -v "$next/mu"', ' -v "$next/theme/payment-confirmation.php":/var/www/html/wp-content/themes/instapass/inc/payment-confirmation.php:ro -v "$next/mu"')
  if dest=='deploy.sh':
   text=text.replace('-eq 12','-eq 8').replace('All 12','All 8').replace('(promotions|product-art|page-faq)','(promotions|product-art|page-faq|payment-confirmation)').replace('inc/(promotions|product-art)','inc/(promotions|product-art|payment-confirmation)')
  z.writestr(dest,text)
print('Account allocation archive prepared; 8 runtime targets.')
