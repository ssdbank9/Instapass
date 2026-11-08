from pathlib import Path
import zipfile,hashlib
root=Path(__file__).resolve().parents[1]
targets=['plugins/instapass-guest-orders/account-allocation.js','plugins/instapass-guest-orders/account-allocation.php','theme-v1.9.0/instapass/patterns/page-faq.php','plugins/instapass-guest-orders/instapass-guest-orders.php']
with zipfile.ZipFile(root/'outputs/account-emails-next.zip','w',zipfile.ZIP_DEFLATED) as z:
 for name in ('instapass-control-center','instapass-tenor-offers','instapass-guest-orders'):
  for p in (root/'plugins'/name).rglob('*'):
   if p.is_file():z.write(p,p.relative_to(root).as_posix())
 for f in ('inc/promotions.php','inc/product-art.php','patterns/page-faq.php'):z.write(root/'theme-v1.9.0/instapass'/f,'theme/'+Path(f).name)
 manifest=[]
 for src in targets:
  stage=src if src.startswith('plugins/') else 'theme/'+Path(src).name
  dest='wp-content/'+src if src.startswith('plugins/') else 'wp-content/themes/instapass/'+src.split('/instapass/')[1]
  manifest.append('\t'.join([stage,dest,hashlib.sha256((root/src).read_bytes()).hexdigest()]))
 z.writestr('deploy.tsv','\n'.join(manifest)+'\n')
 z.writestr('integration.php',(root/'ops/account-allocation-integration.php').read_text().replace('account-allocation','account-emails').replace('19062','19064'))
 for src,dest in [('offer-controls-mu.php','mu/fixture.php'),('owner-pricing-audit.php','audit.php'),('control-center-currency-integration.php','currency.php')]:z.write(root/'ops'/src,dest)
 for src,dest in [('test-offer-controls.sh','test.sh'),('cleanup-offer-controls.sh','cleanup.sh'),('deploy-offer-controls.sh','deploy.sh')]:
  text=(root/'ops'/src).read_text().replace('offer-controls','account-emails').replace('ip-offer-','ip-email-').replace('19061','19064')
  if dest=='deploy.sh':text=text.replace('-eq 12','-eq 4').replace('All 12','All 4').replace('main=wp-content/plugins/instapass-control-center/instapass-control-center.php','main=wp-content/plugins/instapass-guest-orders/instapass-guest-orders.php')
  z.writestr(dest,text)
print('Account email inputs archive prepared;4 runtime targets.')
