from pathlib import Path
import hashlib,json
r=Path(__file__).resolve().parents[1];m={'before':{},'after':{}};rel='mu-plugins/instapass-launch-catalog.php';p=r/rel;raw=p.read_bytes();s=raw.decode('utf-8');m['before'][rel]=hashlib.sha256(raw).hexdigest()
assert 'ipl_credit_expiry_label' not in s
for a,b in [
 ('Version: 1.2.0','Version: 1.3.0'),
 ("'value'=>'Monthly'","'value'=>apply_filters('ipl_credit_order_expiry','Monthly',$o['group'])"),
 ("$item->add_meta_data('Credit expiry','Monthly',true)","$item->add_meta_data('Credit expiry',apply_filters('ipl_credit_order_expiry','Monthly',$o['group']),true)"),
 ('<p class="ipl-credit-expiry">Credits expire monthly</p>', '<p class="ipl-credit-expiry">\'.esc_html(apply_filters(\'ipl_credit_expiry_label\',\'Credits expire monthly\',$g)).\'</p>'),
 ("$choice.' · Credits expire monthly'", "$choice.' · '.apply_filters('ipl_credit_expiry_label','Credits expire monthly',$g)"),
 ("return $h.'</span>';", "$h.=apply_filters('ipl_credit_price_note','',$p,$face);return $h.'</span>';"),
 ("else $html.='<div class=\"ipl-price ipl-pending\">Pricing coming soon</div><p class=\"ipl-term\">'.esc_html($term).' · Price to be announced</p><button class=\"ipl-buy\" disabled>Coming soon</button>';", "else $html.=apply_filters('ipl_pending_quote_html','<div class=\"ipl-price ipl-pending\">Pricing coming soon</div><p class=\"ipl-term\">'.esc_html($term).' · Price to be announced</p><button class=\"ipl-buy\" disabled>Coming soon</button>',$p,$g,$m);")]:
 assert a in s,a;s=s.replace(a,b)
p.write_text(s,encoding='utf-8',newline='\n');m['after'][rel]=hashlib.sha256(p.read_bytes()).hexdigest();rel='mu-plugins/instapass-credit-cycle.php';m['after'][rel]=hashlib.sha256((r/rel).read_bytes()).hexdigest();(r/'outputs/credit-cycle-source-manifest.json').write_text(json.dumps(m,indent=2))
# Derive the isolated/recovery runner, with a new stage and no dependency on the previous job.
s=(r/'ops/cursor-credits-release.py').read_text(encoding='utf-8')
for a,b in [('instapass-cursor-credits-20261108','instapass-credit-cycle-20261108'),('cursor-credits-source-manifest','credit-cycle-source-manifest'),('cursor-credits-data.php','credit-cycle-data.php'),('cursor-credits-check.php','credit-cycle-check.php'),('recovery/cursor-credits-test-','recovery/credit-cycle-test-'),('recovery/cursor-credits-release-','recovery/credit-cycle-release-'),("'ipcc-test-'","'ipce-test-'"),("'ipcc-db-'","'ipce-db-'"),("'ipcc-web-'","'ipce-web-'"),("startswith('ipcc-')","startswith('ipce-')"),("startswith('ipcc-test-')","startswith('ipce-test-')")]:s=s.replace(a,b)
s=s.replace("def baseline():\n", "def baseline():\n for rel in manifest['after']:\n  if rel not in manifest['before']:assert not (ROOT/'wordpress/wp-content'/rel).exists(),'Unexpected new source '+rel\n")
s=s.replace("['mu-plugins/instapass-launch-catalog.php','ops/credit-cycle-data.php'", "['mu-plugins/instapass-credit-cycle.php','mu-plugins/instapass-launch-catalog.php','ops/credit-cycle-data.php'")
s=s.replace("for rel in manifest['after']:p=ROOT/'wordpress/wp-content'/rel;", "for rel in sorted(manifest['after'],key=lambda x:x.endswith('instapass-launch-catalog.php')):p=ROOT/'wordpress/wp-content'/rel;")
s=s.replace("for rel in manifest['before']:shutil.copyfile(w/rel,ROOT/'wordpress/wp-content'/rel)", "for rel in manifest['before']:shutil.copyfile(w/rel,ROOT/'wordpress/wp-content'/rel)\n  for rel in manifest['after']:\n   if rel not in manifest['before']:(ROOT/'wordpress/wp-content'/rel).unlink(missing_ok=True)")
s=s.replace("(mu/'test-fixture.php').chmod(0o644)", "(mu/'test-fixture.php').write_text((mu/'test-fixture.php').read_text()+\"if(isset($_COOKIE['ip_ce_preview_at']))$GLOBALS['ip_ce_test_now']=(int)$_COOKIE['ip_ce_preview_at'];\");(mu/'test-fixture.php').chmod(0o644)")
(r/'ops/credit-cycle-release.py').write_text(s,encoding='utf-8',newline='\n');print('Prepared two runtime sources and a new isolated runner')
