from pathlib import Path
import json,hashlib
r=Path(__file__).resolve().parents[1];p=r/'mu-plugins/instapass-owner-product-manager.php';s=p.read_text(encoding='utf-8');a="}echo '</div>';\n}";assert a in s;s=s.replace(a,"}ip_om_existing_form($fp);echo '</div>';\n}",1)
a="if(($in['mode']??'')==='credit')ip_om_credit_save";assert a in s;s=s.replace(a,"if(($in['mode']??'')==='existing')$key=ip_om_register(absint($in['existing_id']??0),$fp);elseif(($in['mode']??'')==='credit')ip_om_credit_save")
p.write_text(s,encoding='utf-8',newline='\n');m=r/'outputs/owner-controls-source-manifest.json';d=json.loads(m.read_text());d['after']={rel:hashlib.sha256((r/rel).read_bytes()).hexdigest() for rel in d['after']};m.write_text(json.dumps(d,indent=2));print('Existing product registration wired into the owner page')
