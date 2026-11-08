import pathlib,sys,json,hashlib,datetime,urllib.request,ipaddress,subprocess,os,shutil,tempfile,threading,http.server,http.client,time
BASE=pathlib.Path('/opt/instapass/recovery'); CONFIG=pathlib.Path('/etc/caddy/Caddyfile')
def sha(b):return hashlib.sha256(b).hexdigest()
def build_block(ranges):
 return '\t@instapass_cloudflare remote_ip '+ ' '.join(ranges)+'\n\thandle @instapass_cloudflare {\n\t\treverse_proxy 127.0.0.1:8081 {\n\t\t\theader_up X-Forwarded-For {http.request.header.CF-Connecting-IP}\n\t\t}\n\t}\n\thandle {\n\t\treverse_proxy 127.0.0.1:8081\n\t}\n'
def request(port,source,headers):
 c=http.client.HTTPConnection('127.0.0.1',port,timeout=4,source_address=(source,0));c.request('GET','/',headers=headers);r=c.getresponse();v=json.loads(r.read());c.close();return v
class Echo(http.server.BaseHTTPRequestHandler):
 def do_GET(self):
  b=json.dumps({'forwarded':self.headers.get('X-Forwarded-For')}).encode();self.send_response(200);self.end_headers();self.wfile.write(b)
 def log_message(self,*args):pass
mode=sys.argv[1]
if mode=='prepare':
 before=CONFIG.read_bytes();text=before.decode();assert text.count('instapass.online, www.instapass.online {')==1
 marker='\treverse_proxy 127.0.0.1:8081\n';assert text.count(marker)==1,'Unexpected or already patched config'
 ranges=[]
 if len(sys.argv)>2:
  source=json.loads(pathlib.Path(sys.argv[2]).read_text(encoding='utf-8-sig'));assert source['sources']==['https://www.cloudflare.com/ips-v4','https://www.cloudflare.com/ips-v6'];ranges=source['networks']
  assert sum(ipaddress.ip_network(n).version==4 for n in ranges)==15 and sum(ipaddress.ip_network(n).version==6 for n in ranges)==7
 else:
  for version in (4,6):
   raw=urllib.request.urlopen('https://www.cloudflare.com/ips-v'+str(version),timeout=20).read().decode();nets=raw.split();assert nets
   for n in nets:assert ipaddress.ip_network(n).version==version
   ranges.extend(nets)
 assert len(ranges)==22,'Review changed Cloudflare range inventory'
 candidate=text.replace(marker,build_block(ranges));assert text.split('instapass.online, www.instapass.online {')[0]==candidate.split('instapass.online, www.instapass.online {')[0]
 folder=BASE/('cloudflare-proxy-'+datetime.datetime.now(datetime.timezone.utc).strftime('%Y%m%dT%H%M%SZ'));folder.mkdir(mode=0o700)
 (folder/'Caddyfile.before').write_bytes(before);(folder/'Caddyfile.next').write_text(candidate);(folder/'cloudflare-ranges.json').write_text(json.dumps(ranges,indent=2))
 subprocess.run(['caddy','validate','--config',str(folder/'Caddyfile.next'),'--adapter','caddyfile'],check=True,capture_output=True)
 checks=[]
 with tempfile.TemporaryDirectory(prefix='instapass-proxy-check-') as tmp:
  echo=http.server.ThreadingHTTPServer(('127.0.0.1',19062),Echo);threading.Thread(target=echo.serve_forever,daemon=True).start()
  test=pathlib.Path(tmp)/'Caddyfile';test.write_text('{\n admin off\n auto_https off\n}\nhttp://127.0.0.1:19061 {\n'+build_block(['127.0.0.1/32']).replace('127.0.0.1:8081','127.0.0.1:19062')+'}\n')
  proc=subprocess.Popen(['caddy','run','--config',str(test),'--adapter','caddyfile'],stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
  try:
   for i in range(30):
    try:
     x=request(19061,'127.0.0.1',{'CF-Connecting-IP':'81.2.69.142','X-Forwarded-For':'203.0.113.99'});break
    except OSError:time.sleep(.1)
   assert x['forwarded']=='81.2.69.142',x;checks.append('trusted peer uses CF connecting IP, ignores spoofed XFF')
   x=request(19061,'127.0.0.2',{'CF-Connecting-IP':'81.2.69.142','X-Forwarded-For':'203.0.113.99'});assert x['forwarded']=='127.0.0.2',x;checks.append('untrusted peer cannot spoof forwarded IP')
  finally:
   proc.terminate();proc.wait(timeout=8);echo.shutdown();echo.server_close()
 receipt={'folder':str(folder),'before_sha256':sha(before),'candidate_sha256':sha(candidate.encode()),'ranges':len(ranges),'wasooli_block_unchanged':True,'config_valid':True,'isolated_checks':checks,'applied':False};(folder/'RESULT.json').write_text(json.dumps(receipt,indent=2));print(json.dumps(receipt))
elif mode=='apply':
 folder=pathlib.Path(sys.argv[2]).resolve();assert folder.parent==BASE and folder.name.startswith('cloudflare-proxy-')
 receipt=json.loads((folder/'RESULT.json').read_text());assert receipt['config_valid'] and len(receipt['isolated_checks'])==2
 assert sha(CONFIG.read_bytes())==receipt['before_sha256'],'Live config changed since preflight'
 candidate=(folder/'Caddyfile.next').read_bytes();assert sha(candidate)==receipt['candidate_sha256']
 st=CONFIG.stat();nextfile=CONFIG.with_name('Caddyfile.instapass-next');nextfile.write_bytes(candidate);os.chmod(nextfile,st.st_mode);os.chown(nextfile,st.st_uid,st.st_gid);os.replace(nextfile,CONFIG)
 try:
  subprocess.run(['systemctl','reload','caddy'],check=True,capture_output=True)
  assert subprocess.check_output(['systemctl','is-active','caddy'],text=True).strip()=='active'
  for domain,title in [('instapass.online','Instapass'),('wasooli.duckdns.org','Wasooli')]:
   r=subprocess.run(['curl','-fsS','--max-time','25','--resolve',domain+':443:127.0.0.1','https://'+domain+'/'],check=True,capture_output=True,text=True);assert title.lower() in r.stdout.lower(),domain
  receipt['applied']=True;receipt['site_health']='Instapass and Wasooli passed HTTPS/title checks';receipt['live_sha256']=sha(CONFIG.read_bytes());(folder/'RESULT.json').write_text(json.dumps(receipt,indent=2));print(json.dumps(receipt))
 except Exception:
  CONFIG.write_bytes((folder/'Caddyfile.before').read_bytes());subprocess.run(['systemctl','reload','caddy'],check=True);raise
else:raise RuntimeError('Invalid mode')
