import datetime,json,os,pathlib,shutil,subprocess,sys,time,secrets
ROOT=pathlib.Path('/opt/instapass'); STAGE=pathlib.Path('/home/ubuntu/instapass-grouped-20261106')
CLI='wordpress@sha256:661db880e953da051abcac6c0a769767b995a82cd52101c003ca7438dd427d7d'
WEB='wordpress@sha256:d42f63cb89ad0eab272c4b393164e63830d0df270009907919f7dcce52b1a20b'
FILES=['instapass-launch-catalog.php','instapass-launch-catalog.css','instapass-launch-catalog.js']
def run(*args,**kwargs):return subprocess.check_output(args,text=True,**kwargs)
def dc(*args):return ['docker','compose','--env-file',str(ROOT/'.env'),'-f',str(ROOT/'maintenance.yaml'),*args]
def livecli(script,work):return run(*dc('run','--rm','--user','0:0','-e','WP_CLI_ALLOW_ROOT=1','-e','IPL_APPLY=yes','-e','IPL_BACKUP='+str(work/'products-before.json'),'-v',str(work)+':'+str(work),'-v',str(STAGE/script)+':/tmp/check.php:ro','wpcli','eval-file','/tmp/check.php'))
os.umask(0o077)
mode=sys.argv[1]
credit_mode='--credits' in sys.argv or mode=='deploy-credits'
migration='grouped-catalog-credits.php' if credit_mode else 'grouped-catalog-migrate.php'
if mode=='prepare':
 stamp=datetime.datetime.now(datetime.timezone.utc).strftime('%Y%m%dT%H%M%SZ');work=ROOT/('recovery/grouped-test-'+stamp);work.mkdir(mode=0o700)
 net='instapass-grouped-test-'+stamp;db='instapass-grouped-db-'+stamp;web='instapass-grouped-web-'+stamp
 state={'work':str(work),'net':net,'db':db,'web':web};(STAGE/'test-state.json').write_text(json.dumps(state))
 password=secrets.token_hex(24);(work/'db.env').write_text('MYSQL_ROOT_PASSWORD='+password+'\n')
 dbname=run('docker','exec','instapass-db-1','printenv','MYSQL_DATABASE').strip()
 (work/'cli.env').write_text('WORDPRESS_DB_HOST='+db+':3306\nWORDPRESS_DB_USER=root\nWORDPRESS_DB_PASSWORD='+password+'\nWORDPRESS_DB_NAME='+dbname+'\nINSTAPASS_ISOLATED_TEST=yes\nWORDPRESS_CONFIG_EXTRA=define("DISABLE_WP_CRON",true);define("WPMU_PLUGIN_DIR","/tmp/mu");\n')
 run('docker','network','create','--internal',net)
 image=json.loads(run('docker','inspect','instapass-db-1'))[0]['Image']
 run('docker','run','-d','--name',db,'--network',net,'--memory','768m','--tmpfs','/var/lib/mysql:rw,size=512m','--env-file',str(work/'db.env'),image)
 for _ in range(90):
  try:run('docker','exec',db,'sh','-c','mariadb -uroot -p"$MYSQL_ROOT_PASSWORD" -N -e "SELECT 1"',stderr=subprocess.DEVNULL);break
  except subprocess.CalledProcessError:time.sleep(1)
 else:raise RuntimeError('Clone database not ready')
 dump=subprocess.Popen(['docker','exec','instapass-db-1','sh','-c','exec mariadb-dump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction --quick --databases "$MYSQL_DATABASE"'],stdout=subprocess.PIPE)
 load=subprocess.run(['docker','exec','-i',db,'sh','-c','exec mariadb -uroot -p"$MYSQL_ROOT_PASSWORD"'],stdin=dump.stdout,check=True);dump.stdout.close();assert dump.wait()==0
 mu=work/'mu';mu.mkdir(mode=0o755);mu.chmod(0o755)
 for f in (ROOT/'wordpress/wp-content/mu-plugins').iterdir():
  if f.is_file():shutil.copyfile(f,mu/f.name);(mu/f.name).chmod(0o644)
 for f in FILES:shutil.copyfile(STAGE/f,mu/f);(mu/f).chmod(0o644)
 (mu/'test-fixture.php').write_text('<?php if(getenv("INSTAPASS_ISOLATED_TEST")!=="yes")return; add_filter("option_home",fn()=>"http://127.0.0.1:19101");add_filter("option_siteurl",fn()=>"http://127.0.0.1:19101");add_filter("pre_wp_mail",fn()=>true,999);add_filter("pre_http_request",fn()=>new WP_Error("isolated","External HTTP disabled"),999);');(mu/'test-fixture.php').chmod(0o644)
 mounts=['-v',str(ROOT/'wordpress')+':/var/www/html:ro','-v',str(mu)+':/tmp/mu:ro','-v',str(ROOT/'php-security.ini')+':/usr/local/etc/php/conf.d/zz-security.ini:ro','-v',str(work)+':'+str(work)]
 for file in ['instapass-launch-catalog.php',migration,'grouped-catalog-check.php','grouped-catalog-rollback.php']:
  print(run('docker','run','--rm','--network','none','--entrypoint','php','-v',str(STAGE/file)+':/tmp/code.php:ro',CLI,'-l','/tmp/code.php'),flush=True)
 for file in [migration,'grouped-catalog-check.php']:
  result=run('docker','run','--rm','--user','0:0','-e','WP_CLI_ALLOW_ROOT=1','--network',net,'--env-file',str(work/'cli.env'),'-e','IPL_APPLY=yes','-e','IPL_BACKUP='+str(work/'products-before.json'),*mounts,'-v',str(STAGE/file)+':/tmp/test.php:ro',CLI,'eval-file','/tmp/test.php');(work/(file+'.result')).write_text(result);print(result,flush=True)
 run('docker','run','-d','--name',web,'--network',net,'--memory','768m','--env-file',str(work/'cli.env'),*mounts,WEB)
 ip=list(json.loads(run('docker','inspect',web))[0]['NetworkSettings']['Networks'].values())[0]['IPAddress'];state['ip']=ip;(STAGE/'test-state.json').write_text(json.dumps(state));(STAGE/'test-ip').write_text(ip)
 print('ISOLATED_READY '+json.dumps(state),flush=True)
 subprocess.run([sys.executable,__file__,'refresh'],check=True)
elif mode=='refresh':
 state=json.loads((STAGE/'test-state.json').read_text());work=pathlib.Path(state['work']);mu=work/'mu'
 assert state['web'].startswith('instapass-grouped-web-')
 for file in FILES:shutil.copyfile(STAGE/file,mu/file);(mu/file).chmod(0o644)
 fixture=mu/'test-fixture.php';text=fixture.read_text()
 if 'content_url' not in text:text+='add_filter("content_url",function($url){return strpos($url,"instapass-launch-catalog")!==false?preg_replace("~^https?://instapass.online~","http://127.0.0.1:19101",$url):$url;});'
 text=text.replace('str_replace("https://instapass.online","http://127.0.0.1:19101",$url)','preg_replace("~^https?://instapass.online~","http://127.0.0.1:19101",$url)');fixture.write_text(text);fixture.chmod(0o644)
 conf=work/'assets.conf';conf.write_text('Alias /wp-content/mu-plugins/ /tmp/mu/\n<Directory /tmp/mu>\nRequire all granted\n</Directory>\n');conf.chmod(0o644)
 run('docker','rm','-f',state['web'])
 run('docker','run','-d','--name',state['web'],'--network',state['net'],'--memory','768m','--env-file',str(work/'cli.env'),'-v',str(ROOT/'wordpress')+':/var/www/html:ro','-v',str(mu)+':/tmp/mu:ro','-v',str(conf)+':/etc/apache2/conf-enabled/zz-grouped-assets.conf:ro','-v',str(ROOT/'php-security.ini')+':/usr/local/etc/php/conf.d/zz-security.ini:ro',WEB)
 ip=list(json.loads(run('docker','inspect',state['web']))[0]['NetworkSettings']['Networks'].values())[0]['IPAddress'];print('PREVIEW_REFRESHED '+ip)
elif mode in ['deploy','deploy-credits']:
 assert '--browser-passed' in sys.argv,'Browser acceptance required'
 previous={}
 for file in FILES:
  live=ROOT/'wordpress/wp-content/mu-plugins'/file
  if credit_mode:
   expected=ROOT/'recovery/grouped-catalog-20261106T114755Z'/file
   assert live.read_bytes()==expected.read_bytes(),'Live grouped sources changed; inspect before patching'
   previous[file]=live.read_bytes()
  else:assert not live.exists(),'Already installed; inspect rather than overwrite'
 stamp=datetime.datetime.now(datetime.timezone.utc).strftime('%Y%m%dT%H%M%SZ');work=ROOT/('recovery/grouped-catalog-'+stamp);work.mkdir(mode=0o700)
 with (work/'database-before.sql').open('wb') as out:subprocess.run(['docker','exec','instapass-db-1','sh','-c','exec mariadb-dump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction --quick --databases "$MYSQL_DATABASE"'],stdout=out,check=True)
 for file in [FILES[1],FILES[2],FILES[0]]:
  target=ROOT/'wordpress/wp-content/mu-plugins'/file
  if file in previous:(work/(file+'.before')).write_bytes(previous[file])
  temp=target.with_suffix(target.suffix+'.next');shutil.copyfile(STAGE/file,temp);temp.chmod(0o644);os.replace(temp,target);shutil.copyfile(STAGE/file,work/file)
 try:
  result=livecli(migration,work);(work/'migration-result.json').write_text(result);print(result,flush=True)
  for url in ['https://instapass.online/','https://instapass.online/shop/','https://wasooli.duckdns.org/healthz']:run('curl','-fsS','--max-time','20',url)
  print('LIVE_RELEASE '+str(work),flush=True);(STAGE/'release-state.json').write_text(json.dumps({'work':str(work)}))
 except BaseException:
  if (work/'products-before.json').exists():print(livecli('grouped-catalog-rollback.php',work),flush=True)
  for file in FILES:
   target=ROOT/'wordpress/wp-content/mu-plugins'/file
   if file in previous:target.write_bytes(previous[file])
   else:target.unlink(missing_ok=True)
  print('Grouped module removed after failure; recovery at '+str(work),flush=True);raise
elif mode=='cleanup':
 state=json.loads((STAGE/'test-state.json').read_text())
 for k in ['web','db']:
  assert state[k].startswith('instapass-grouped-');subprocess.run(['docker','rm','-f',state[k]],check=True)
 assert state['net'].startswith('instapass-grouped-test-');run('docker','network','rm',state['net']);print('Exact grouped test containers/network cleaned; protected snapshots retained.')
else:raise RuntimeError('Unknown mode')
