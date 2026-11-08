from pathlib import Path
import hashlib
import zipfile

root=Path(__file__).resolve().parents[1]
archive=root/'.qa'/'control-center-ui-0.7.3.zip'
files=[]
files.extend((p,Path('plugins/instapass-control-center')/p.relative_to(root/'plugins'/'instapass-control-center')) for p in (root/'plugins'/'instapass-control-center').rglob('*') if p.is_file())
for source,destination in [
    ('ops/offer-controls-integration.php','integration.php'),
    ('ops/control-center-currency-integration.php','currency.php'),
    ('ops/offer-controls-browser.cjs','browser.cjs'),
    ('ops/test-offer-controls.sh','test.sh'),
    ('ops/cleanup-offer-controls.sh','cleanup.sh'),
    ('ops/offer-controls-mu.php','mu/fixture.php'),
    ('ops/owner-pricing-audit.php','audit.php'),
]:
    files.append((root/source,Path(destination)))
manifest=[]
with zipfile.ZipFile(archive,'w',compression=zipfile.ZIP_DEFLATED) as z:
    for source,relative in files:
        data=source.read_bytes()
        z.writestr(relative.as_posix(),data)
        manifest.append(f'{relative.as_posix()}\t{hashlib.sha256(data).hexdigest()}')
    z.writestr('SOURCE-SHA256.tsv','\n'.join(manifest)+'\n')
print(f'Created {archive} with {len(files)} scoped source files; SHA256 {hashlib.sha256(archive.read_bytes()).hexdigest()}')
