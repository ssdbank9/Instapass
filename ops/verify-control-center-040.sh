#!/usr/bin/env bash
set -euo pipefail
work=$(cat /home/ubuntu/control-center-040-deployment-state)
[[ "$work" =~ ^/opt/instapass/recovery/control-center-040-[0-9]{8}T[0-9]{6}Z$ ]] || exit 2
docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v /home/ubuntu/audit-bulk-live.php:/tmp/audit.php:ro wpcli eval-file /tmp/audit.php > "$work/final.json"
python3 - "$work" <<'PY'
import json,sys,hashlib
from pathlib import Path
p=Path(sys.argv[1]);before=json.loads((p/'before.json').read_text());after=json.loads((p/'final.json').read_text());browser=json.loads(Path('/home/ubuntu/control-center-040-live-browser-verification.json').read_text())
assert after['control_version']=='0.4.0'
assert browser['authenticated_admin'] and browser['pass_count']==13 and not browser['failures']
for field in ['base_currency','pkr_rate','product_count','usd_priced','gbp_priced','order_count','catalog_fingerprint','order_fingerprint']:
    assert before[field]==after[field],field+' changed'
expected={'includes/bulk-pricing.php':'cca5633dad5aece908c3a4195428646e7330eac98a14d29c179b79ff86da4b57','assets/bulk-pricing.js':'43b6ff99f63b45f39208716aaca8ac4e32b61b54160102c85d78da3ae48dd87f','instapass-control-center.php':'56365161b9c309eba4c0c2c2bc051d66126099b9837526226e636da26753b907'}
for file,h in expected.items(): assert hashlib.sha256((Path('/opt/instapass/wordpress/wp-content/plugins/instapass-control-center')/file).read_bytes()).hexdigest()==h,file+' source mismatch'
result={'state':'live_verified','ui_asset_build':'0.4.0.1','source_sha256':expected,'before':before,'after':after,'browser_checks':browser['pass_count'],'data_fingerprints_unchanged':True,'full_backup':'/opt/instapass/recovery/pre-commerce-20261018T085637Z','backup_restore_tables':56,'rollback_archive':str(p/'control-center-before-040.tar.gz'),'rollback_sha256':'dd7a5daefe93cdee3f626012fca1d796e4380ab002c40f2c18b7b61c68ac00ef'}
(p/'verified-result.json').write_text(json.dumps(result,indent=2))
print(json.dumps(result,indent=2))
PY
cp "$work/verified-result.json" /home/ubuntu/control-center-040-verified-result.json
chmod 644 /home/ubuntu/control-center-040-verified-result.json
