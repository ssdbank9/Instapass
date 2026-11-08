import datetime,hashlib,json,os,pathlib,shutil,subprocess,sys
ROOT=pathlib.Path('/opt/instapass');STAGE=pathlib.Path('/home/ubuntu/instapass-tile-copy-20261108');OLD=pathlib.Path('/home/ubuntu/instapass-credit-cycle-20261108');CLI='wordpress@sha256:661db880e953da051abcac6c0a769767b995a82cd52101c003ca7438dd427d7d';WEB='wordpress@sha256:d42f63cb89ad0eab272c4b393164e63830d0df270009907919f7dcce52b1a20b';os.umask(0o077);m=json.loads((STAGE/'outputs/tile-copy-source-manifest.json').read_text())
def run(*a):return subprocess.check_output(a,text=True)
def relpath(r):return 'themes/'+r.removeprefix('theme-v1.9.0/') if r.startswith('theme-v1.9.0/') else r
def target(r):return ROOT/'wordpress/wp-content'/relpath(r)
def audit():return run('docker','compose','--env-file',str(ROOT/'.env'),'-f',str(ROOT/'maintenance.yaml'),'run','--rm','--user','0:0','-e','WP_CLI_ALLOW_ROOT=1','-v',str(STAGE/'ops/tile-copy-audit.php')+':/tmp/audit.php:ro','wpcli','eval-file','/tmp/audit.php')
mode=sys.argv[1]
if mode=='preview':
 state=json.loads((OLD/'test-state.json').read_text());w=pathlib.Path(state['work']);assert state['web'].startswith('ipce-web-')
 for r in m['after']:
  if r.startswith('mu-plugins/'):p=w/'mu'/pathlib.Path(r).name;shutil.copyfile(STAGE/r,p);p.chmod(0o644)
 for r in m['after']:run('docker','run','--rm','--network','none','--entrypoint','php','-v',str(STAGE/r)+':/tmp/source.php:ro',CLI,'-l','/tmp/source.php')
 run('docker','rm','-f',state['web']);mounts=['-v',str(ROOT/'wordpress')+':/var/www/html:ro','-v',str(w/'mu')+':/tmp/mu:ro','-v',str(w/'assets.conf')+':/etc/apache2/conf-enabled/zz-assets.conf:ro','-v',str(ROOT/'php-security.ini')+':/usr/local/etc/php/conf.d/zz-security.ini:ro']
 for r in m['after']:
  if not r.startswith('mu-plugins/'):mounts+=['-v',str(STAGE/r)+':/var/www/html/wp-content/'+relpath(r)+':ro']
 run('docker','run','-d','--name',state['web'],'--network',state['net'],'--memory','768m','--env-file',str(w/'cli.env'),*mounts,WEB);state['ip']=list(json.loads(run('docker','inspect',state['web']))[0]['NetworkSettings']['Networks'].values())[0]['IPAddress'];(OLD/'test-state.json').write_text(json.dumps(state));print('PREVIEW '+state['ip'])
elif mode=='deploy':
 assert '--browser-passed' in sys.argv
 for r,h in m['before'].items():assert hashlib.sha256(target(r).read_bytes()).hexdigest()==h,'Source drift '+r
 w=ROOT/('recovery/tile-copy-'+datetime.datetime.now(datetime.timezone.utc).strftime('%Y%m%dT%H%M%SZ'));w.mkdir(mode=0o700);before=audit();(w/'audit-before.json').write_text(before)
 for r in m['after']:p=w/r;p.parent.mkdir(parents=True,exist_ok=True);shutil.copyfile(target(r),p)
 try:
  for r in m['after']:p=target(r);temp=p.with_suffix(p.suffix+'.next');shutil.copyfile(STAGE/r,temp);temp.chmod(0o644);os.replace(temp,p)
  after=audit();(w/'audit-after.json').write_text(after);assert json.loads(before)==json.loads(after),'Data/options changed during code-only update'
  assert all(hashlib.sha256(target(r).read_bytes()).hexdigest()==h for r,h in m['after'].items());print('LIVE_COPY_RELEASE '+str(w));print('Product/order/options hashes unchanged')
 except BaseException:
  for r in m['after']:shutil.copyfile(w/r,target(r))
  raise
else:raise RuntimeError('Unknown mode')
