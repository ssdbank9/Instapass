import datetime,hashlib,json,os,pathlib,shutil,subprocess,sys,time,secrets
ROOT=pathlib.Path('/opt/instapass');STAGE=pathlib.Path('/home/ubuntu/instapass-pricing-20261108')
CLI='wordpress@sha256:661db880e953da051abcac6c0a769767b995a82cd52101c003ca7438dd427d7d';WEB='wordpress@sha256:d42f63cb89ad0eab272c4b393164e63830d0df270009907919f7dcce52b1a20b'
manifest=json.loads((STAGE/'outputs/four-currency-source-manifest.json').read_text());files=list(manifest['after'])
def target(rel):
 return ROOT/'wordpress/wp-content'/('themes/'+rel.removeprefix('theme-v1.9.0/') if rel.startswith('theme-v1.9.0/') else rel)
def run(*a,**k):return subprocess.check_output(a,text=True,**k)
def baseline():
 for rel in files:
  p=target(rel)
  if rel in manifest['before']:assert p.exists() and hashlib.sha256(p.read_bytes()).hexdigest()==manifest['before'][rel], 'Live source drift: '+rel
  else:assert not p.exists(),'Unexpected new live file: '+rel
def mounts(w):
 return ['-v',str(ROOT/'wordpress')+':/var/www/html:ro','-v',str(w/'mu')+':/tmp/mu:ro','-v',str(w/'cc')+':/var/www/html/wp-content/plugins/instapass-control-center:ro','-v',str(STAGE/'theme-v1.9.0/instapass/inc/promotions.php')+':/var/www/html/wp-content/themes/instapass/inc/promotions.php:ro','-v',str(ROOT/'php-security.ini')+':/usr/local/etc/php/conf.d/zz-security.ini:ro','-v',str(w)+':'+str(w)]
def testcli(state,file):
 w=pathlib.Path(state['work']);return run('docker','run','--rm','--user','0:0','-e','WP_CLI_ALLOW_ROOT=1','--network',state['net'],'--env-file',str(w/'cli.env'),'-e','IP4_APPLY=yes','-e','IP4_BACKUP='+str(w/'pricing-before.json'),*mounts(w),'-v',str(STAGE/'ops'/file)+':/tmp/test.php:ro',CLI,'eval-file','/tmp/test.php')
def livecli(file,w):
 return run('docker','compose','--env-file',str(ROOT/'.env'),'-f',str(ROOT/'maintenance.yaml'),'run','--rm','--user','0:0','-e','WP_CLI_ALLOW_ROOT=1','-e','IP4_APPLY=yes','-e','IP4_BACKUP='+str(w/'pricing-before.json'),'-v',str(w)+':'+str(w),'-v',str(STAGE/'ops'/file)+':/tmp/test.php:ro','wpcli','eval-file','/tmp/test.php')
os.umask(0o077);mode=sys.argv[1]
if mode=='prepare':
 baseline();stamp=datetime.datetime.now(datetime.timezone.utc).strftime('%Y%m%dT%H%M%SZ');w=ROOT/('recovery/pricing-test-'+stamp);w.mkdir(mode=0o700)
 state={'work':str(w),'net':'ip4-test-'+stamp,'db':'ip4-db-'+stamp,'web':'ip4-web-'+stamp};(STAGE/'test-state.json').write_text(json.dumps(state))
 cc=w/'cc';shutil.copytree(ROOT/'wordpress/wp-content/plugins/instapass-control-center',cc)
 for rel in files:
  if rel.startswith('plugins/'):p=cc/rel.split('instapass-control-center/',1)[1];p.parent.mkdir(parents=True,exist_ok=True);shutil.copyfile(STAGE/rel,p)
 for p in [cc,*cc.rglob('*')]:p.chmod(0o755 if p.is_dir() else 0o644)
 mu=w/'mu';shutil.copytree(ROOT/'wordpress/wp-content/mu-plugins',mu)
 for rel in files:
  if rel.startswith('mu-plugins/'):shutil.copyfile(STAGE/rel,mu/pathlib.Path(rel).name)
 for p in [mu,*mu.rglob('*')]:p.chmod(0o755 if p.is_dir() else 0o644)
 fixture='''<?php if(getenv('INSTAPASS_ISOLATED_TEST')!=='yes')return;
add_filter('option_home',fn()=>'http://127.0.0.1:19101');add_filter('option_siteurl',fn()=>'http://127.0.0.1:19101');add_filter('pre_wp_mail',fn()=>true,999);add_filter('pre_http_request',fn()=>new WP_Error('isolated','External HTTP disabled'),999);
add_filter('content_url',fn($url)=>preg_replace('~^https?://instapass.online~','http://127.0.0.1:19101',$url));
''';(mu/'test-fixture.php').write_text(fixture);(mu/'test-fixture.php').chmod(0o644)
 password=secrets.token_hex(24);(w/'db.env').write_text('MYSQL_ROOT_PASSWORD='+password+'\n');dbname=run('docker','exec','instapass-db-1','printenv','MYSQL_DATABASE').strip()
 (w/'cli.env').write_text('WORDPRESS_DB_HOST='+state['db']+':3306\nWORDPRESS_DB_USER=root\nWORDPRESS_DB_PASSWORD='+password+'\nWORDPRESS_DB_NAME='+dbname+'\nINSTAPASS_ISOLATED_TEST=yes\nWORDPRESS_CONFIG_EXTRA=define("DISABLE_WP_CRON",true);define("WPMU_PLUGIN_DIR","/tmp/mu");\n')
 run('docker','network','create','--internal',state['net']);image=json.loads(run('docker','inspect','instapass-db-1'))[0]['Image'];run('docker','run','-d','--name',state['db'],'--network',state['net'],'--memory','768m','--tmpfs','/var/lib/mysql:rw,size=512m','--env-file',str(w/'db.env'),image)
 for i in range(60):
  try:run('docker','exec',state['db'],'sh','-c','mariadb -uroot -p"$MYSQL_ROOT_PASSWORD" -N -e "SELECT 1"',stderr=subprocess.DEVNULL);break
  except subprocess.CalledProcessError:time.sleep(1)
 else:raise RuntimeError('Clone DB not ready')
 dump=subprocess.Popen(['docker','exec','instapass-db-1','sh','-c','exec mariadb-dump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction --quick --databases "$MYSQL_DATABASE"'],stdout=subprocess.PIPE);subprocess.run(['docker','exec','-i',state['db'],'sh','-c','exec mariadb -uroot -p"$MYSQL_ROOT_PASSWORD"'],stdin=dump.stdout,check=True);dump.stdout.close();assert dump.wait()==0
 for rel in files+['ops/four-currency-migrate.php','ops/four-currency-check.php','ops/four-currency-rollback.php']:
  if rel.endswith('.php'):run('docker','run','--rm','--network','none','--entrypoint','php','-v',str(STAGE/rel)+':/tmp/code.php:ro',CLI,'-l','/tmp/code.php')
 print('Source hashes and PHP lint pass',flush=True)
 for file in ['four-currency-migrate.php','four-currency-check.php']:
  result=testcli(state,file);(w/(file+'.result')).write_text(result);print(result,flush=True)
 conf=w/'assets.conf';conf.write_text('Alias /wp-content/mu-plugins/ /tmp/mu/\n<Directory /tmp/mu>\nRequire all granted\n</Directory>\n');conf.chmod(0o644)
 run('docker','run','-d','--name',state['web'],'--network',state['net'],'--memory','768m','--env-file',str(w/'cli.env'),*mounts(w),'-v',str(conf)+':/etc/apache2/conf-enabled/zz-assets.conf:ro',WEB)
 state['ip']=list(json.loads(run('docker','inspect',state['web']))[0]['NetworkSettings']['Networks'].values())[0]['IPAddress'];(STAGE/'test-state.json').write_text(json.dumps(state));print('ISOLATED_READY '+json.dumps(state),flush=True)
elif mode=='refresh':
 state=json.loads((STAGE/'test-state.json').read_text());w=pathlib.Path(state['work'])
 for rel in files:
  if rel.startswith('mu-plugins/'):p=w/'mu'/pathlib.Path(rel).name;shutil.copyfile(STAGE/rel,p);p.chmod(0o644)
 print('Preview source refreshed')
elif mode=='check':
 state=json.loads((STAGE/'test-state.json').read_text());print(testcli(state,'four-currency-check.php'))
elif mode=='deploy':
 assert '--browser-passed' in sys.argv;baseline();stamp=datetime.datetime.now(datetime.timezone.utc).strftime('%Y%m%dT%H%M%SZ');w=ROOT/('recovery/pricing-release-'+stamp);w.mkdir(mode=0o700);(STAGE/'release-state.json').write_text(json.dumps({'work':str(w)}))
 with (w/'database-before.sql').open('wb') as out:subprocess.run(['docker','exec','instapass-db-1','sh','-c','exec mariadb-dump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction --quick --databases "$MYSQL_DATABASE"'],stdout=out,check=True)
 for rel in files:
  p=target(rel)
  if p.exists():dest=w/'sources'/rel;dest.parent.mkdir(parents=True,exist_ok=True);shutil.copyfile(p,dest)
 try:
  ordered=sorted(files,key=lambda r:r.endswith('/instapass-control-center.php'))
  for rel in ordered:
   p=target(rel);temp=p.with_suffix(p.suffix+'.next');shutil.copyfile(STAGE/rel,temp);temp.chmod(0o644);os.replace(temp,p)
  print(livecli('four-currency-migrate.php',w),flush=True);print(livecli('four-currency-check.php',w),flush=True)
  for url in ['https://instapass.online/','https://instapass.online/shop/','https://wasooli.duckdns.org/healthz']:run('curl','-fsS','--max-time','25','-o','/dev/null',url)
  assert all(hashlib.sha256(target(r).read_bytes()).hexdigest()==manifest['after'][r] for r in files)
  print('LIVE_RELEASE '+str(w),flush=True)
 except BaseException:
  if (w/'pricing-before.json').exists():print(livecli('four-currency-rollback.php',w),flush=True)
  for rel in files:
   p=target(rel);old=w/'sources'/rel
   if old.exists():shutil.copyfile(old,p)
   elif rel not in manifest['before']:p.unlink(missing_ok=True)
  raise
elif mode=='cleanup':
 state=json.loads((STAGE/'test-state.json').read_text())
 for k in ['web','db']:assert state[k].startswith('ip4-');run('docker','rm','-f',state[k])
 assert state['net'].startswith('ip4-test-');run('docker','network','rm',state['net']);print('Own clone containers and network removed; protected recovery retained')
else:raise RuntimeError('Unknown mode')
