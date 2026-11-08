#!/usr/bin/env bash
set -euo pipefail
umask 077
source=/home/ubuntu/bulk-040-code
target=/opt/instapass/wordpress/wp-content/plugins/instapass-control-center
grep -q 'Version: 0.3.0' "$target/instapass-control-center.php"
grep -q 'Version: 0.4.0' "$source/instapass-control-center.php"
test "$(sha256sum "$source/includes/bulk-pricing.php" | cut -d ' ' -f1)" = cca5633dad5aece908c3a4195428646e7330eac98a14d29c179b79ff86da4b57
test "$(sha256sum "$source/assets/bulk-pricing.js" | cut -d ' ' -f1)" = 43b6ff99f63b45f39208716aaca8ac4e32b61b54160102c85d78da3ae48dd87f
test "$(sha256sum "$source/instapass-control-center.php" | cut -d ' ' -f1)" = 56365161b9c309eba4c0c2c2bc051d66126099b9837526226e636da26753b907
for file in money.php rate-settings.php currencies.php gbp-editor.php gbp-values.php;do
 cmp "$source/includes/$file" "$target/includes/$file"
done
for file in admin.css currency.css;do cmp "$source/assets/$file" "$target/assets/$file";done
stamp=$(date -u +%Y%m%dT%H%M%SZ)
work=/opt/instapass/recovery/control-center-040-$stamp
mkdir -p "$work"
printf '%s\n' "$work" > /home/ubuntu/control-center-040-deployment-state
docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v /home/ubuntu/audit-bulk-live.php:/tmp/audit.php:ro wpcli eval-file /tmp/audit.php > "$work/before.json"
backup="$work/control-center-before-040.tar.gz"
tar -czf "$backup" -C /opt/instapass/wordpress/wp-content/plugins instapass-control-center
sha256sum "$backup" > "$work/SHA256SUMS"
cat "$work/SHA256SUMS"
install -o 33 -g 33 -m 644 "$source/includes/bulk-pricing.php" "$target/includes/bulk-pricing.php"
for file in bulk-pricing.js bulk-pricing.css xlsx.full.min.js SHEETJS-LICENSE.txt;do
 install -o 33 -g 33 -m 644 "$source/assets/$file" "$target/assets/$file"
done
docker exec instapass-wordpress-1 php -l /var/www/html/wp-content/plugins/instapass-control-center/includes/bulk-pricing.php
install -o 33 -g 33 -m 644 "$source/README.md" "$target/README.md"
install -o 33 -g 33 -m 644 "$source/instapass-control-center.php" "$target/instapass-control-center.next"
docker exec instapass-wordpress-1 php -l /var/www/html/wp-content/plugins/instapass-control-center/instapass-control-center.next
mv "$target/instapass-control-center.next" "$target/instapass-control-center.php"
touch "$target/instapass-control-center.php"
docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v /home/ubuntu/audit-bulk-live.php:/tmp/audit.php:ro wpcli eval-file /tmp/audit.php > "$work/after.json"
python3 - "$work" <<'PY'
import json,sys
from pathlib import Path
p=Path(sys.argv[1]);before=json.loads((p/'before.json').read_text());after=json.loads((p/'after.json').read_text())
assert before['control_version']=='0.3.0'
assert after['control_version']=='0.4.0'
for field in ['base_currency','pkr_rate','product_count','usd_priced','gbp_priced','order_count','catalog_fingerprint','order_fingerprint']:
    assert before[field]==after[field],field+' changed'
result={'state':'code_installed','before':before,'after':after,'data_fingerprints_unchanged':True,'rollback_archive':str(p/'control-center-before-040.tar.gz')}
(p/'result.json').write_text(json.dumps(result,indent=2))
print(json.dumps(result,indent=2))
PY
cp "$work/result.json" /home/ubuntu/control-center-040-deployment-result.json
chmod 644 /home/ubuntu/control-center-040-deployment-result.json
