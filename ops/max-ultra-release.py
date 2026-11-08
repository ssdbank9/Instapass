"""Data-only approved Max/Ultra prices; protected backup, guards, scoped rollback."""
import datetime,json,os,pathlib,subprocess,sys
root=pathlib.Path('/opt/instapass');stage=pathlib.Path('/home/ubuntu/instapass-max-ultra-20261108');cli='wordpress@sha256:661db880e953da051abcac6c0a769767b995a82cd52101c003ca7438dd427d7d';os.umask(0o077)
def run(mode,w):
 return subprocess.check_output(['docker','compose','--env-file',str(root/'.env'),'-f',str(root/'maintenance.yaml'),'run','--rm','--user','0:0','-e','WP_CLI_ALLOW_ROOT=1','-e','IPMU_MODE='+mode,'-e','IPMU_BACKUP='+str(w/'state-before.json'),'-v',str(w)+':'+str(w),'-v',str(stage/'max-ultra-pricing.php')+':/tmp/pricing.php:ro','wpcli','eval-file','/tmp/pricing.php'],text=True)
mode=sys.argv[1]
if mode=='apply':
 w=root/('recovery/max-ultra-pricing-'+datetime.datetime.now(datetime.timezone.utc).strftime('%Y%m%dT%H%M%SZ'));w.mkdir(mode=0o700);(stage/'release-state.json').write_text(json.dumps({'work':str(w)}))
 subprocess.run(['docker','run','--rm','--network','none','--entrypoint','php','-v',str(stage/'max-ultra-pricing.php')+':/tmp/pricing.php:ro',cli,'-l','/tmp/pricing.php'],check=True)
 with (w/'database-before.sql').open('wb') as out:subprocess.run(['docker','exec','instapass-db-1','sh','-c','exec mariadb-dump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction --quick --databases "$MYSQL_DATABASE"'],stdout=out,check=True)
 try:
  result=run('apply',w);(w/'native-result.json').write_text(result);print(result,flush=True)
  for url in ['https://instapass.online/shop/','https://wasooli.duckdns.org/healthz']:subprocess.run(['curl','-fsS','--max-time','25','-o','/dev/null',url],check=True)
  print('LIVE_RELEASE '+str(w))
 except BaseException:
  if (w/'state-before.json').exists():print(run('rollback',w),flush=True)
  raise
elif mode=='verify':
 w=pathlib.Path(json.loads((stage/'release-state.json').read_text())['work']);print(run('verify',w))
else:raise RuntimeError('Unknown mode')
