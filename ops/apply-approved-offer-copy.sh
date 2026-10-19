#!/usr/bin/env bash
set -euo pipefail
umask 077
r=/opt/instapass/recovery/approved-prices-copy-$(date -u +%Y%m%dT%H%M%SZ)
mkdir -p "$r"
wp(){ docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v /home/ubuntu/approved-offer-copy.php:/tmp/copy.php:ro -v /home/ubuntu/audit-guest-commerce.php:/tmp/audit.php:ro "$@"; }
wp wpcli eval-file /tmp/copy.php > "$r/copy-before.json"
wp wpcli eval-file /tmp/audit.php > "$r/audit-before.json"
wp -e IP_OFFER_COPY_APPLY=yes wpcli eval-file /tmp/copy.php > "$r/copy-after.json"
wp wpcli eval-file /tmp/audit.php > "$r/audit-after.json"
cmp "$r/audit-before.json" "$r/audit-after.json"
cat "$r/copy-after.json"
printf '\nPrice, stock, order, coupon and payment audit unchanged. Copy recovery: %s\n' "$r"
