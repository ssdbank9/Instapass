from pathlib import Path
import zipfile,hashlib
root=Path(__file__).resolve().parents[1]
targets=['plugins/instapass-control-center/includes/offer-manager.php','plugins/instapass-control-center/assets/offer-savings.css','plugins/instapass-tenor-offers/monthly-pricing.php','plugins/instapass-tenor-offers/instapass-tenor-offers.php','theme-v1.9.0/instapass/patterns/page-faq.php','plugins/instapass-control-center/instapass-control-center.php']
with zipfile.ZipFile(root/'outputs/official-savings-next.zip','w',zipfile.ZIP_DEFLATED) as z:
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
 for src,dest in [('official-savings-integration.php','integration.php'),('official-savings-pro-setup.php','pro-setup.php'),('official-savings-catalog-audit.php','catalog-audit.php'),('deploy-pro-offer.sh','pro-deploy.sh'),('offer-controls-mu.php','mu/fixture.php'),('owner-pricing-audit.php','audit.php'),('control-center-currency-integration.php','currency.php')]:z.write(root/'ops'/src,dest)
 for src,dest in [('test-offer-controls.sh','test.sh'),('cleanup-offer-controls.sh','cleanup.sh'),('deploy-offer-controls.sh','deploy.sh')]:
  text=(root/'ops'/src).read_text().replace('offer-controls','official-savings').replace('ip-offer-','ip-save-').replace('19061','19063')
  if dest=='test.sh':text=text.replace(' -v "$next/mu"',' -v "$next/pro-setup.php":/tmp/pro-setup.php:ro -v "$next/mu"')
  if dest=='deploy.sh':text=text.replace('-eq 12','-eq 6').replace('All 12','All 6')
  z.writestr(dest,text)
print('Official savings archive prepared;6 runtime targets.')
