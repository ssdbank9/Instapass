from pathlib import Path
import json,hashlib
r=Path(__file__).resolve().parents[1];p=r/'mu-plugins/instapass-launch-catalog.css';m=r/'outputs/four-currency-source-manifest.json';data=json.loads(m.read_text());rel=str(p.relative_to(r)).replace('\\','/')
assert rel not in data['before'];data['before'][rel]=hashlib.sha256(p.read_bytes()).hexdigest()
style='''
/* Keep savings and prepaid prices readable inside the original narrow tiles. */
.ipl-card .ipl-price{letter-spacing:normal;font-size:16px;line-height:1.5}
.ipl-card .ipl-pending{font-size:18px;font-weight:750}
.ipl-card .ip-offer-savings{font-size:12px;letter-spacing:normal;line-height:1.6}
.ipl-card .ip-offer-saving-total small,.ipl-card .ip-official-reference,.ipl-card .ip-offer-tax{font-size:11px;line-height:1.6;letter-spacing:normal}
.ipl-card .ip-monthly-primary{font-size:30px;line-height:1.2;letter-spacing:-.025em;margin-top:14px}
.ipl-card .ip-monthly-unit{display:block;font-size:12px;font-weight:500;line-height:1.5;letter-spacing:normal;margin-top:3px}
.ipl-card .ip-term-upfront{font-size:12px;letter-spacing:normal}
.ipl-card .ip-term-bundle{font-size:13px;letter-spacing:normal}
.ipl-card .ip-bundle-condition{font-size:11px;letter-spacing:normal;line-height:1.6}
'''
p.write_text(p.read_text()+style,encoding='utf-8',newline='\n');data['after'][rel]=hashlib.sha256(p.read_bytes()).hexdigest();m.write_text(json.dumps(data,indent=2));print('Added one scoped tile typography file')
