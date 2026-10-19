#!/usr/bin/env bash
set -euo pipefail
umask 077
target=/opt/instapass/wordpress/wp-content/plugins/instapass-tenor-offers
stage=/home/ubuntu/monthly-tenor-code
test "$(sha256sum "$target/instapass-tenor-offers.php"|cut -d' ' -f1)" = 302381b0d5994778ee5d8f81c8bdd32f8a41db3f1ef07e47381a069b651cfa64
test "$(sha256sum "$target/tenor-offers.css"|cut -d' ' -f1)" = e832618228b278dbcb61841b4a244dba44e216fff577ddd053a35adb2f70fc03
r=/opt/instapass/recovery/monthly-card-wrap-$(date -u +%Y%m%dT%H%M%SZ)
mkdir -p "$r"
cp "$target/instapass-tenor-offers.php" "$target/tenor-offers.css" "$r/"
wp(){ docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v /home/ubuntu/audit-guest-commerce.php:/tmp/audit.php:ro wpcli eval-file /tmp/audit.php; }
wp > "$r/audit-before.json"
for f in instapass-tenor-offers.php tenor-offers.css;do install -o 33 -g 33 -m 644 "$stage/$f" "$target/$f.next";done
docker exec instapass-wordpress-1 php -l /var/www/html/wp-content/plugins/instapass-tenor-offers/instapass-tenor-offers.php.next
mv "$target/tenor-offers.css.next" "$target/tenor-offers.css"
mv "$target/instapass-tenor-offers.php.next" "$target/instapass-tenor-offers.php"
wp > "$r/audit-after.json"
cmp "$r/audit-before.json" "$r/audit-after.json"
for f in instapass-tenor-offers.php tenor-offers.css;do cmp "$stage/$f" "$target/$f";done
printf '\nMonthly card wrapping deployed; financial audit unchanged. Recovery: %s\n' "$r"
