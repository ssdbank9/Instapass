#!/usr/bin/env bash
set -euo pipefail
umask 077
next=/home/ubuntu/official-savings-next
work=/opt/instapass/recovery/pro-offer-$(date -u +%Y%m%dT%H%M%SZ)
mkdir -p "$work"
chown 33:33 "$work"
printf '%s\n' "$work" > /home/ubuntu/official-savings-pro-state
audit(){ docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v "$next/$1":/tmp/audit.php:ro wpcli eval-file /tmp/audit.php; }
audit audit.php > "$work/before-audit.json"
audit catalog-audit.php > "$work/before-products.json"
docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v "$next/pro-setup.php":/tmp/pro.php:ro -v "$work":/tmp/pro-recovery:rw wpcli eval-file /tmp/pro.php > "$work/result.json"
audit audit.php > "$work/after-audit.json"
audit catalog-audit.php > "$work/after-products.json"
python3 - "$work" <<'PY'
import json,sys,pathlib
p=pathlib.Path(sys.argv[1]);load=lambda f:json.loads((p/f).read_text())
b,a=load('before-audit.json'),load('after-audit.json');bp,ap=load('before-products.json'),load('after-products.json');r=load('result.json');added=str(r['annual_id'])
for k in ('orders_count','orders_hash','settings_hash','gateways_hash','coupons_hash'):assert b[k]==a[k],k+' changed'
for pid,h in bp.items():
 if pid!='30':assert ap[pid]==h,'Unrelated product changed: '+pid
assert set(ap)-set(bp)=={added},'Unexpected product additions'
assert r['monthly_prices_unchanged'] and r['annual_usd']=='173.33' and float(r['annual_pkr'])==47500 and r['annual_gbp']==''
print(json.dumps(r,indent=2));print('Only approved Pro monthly metadata and one annual Pro offer changed. Orders and other commercial settings unchanged.')
PY
echo "Pro offer recovery: $work"
