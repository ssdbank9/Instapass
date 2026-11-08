import datetime,hashlib,json,os,pathlib,shutil,subprocess
root=pathlib.Path('/opt/instapass');stage=pathlib.Path('/home/ubuntu/instapass-ultra-savings-20261108');target=root/'wordpress/wp-content/plugins/instapass-control-center/includes/offer-manager.php';source=stage/'offer-manager.php';cli='wordpress@sha256:661db880e953da051abcac6c0a769767b995a82cd52101c003ca7438dd427d7d';os.umask(0o077)
m=json.loads((stage/'manifest.json').read_text());assert hashlib.sha256(target.read_bytes()).hexdigest()==m['before'],'Live source drift';assert hashlib.sha256(source.read_bytes()).hexdigest()==m['after'],'Candidate hash mismatch'
def wp(file):
 return subprocess.check_output(['docker','compose','--env-file',str(root/'.env'),'-f',str(root/'maintenance.yaml'),'run','--rm','--user','0:0','-e','WP_CLI_ALLOW_ROOT=1','-v',str(stage/file)+':/tmp/check.php:ro','wpcli','eval-file','/tmp/check.php'],text=True)
subprocess.run(['docker','run','--rm','--network','none','--entrypoint','php','-v',str(source)+':/tmp/source.php:ro',cli,'-l','/tmp/source.php'],check=True)
w=root/('recovery/ultra-savings-'+datetime.datetime.now(datetime.timezone.utc).strftime('%Y%m%dT%H%M%SZ'));w.mkdir(mode=0o700);shutil.copyfile(target,w/'source-before.php');(w/'source-before.php').chmod(0o600);before=wp('tile-copy-audit.php');(w/'audit-before.json').write_text(before)
try:
 temp=target.with_suffix('.php.next');shutil.copyfile(source,temp);temp.chmod(0o644);os.replace(temp,target)
 result=wp('ultra-savings-native.php');after=wp('tile-copy-audit.php');(w/'audit-after.json').write_text(after);assert json.loads(before)==json.loads(after),'Commerce hashes changed';assert hashlib.sha256(target.read_bytes()).hexdigest()==m['after'];(w/'native.json').write_text(result);print(result);print('LIVE_ULTRA_SAVINGS '+str(w));print('Product/order/options hashes unchanged')
except BaseException:
 shutil.copyfile(w/'source-before.php',target);target.chmod(0o644);raise
