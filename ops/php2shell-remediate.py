"""Scoped Instapass PHP runtime remediation; no exploit traffic or application update."""
import copy, datetime, hashlib, json, os, pathlib, subprocess, tempfile, time
import yaml

ROOT = pathlib.Path('/opt/instapass')
WEB = 'wordpress@sha256:d42f63cb89ad0eab272c4b393164e63830d0df270009907919f7dcce52b1a20b'
CLI = 'wordpress@sha256:661db880e953da051abcac6c0a769767b995a82cd52101c003ca7438dd427d7d'
OLD = 'wordpress@sha256:a85a30d9e7524d4577d6fdd09935b698687521026efbefd4b05d85ed6ae5dec7'
FIXTURE = ROOT/'recovery/guest-commerce-test-20261026T052426Z'
NET = 'instapass-guest-test-20261026T052426Z'
NAME = 'instapass-php2shell-check'
SECURITY = 'session.upload_progress.enabled=0\n'

def run(*args, **kw):
    return subprocess.check_output(args, text=True, **kw)

def compose(file):
    return ['docker','compose','--env-file',str(ROOT/'.env'),'-f',str(ROOT/file)]

def resolved(file):
    return json.loads(run(*(compose(file)+['config','--format','json'])))

def audit():
    return run(*(compose('maintenance.yaml')+['run','--rm','-v','/home/ubuntu/instapass-access-safety-release/audit-guest-commerce.php:/tmp/audit.php:ro','wpcli','eval-file','/tmp/audit.php']))

def image_version(image):
    v=run('docker','run','--rm','--network','none','--entrypoint','php',image,'-r','echo PHP_VERSION;').strip()
    assert v.startswith('8.3.') and tuple(map(int,v.split('.'))) >= (8,3,36), 'Image lacks the required security release'
    return v

os.umask(0o077)
assert image_version(WEB) == image_version(CLI)
before_bytes={f:(ROOT/f).read_bytes() for f in ['compose.yaml','maintenance.yaml']}
before_cfg={f:resolved(f) for f in before_bytes}
assert before_cfg['compose.yaml']['services']['wordpress']['image'] == OLD
assert not (ROOT/'php-security.ini').exists(), 'Existing security config requires review'
assert (FIXTURE/'cli.env').is_file()
assert json.loads(run('docker','network','inspect',NET))[0]['Internal'] is True
assert not run('docker','ps','-aq','--filter','name=^/'+NAME+'$').strip(), 'Test name already exists'

testroot=pathlib.Path(tempfile.mkdtemp(prefix='instapass-php2shell-'))
testroot.chmod(0o755)
mu=testroot/'mu'; mu.mkdir(mode=0o755); mu.chmod(0o755)
for source in (ROOT/'wordpress/wp-content/mu-plugins').iterdir():
    if source.is_file():
        target=mu/source.name; target.write_bytes(source.read_bytes()); target.chmod(0o644)
(mu/'fixture.php').write_bytes((FIXTURE/'mu/fixture.php').read_bytes()); (mu/'fixture.php').chmod(0o644)
ini=testroot/'security.ini'; ini.write_text(SECURITY); ini.chmod(0o644)
probe=testroot/'runtime.php'; probe.write_text('<?php header("Content-Type: application/json"); echo json_encode(["php"=>PHP_VERSION,"sapi"=>PHP_SAPI,"upload_progress"=>ini_get("session.upload_progress.enabled")]);'); probe.chmod(0o644)
conf=testroot/'probe.conf'; conf.write_text('Alias /ip-php-runtime /tmp/ip-php-probe.php\n<Directory /tmp>\nRequire all granted\n</Directory>\n'); conf.chmod(0o644)
mounts=['-v',str(ROOT/'wordpress')+':/var/www/html:ro','-v',str(mu)+':/tmp/mu:ro','-v',str(ini)+':/usr/local/etc/php/conf.d/zz-security.ini:ro']
try:
    run('docker','run','-d','--name',NAME,'--network',NET,'--memory','768m','--env-file',str(FIXTURE/'cli.env'),*mounts,'-v',str(probe)+':/tmp/ip-php-probe.php:ro','-v',str(conf)+':/etc/apache2/conf-enabled/zz-probe.conf:ro',WEB)
    runtime=None
    for _ in range(30):
        try:
            runtime=json.loads(run('docker','exec',NAME,'curl','-fsS','http://127.0.0.1/ip-php-runtime',stderr=subprocess.DEVNULL)); break
        except (subprocess.CalledProcessError,json.JSONDecodeError): time.sleep(1)
    assert runtime and runtime['php']=='8.3.36' and runtime['sapi']=='apache2handler' and runtime['upload_progress']=='0', 'Apache runtime check failed'
    print('ISOLATED_APACHE_RUNTIME '+json.dumps(runtime),flush=True)
    for path,needle in [('/', 'Instapass'),('/shop/', 'Instapass'),('/track-order/','ip-order-number'),('/wp-login.php','user_login'),('/checkout/?add-to-cart=29&ip_buy_now=1','instapass_usage_country')]:
        html=run('docker','exec',NAME,'curl','-fsSL','--max-time','20','-c','/tmp/ip-php2shell-cookies','-b','/tmp/ip-php2shell-cookies','--connect-to','127.0.0.1:19051:127.0.0.1:80','-H','Host: 127.0.0.1:19051','http://127.0.0.1:19051'+path)
        assert needle in html, 'Isolated page check failed: '+path
        print('ISOLATED_HTTP_PASS '+path,flush=True)
    print(run('docker','run','--rm','--network',NET,'--env-file',str(FIXTURE/'cli.env'),*mounts,'-v','/home/ubuntu/instapass-access-safety-release/vpn-country-check.php:/tmp/check.php:ro',CLI,'eval-file','/tmp/check.php'),flush=True)
finally:
    subprocess.run(['docker','rm','-f',NAME],stdout=subprocess.DEVNULL,check=False)
    for file in mu.iterdir(): file.unlink()
    mu.rmdir()
    for file in testroot.iterdir(): file.unlink()
    testroot.rmdir()

# Only the tested service image/config are changed. Database and application mounts stay intact.
assert all((ROOT/f).read_bytes()==b for f,b in before_bytes.items()), 'Configuration changed during testing'
stamp=datetime.datetime.now(datetime.timezone.utc).strftime('%Y%m%dT%H%M%SZ')
recovery=ROOT/('recovery/php2shell-'+stamp); recovery.mkdir(mode=0o700)
for f,b in before_bytes.items(): (recovery/(f+'.before')).write_bytes(b)
before_audit=audit(); (recovery/'commerce-before.json').write_text(before_audit)
with (recovery/'database-before.sql').open('wb') as out:
    subprocess.run(['docker','exec','instapass-db-1','sh','-c','exec mariadb-dump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction --quick --databases "$MYSQL_DATABASE"'],stdout=out,check=True)
security_mount='/opt/instapass/php-security.ini:/usr/local/etc/php/conf.d/zz-security.ini:ro'
baseline_wasooli=run('curl','-fsS','--max-time','20','https://wasooli.duckdns.org/healthz')
try:
    for filename,service,image in [('compose.yaml','wordpress',WEB),('maintenance.yaml','wpcli',CLI)]:
        data=yaml.safe_load(before_bytes[filename]); data['services'][service]['image']=image
        data['services'][service].setdefault('volumes',[]).append(security_mount)
        temp=ROOT/(filename+'.php2shell-next'); temp.write_text(yaml.safe_dump(data,sort_keys=False)); temp.chmod((ROOT/filename).stat().st_mode & 0o777); os.replace(temp,ROOT/filename)
    (ROOT/'php-security.ini').write_text(SECURITY); (ROOT/'php-security.ini').chmod(0o644)
    for filename,service,image in [('compose.yaml','wordpress',WEB),('maintenance.yaml','wpcli',CLI)]:
        now=resolved(filename); original=copy.deepcopy(before_cfg[filename]); original['services'][service]['image']=image
        volumes=now['services'][service]['volumes']
        matches=[v for v in volumes if v.get('source')=='/opt/instapass/php-security.ini']
        assert len(matches)==1
        extra=matches[0]; volumes.remove(extra)
        assert extra['source']=='/opt/instapass/php-security.ini' and extra['read_only'] is True
        assert now==original, 'Unexpected resolved Compose change'
    print('BACKUP '+str(recovery),flush=True)
    run(*(compose('compose.yaml')+['up','-d','--no-deps','--force-recreate','wordpress']))
    for _ in range(30):
        try:
            run('curl','-fsS','--max-time','5','-H','Host: instapass.online','http://127.0.0.1:8081/',stderr=subprocess.DEVNULL); break
        except subprocess.CalledProcessError: time.sleep(1)
    else: raise RuntimeError('Production origin failed health check')
    version=run('docker','exec','instapass-wordpress-1','php','-r','echo PHP_VERSION." upload_progress=".ini_get("session.upload_progress.enabled");')
    assert version=='8.3.36 upload_progress=0',version
    assert run('docker','exec','instapass-wordpress-1','php','-r','require "/var/www/html/wp-includes/version.php"; echo $wp_version;')=='7.1.3'
    after_audit=audit(); (recovery/'commerce-after.json').write_text(after_audit)
    assert json.loads(before_audit)==json.loads(after_audit), 'Commerce audit changed'
    for url in ['https://instapass.online/','https://instapass.online/shop/','https://instapass.online/track-order/','https://wasooli.duckdns.org/healthz']:
        run('curl','-fsS','--max-time','20',url)
        print('LIVE_HTTPS_PASS '+url,flush=True)
    # The old, isolated fixture is not a business service; stop its vulnerable PHP workers.
    run('docker','stop','instapass-guest-web')
    result={'result':'pass','php':version,'web_image':WEB,'cli_image':CLI,'recovery':str(recovery),'commerce_audit':'unchanged','old_isolated_php':'stopped'}
    (recovery/'acceptance.json').write_text(json.dumps(result,indent=2)); print(json.dumps(result),flush=True)
except BaseException:
    for f,b in before_bytes.items(): (ROOT/f).write_bytes(b)
    (ROOT/'php-security.ini').unlink(missing_ok=True)
    subprocess.run(compose('compose.yaml')+['up','-d','--no-deps','--force-recreate','wordpress'],check=True)
    print('ROLLED_BACK_RUNTIME_CONFIG; security remediation remains incomplete',flush=True)
    raise
