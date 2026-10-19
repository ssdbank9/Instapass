#!/usr/bin/env bash
set -euo pipefail
umask 077
target=/opt/instapass/wordpress/wp-content/plugins/instapass-tenor-offers
stage=/home/ubuntu/owner-pricing-code/tenor
test "$(sha256sum "$target/monthly-pricing.php"|cut -d' ' -f1)" = 24fb55f70e98bc94d9f3a24a73bbc137a68e923a791e511aaad690909c30cdf3
test "$(sha256sum "$target/instapass-tenor-offers.php"|cut -d' ' -f1)" = bad8e631f315c814f0085c1759994a8ee3ae9dfc5b6a325204788456193514a3
r=/opt/instapass/recovery/owner-pricing-copy-$(date -u +%Y%m%dT%H%M%SZ)
mkdir -p "$r"
cp -a "$target" "$r/tenor"
wp(){ docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v /home/ubuntu/owner-pricing-audit.php:/tmp/audit.php:ro wpcli eval-file /tmp/audit.php; }
wp > "$r/before.json"
for f in monthly-pricing.php instapass-tenor-offers.php;do install -o 33 -g 33 -m 644 "$stage/$f" "$target/$f.next";docker exec instapass-wordpress-1 php -l "/var/www/html/wp-content/plugins/instapass-tenor-offers/$f.next";done
for f in monthly-pricing.php instapass-tenor-offers.php;do mv "$target/$f.next" "$target/$f";cmp "$stage/$f" "$target/$f";done
wp > "$r/after.json"
cmp "$r/before.json" "$r/after.json"
printf 'Product excerpt repair published; complete financial/catalog audits unchanged. Recovery: %s\n' "$r"
