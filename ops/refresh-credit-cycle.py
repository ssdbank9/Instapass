from pathlib import Path
import json,hashlib
r=Path(__file__).resolve().parents[1];p=r/'mu-plugins/instapass-credit-cycle.php';s=p.read_text(encoding='utf-8');s=s.replace("(?:[1-9][0-9]?|100)(?:\\.[0-9]{1,2})?", "(?:0|[1-9][0-9]?|100)(?:\\.[0-9]{1,2})?").replace("||(float)$raw>100)","||(float)$raw<=0||(float)$raw>100)")
for a,b in [('from1to31','from 1 to 31'),('most100%','most 100%'),('at00:00','at 00:00'),('is40%','is 40%'),('their40%','their 40%'),('The5%','The 5%')]:s=s.replace(a,b)
s=s.replace("return '<span class=\"ip-term-upfront\">Current rate:","return '<span class=\"ip-term-upfront ip-credit-cycle-note\" data-percent=\"'.esc_attr($s['percent']).'\" data-expiry=\"'.esc_attr($s['expiry']).'\">Current rate:")
p.write_text(s,encoding='utf-8',newline='\n');m=r/'outputs/credit-cycle-source-manifest.json';d=json.loads(m.read_text());d['after']={rel:hashlib.sha256((r/rel).read_bytes()).hexdigest() for rel in d['after']};m.write_text(json.dumps(d,indent=2))
p=r/'ops/credit-cycle-release.py';s=p.read_text(encoding='utf-8');anchor="elif mode=='check':\n";assert anchor in s
s=s.replace(anchor,"""elif mode=='resume':
 state=json.loads((STAGE/'test-state.json').read_text());w=pathlib.Path(state['work']);mu=w/'mu'
 for rel in manifest['after']:p=mu/pathlib.Path(rel).name;shutil.copyfile(STAGE/rel,p);p.chmod(0o644)
 result=testcli(state,'verify','credit-cycle-check.php');(w/'credit-cycle-check.php.result').write_text(result);print(result,flush=True)
 conf=w/'assets.conf';conf.write_text('Alias /wp-content/mu-plugins/ /tmp/mu/\\n<Directory /tmp/mu>\\nRequire all granted\\n</Directory>\\n');conf.chmod(0o644)
 run('docker','run','-d','--name',state['web'],'--network',state['net'],'--memory','768m','--env-file',str(w/'cli.env'),*mounts(w),'-v',str(conf)+':/etc/apache2/conf-enabled/zz-assets.conf:ro',WEB)
 state['ip']=list(json.loads(run('docker','inspect',state['web']))[0]['NetworkSettings']['Networks'].values())[0]['IPAddress'];(STAGE/'test-state.json').write_text(json.dumps(state));print('ISOLATED_READY '+json.dumps(state),flush=True)
"""+anchor)
p.write_text(s,encoding='utf-8',newline='\n');print('Updated review fixes and resume runner')
