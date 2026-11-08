import datetime,hashlib,json,os,pathlib,shutil,subprocess,sys,time,secrets
ROOT=pathlib.Path('/opt/instapass');STAGE=pathlib.Path('/home/ubuntu/instapass-owner-controls-20261108');CLI='wordpress@sha256:661db880e953da051abcac6c0a769767b995a82cd52101c003ca7438dd427d7d';WEB='wordpress@sha256:d42f63cb89ad0eab272c4b393164e63830d0df270009907919f7dcce52b1a20b'
manifest=json.loads((STAGE/'outputs/owner-controls-source-manifest.json').read_text());os.umask(0o077)
def run(*a,**k):return subprocess.check_output(a,text=True,**k)
def baseline():
 for rel in manifest['after']:
  if rel not in manifest['before']:assert not (ROOT/'wordpress/wp-content'/rel).exists(),'Unexpected new source '+rel
 for rel,h in manifest['before'].items():assert hashlib.sha256((ROOT/'wordpress/wp-content'/rel).read_bytes()).hexdigest()==h,'Live source drift '+rel
def mounts(w):return ['-v',str(STAGE/'plugins/instapass-control-center/includes/offer-manager.php')+':/var/www/html/wp-content/plugins/instapass-control-center/includes/offer-manager.php:ro','-v',str(ROOT/'wordpress')+':/var/www/html:ro','-v',str(w/'mu')+':/tmp/mu:ro','-v',str(ROOT/'php-security.ini')+':/usr/local/etc/php/conf.d/zz-security.ini:ro','-v',str(w)+':'+str(w)]
def testcli(state,mode,file='owner-controls-data.php'):
 w=pathlib.Path(state['work']);return run('docker','run','--rm','--user','0:0','-e','WP_CLI_ALLOW_ROOT=1','--network',state['net'],'--env-file',str(w/'cli.env'),'-e','IPCC_MODE='+mode,'-e','IPCC_BACKUP='+str(w/'state-before.json'),*mounts(w),'-v',str(STAGE/'ops'/file)+':/tmp/test.php:ro',CLI,'eval-file','/tmp/test.php')
def livecli(mode,w):return run('docker','compose','--env-file',str(ROOT/'.env'),'-f',str(ROOT/'maintenance.yaml'),'run','--rm','--user','0:0','-e','WP_CLI_ALLOW_ROOT=1','-e','IPCC_ANNUAL_ONLY='+('yes' if '--annual-only' in sys.argv else 'no'),'-e','IPCC_MODE='+mode,'-e','IPCC_BACKUP='+str(w/'state-before.json'),'-v',str(w)+':'+str(w),'-v',str(STAGE/'ops/owner-controls-data.php')+':/tmp/test.php:ro','wpcli','eval-file','/tmp/test.php')
mode=sys.argv[1]
if mode=='prepare':
 baseline();stamp=datetime.datetime.now(datetime.timezone.utc).strftime('%Y%m%dT%H%M%SZ');w=ROOT/('recovery/owner-controls-test-'+stamp);w.mkdir(mode=0o700);state={'work':str(w),'net':'ipom-test-'+stamp,'db':'ipom-db-'+stamp,'web':'ipom-web-'+stamp};(STAGE/'test-state.json').write_text(json.dumps(state))
 mu=w/'mu';shutil.copytree(ROOT/'wordpress/wp-content/mu-plugins',mu)
 for rel in manifest['after']:
  if rel.startswith('mu-plugins/'):shutil.copyfile(STAGE/rel,mu/pathlib.Path(rel).name)
 for p in [mu,*mu.rglob('*')]:p.chmod(0o755 if p.is_dir() else 0o644)
 (mu/'test-fixture.php').write_text('''<?php if(getenv('INSTAPASS_ISOLATED_TEST')!=='yes')return;add_filter('option_home',fn()=>'http://127.0.0.1:19101');add_filter('option_siteurl',fn()=>'http://127.0.0.1:19101');add_filter('pre_wp_mail',fn()=>true,999);add_filter('pre_http_request',fn()=>new WP_Error('isolated','External HTTP disabled'),999);add_filter('content_url',fn($u)=>preg_replace('~^https?://instapass.online~','http://127.0.0.1:19101',$u));''');(mu/'test-fixture.php').write_text((mu/'test-fixture.php').read_text()+"if(isset($_COOKIE['ip_ce_preview_at']))$GLOBALS['ip_ce_test_now']=(int)$_COOKIE['ip_ce_preview_at'];");(mu/'test-fixture.php').write_text((mu/'test-fixture.php').read_text()+"add_action('template_redirect',function(){if(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)!=='/__owner_test_login')return;$uid=(int)get_users(['role'=>'administrator','number'=>1,'fields'=>'ID'])[0];wp_set_auth_cookie($uid,true,false);wp_safe_redirect(admin_url('admin.php?page=instapass-product-manager'));exit;},0);");(mu/'test-fixture.php').chmod(0o644)
 password=secrets.token_hex(24);(w/'db.env').write_text('MYSQL_ROOT_PASSWORD='+password+'\n');dbname=run('docker','exec','instapass-db-1','printenv','MYSQL_DATABASE').strip();(w/'cli.env').write_text('WORDPRESS_DB_HOST='+state['db']+':3306\nWORDPRESS_DB_USER=root\nWORDPRESS_DB_PASSWORD='+password+'\nWORDPRESS_DB_NAME='+dbname+'\nINSTAPASS_ISOLATED_TEST=yes\nWORDPRESS_CONFIG_EXTRA=define("DISABLE_WP_CRON",true);define("WPMU_PLUGIN_DIR","/tmp/mu");\n')
 run('docker','network','create','--internal',state['net']);image=json.loads(run('docker','inspect','instapass-db-1'))[0]['Image'];run('docker','run','-d','--name',state['db'],'--network',state['net'],'--memory','768m','--tmpfs','/var/lib/mysql:rw,size=512m','--env-file',str(w/'db.env'),image)
 for _ in range(60):
  try:run('docker','exec',state['db'],'sh','-c','mariadb -uroot -p"$MYSQL_ROOT_PASSWORD" -N -e "SELECT 1"',stderr=subprocess.DEVNULL);break
  except subprocess.CalledProcessError:time.sleep(1)
 else:raise RuntimeError('Clone DB not ready')
 dump=subprocess.Popen(['docker','exec','instapass-db-1','sh','-c','exec mariadb-dump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction --quick --databases "$MYSQL_DATABASE"'],stdout=subprocess.PIPE);subprocess.run(['docker','exec','-i',state['db'],'sh','-c','exec mariadb -uroot -p"$MYSQL_ROOT_PASSWORD"'],stdin=dump.stdout,check=True);dump.stdout.close();assert dump.wait()==0
 for rel in ['mu-plugins/instapass-owner-product-manager.php','plugins/instapass-control-center/includes/offer-manager.php','mu-plugins/instapass-credit-cycle.php','mu-plugins/instapass-launch-catalog.php','ops/owner-controls-data.php','ops/owner-controls-check.php']:run('docker','run','--rm','--network','none','--entrypoint','php','-v',str(STAGE/rel)+':/tmp/source.php:ro',CLI,'-l','/tmp/source.php')
 print('Source hash guards and PHP lint pass',flush=True)
 for action,file in [('apply','owner-controls-data.php'),('verify','owner-controls-check.php')]:
  result=testcli(state,action,file);(w/(file+'.result')).write_text(result);print(result,flush=True)
 conf=w/'assets.conf';conf.write_text('Alias /wp-content/mu-plugins/ /tmp/mu/\n<Directory /tmp/mu>\nRequire all granted\n</Directory>\n');conf.chmod(0o644);run('docker','run','-d','--name',state['web'],'--network',state['net'],'--memory','768m','--env-file',str(w/'cli.env'),*mounts(w),'-v',str(conf)+':/etc/apache2/conf-enabled/zz-assets.conf:ro',WEB)
 state['ip']=list(json.loads(run('docker','inspect',state['web']))[0]['NetworkSettings']['Networks'].values())[0]['IPAddress'];(STAGE/'test-state.json').write_text(json.dumps(state));print('ISOLATED_READY '+json.dumps(state),flush=True)
elif mode=='resume':
 state=json.loads((STAGE/'test-state.json').read_text());w=pathlib.Path(state['work']);mu=w/'mu'
 for rel in manifest['after']:
  if rel.startswith('mu-plugins/'):p=mu/pathlib.Path(rel).name;shutil.copyfile(STAGE/rel,p);p.chmod(0o644)
 result=testcli(state,'verify','owner-controls-check.php');(w/'owner-controls-check.php.result').write_text(result);print(result,flush=True)
 conf=w/'assets.conf';conf.write_text('Alias /wp-content/mu-plugins/ /tmp/mu/\n<Directory /tmp/mu>\nRequire all granted\n</Directory>\n');conf.chmod(0o644)
 run('docker','run','-d','--name',state['web'],'--network',state['net'],'--memory','768m','--env-file',str(w/'cli.env'),*mounts(w),'-v',str(conf)+':/etc/apache2/conf-enabled/zz-assets.conf:ro',WEB)
 state['ip']=list(json.loads(run('docker','inspect',state['web']))[0]['NetworkSettings']['Networks'].values())[0]['IPAddress'];(STAGE/'test-state.json').write_text(json.dumps(state));print('ISOLATED_READY '+json.dumps(state),flush=True)
elif mode=='refresh':
 s=json.loads((STAGE/'test-state.json').read_text());w=pathlib.Path(s['work'])
 for rel in manifest['after']:
  if rel.startswith('mu-plugins/'):p=w/'mu'/pathlib.Path(rel).name;shutil.copyfile(STAGE/rel,p);p.chmod(0o644)
 print(testcli(s,'verify','owner-controls-check.php'))
elif mode=='reset':
 s=json.loads((STAGE/'test-state.json').read_text());print(testcli(s,'reset','owner-controls-check.php'))
elif mode=='check':
 s=json.loads((STAGE/'test-state.json').read_text());result=testcli(s,'verify','owner-controls-check.php');(pathlib.Path(s['work'])/'owner-controls-check.php.result').write_text(result);print(result)
elif mode=='deploy':
 assert '--browser-passed' in sys.argv;baseline();stamp=datetime.datetime.now(datetime.timezone.utc).strftime('%Y%m%dT%H%M%SZ');w=ROOT/('recovery/owner-controls-release-'+stamp);w.mkdir(mode=0o700);(STAGE/'release-state.json').write_text(json.dumps({'work':str(w)}))
 with (w/'database-before.sql').open('wb') as out:subprocess.run(['docker','exec','instapass-db-1','sh','-c','exec mariadb-dump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction --quick --databases "$MYSQL_DATABASE"'],stdout=out,check=True)
 before=livecli('audit',w);(w/'audit-before.json').write_text(before)
 for rel in manifest['before']:p=w/rel;p.parent.mkdir(parents=True,exist_ok=True);shutil.copyfile(ROOT/'wordpress/wp-content'/rel,p)
 try:
  for rel in sorted(manifest['after'],key=lambda x:0 if x.endswith('instapass-owner-product-manager.php') else (2 if x.endswith('instapass-launch-catalog.php') else 1)):p=ROOT/'wordpress/wp-content'/rel;temp=p.with_suffix(p.suffix+'.next');shutil.copyfile(STAGE/rel,temp);temp.chmod(0o644);os.replace(temp,p)
  result=livecli('apply',w);(w/'native-result.json').write_text(result);print(result,flush=True);after=livecli('audit',w);(w/'audit-after.json').write_text(after);assert json.loads(before)==json.loads(after),'Product/order/options changed'
  for url in ['https://instapass.online/','https://instapass.online/shop/','https://wasooli.duckdns.org/healthz']:run('curl','-fsS','--max-time','25','-o','/dev/null',url)
  assert all(hashlib.sha256((ROOT/'wordpress/wp-content'/r).read_bytes()).hexdigest()==h for r,h in manifest['after'].items());print('LIVE_RELEASE '+str(w),flush=True)
 except BaseException:
  if (w/'state-before.json').exists():print(livecli('rollback',w),flush=True)
  for rel in manifest['before']:shutil.copyfile(w/rel,ROOT/'wordpress/wp-content'/rel)
  for rel in manifest['after']:
   if rel not in manifest['before']:(ROOT/'wordpress/wp-content'/rel).unlink(missing_ok=True)
  raise
elif mode=='cleanup':
 s=json.loads((STAGE/'test-state.json').read_text())
 for k in ['web','db']:assert s[k].startswith('ipom-');run('docker','rm','-f',s[k])
 assert s['net'].startswith('ipom-test-');run('docker','network','rm',s['net']);print('Own test containers/network removed; protected recovery retained')
else:raise RuntimeError('Unknown mode')
