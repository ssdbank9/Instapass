import datetime,hashlib,json,os,pathlib,shutil,subprocess
root=pathlib.Path('/opt/instapass');stage=pathlib.Path('/home/ubuntu/instapass-pricing-20261108');m=json.loads((stage/'outputs/four-currency-copy-manifest.json').read_text());os.umask(0o077)
def target(r):return root/'wordpress/wp-content'/('themes/'+r.removeprefix('theme-v1.9.0/') if r.startswith('theme-v1.9.0/') else r)
for rel,h in m['before'].items():assert hashlib.sha256(target(rel).read_bytes()).hexdigest()==h,'Copy drift: '+rel
for rel in m['after']:
 subprocess.run(['docker','run','--rm','--network','none','--entrypoint','php','-v',str(stage/rel)+':/tmp/source.php:ro','wordpress@sha256:661db880e953da051abcac6c0a769767b995a82cd52101c003ca7438dd427d7d','-l','/tmp/source.php'],check=True)
w=root/('recovery/pricing-copy-'+datetime.datetime.now(datetime.timezone.utc).strftime('%Y%m%dT%H%M%SZ'));w.mkdir(mode=0o700)
for rel in m['after']:
 old=w/rel;old.parent.mkdir(parents=True,exist_ok=True);shutil.copyfile(target(rel),old);p=target(rel);temp=p.with_suffix(p.suffix+'.next');shutil.copyfile(stage/rel,temp);temp.chmod(0o644);os.replace(temp,p)
assert all(hashlib.sha256(target(r).read_bytes()).hexdigest()==h for r,h in m['after'].items());print('COPY_RELEASE '+str(w))
