from pathlib import Path
import json,hashlib
r=Path(__file__).resolve().parents[1];p=r/'mu-plugins/instapass-credit-cycle.php';s=p.read_text(encoding='utf-8');s=s.replace("return (string)Instapass_Control_Money::pkr_unit($scaled,instapass_cc_rate());", "$converted=Instapass_Control_Money::pkr_unit($scaled,instapass_cc_rate());return $cents>0&&$converted===0?'':(string)$converted;")
s=s.replace("return number_format($scaled/100,2,'.','');", "return $cents>0&&$scaled===0?'':number_format($scaled/100,2,'.','');");p.write_text(s,encoding='utf-8',newline='\n')
m=r/'outputs/owner-controls-source-manifest.json';d=json.loads(m.read_text());d['after']={rel:hashlib.sha256((r/rel).read_bytes()).hexdigest() for rel in d['after']};m.write_text(json.dumps(d,indent=2))
p=r/'ops/owner-controls-release.py';s=p.read_text(encoding='utf-8');s=s.replace("elif mode=='reset':", "elif mode=='refresh':\n s=json.loads((STAGE/'test-state.json').read_text());w=pathlib.Path(s['work'])\n for rel in manifest['after']:\n  if rel.startswith('mu-plugins/'):p=w/'mu'/pathlib.Path(rel).name;shutil.copyfile(STAGE/rel,p);p.chmod(0o644)\n print(testcli(s,'verify','owner-controls-check.php'))\nelif mode=='reset':")
p.write_text(s,encoding='utf-8',newline='\n');print('Added rounded-positive-price guard and candidate refresh')
