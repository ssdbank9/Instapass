#!/usr/bin/env bash
set -euo pipefail
umask 077
target=/opt/instapass/wordpress/wp-content/plugins/instapass-tenor-offers
stage=/home/ubuntu/monthly-tenor-code
test "$(sha256sum "$target/instapass-tenor-offers.php"|cut -d' ' -f1)" = 611fa2ad908acbbaa89aff1592469797ee405b4a4c68073d1176ab2872769cb7
test "$(sha256sum "$target/tenor-offers.css"|cut -d' ' -f1)" = 65177c2b283c7aa1ae9e75c9ce6411c896f741158fd9eb6b460792580dd70aa7
test ! -e "$target/monthly-pricing.php"
r=/opt/instapass/recovery/monthly-pricing-$(date -u +%Y%m%dT%H%M%SZ)
mkdir -p "$r"
cp -a "$target" "$r/tenor-plugin"
wp(){ docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v /home/ubuntu/audit-guest-commerce.php:/tmp/audit.php:ro -v /home/ubuntu/monthly-price-preflight.php:/tmp/tax.php:ro "$@"; }
wp wpcli eval-file /tmp/audit.php > "$r/audit-before.json"
wp wpcli eval-file /tmp/tax.php > "$r/tax-before.json"
for f in instapass-tenor-offers.php monthly-pricing.php tenor-offers.css;do install -o 33 -g 33 -m 644 "$stage/$f" "$target/$f.next";done
for f in instapass-tenor-offers.php monthly-pricing.php;do docker exec instapass-wordpress-1 php -l "/var/www/html/wp-content/plugins/instapass-tenor-offers/$f.next";done
for f in tenor-offers.css monthly-pricing.php instapass-tenor-offers.php;do mv "$target/$f.next" "$target/$f";done
wp wpcli eval-file /tmp/audit.php > "$r/audit-after.json"
wp wpcli eval-file /tmp/tax.php > "$r/tax-after.json"
cmp "$r/audit-before.json" "$r/audit-after.json"
cmp "$r/tax-before.json" "$r/tax-after.json"
for f in instapass-tenor-offers.php monthly-pricing.php tenor-offers.css;do cmp "$stage/$f" "$target/$f";done
cat "$r/tax-after.json"
printf '\nAll product prices/stock, orders, coupons, gateways, currency and tax settings unchanged. Recovery: %s\n' "$r"
