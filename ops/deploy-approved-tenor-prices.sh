#!/usr/bin/env bash
set -euo pipefail
umask 077
target=/opt/instapass/wordpress/wp-content/plugins/instapass-control-center
stage=/home/ubuntu/control-currency-code
test "$(sha256sum "$target/instapass-control-center.php"|cut -d' ' -f1)" = 56365161b9c309eba4c0c2c2bc051d66126099b9837526226e636da26753b907
test "$(sha256sum "$target/includes/currencies.php"|cut -d' ' -f1)" = 0ee7f3db4e5cb0f2d263a2e5f2edf2e14681b96d1110f8fa4fc8346d089103c6
test "$(sha256sum "$target/includes/bulk-pricing.php"|cut -d' ' -f1)" = cca5633dad5aece908c3a4195428646e7330eac98a14d29c179b79ff86da4b57
test ! -e "$target/includes/pkr-editor.php"
recovery=/opt/instapass/recovery/approved-prices-$(date -u +%Y%m%dT%H%M%SZ)
mkdir -p "$recovery"
cp -a "$target" "$recovery/control-center"
wp(){ docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v /home/ubuntu/configure-approved-tenor-prices.php:/tmp/configure.php:ro -v /home/ubuntu/approved-prices-state.php:/tmp/state.php:ro -v /home/ubuntu/tenor-price-preflight.php:/tmp/preflight.php:ro "$@"; }
wp wpcli eval-file /tmp/state.php > "$recovery/protected-before.json"
wp wpcli eval-file /tmp/preflight.php > "$recovery/source-products-before.json"
for f in instapass-control-center.php includes/pkr-editor.php includes/currencies.php includes/bulk-pricing.php;do
 install -o 33 -g 33 -m 644 "$stage/$f" "$target/$f.next"
 docker exec instapass-wordpress-1 php -l "/var/www/html/wp-content/plugins/instapass-control-center/$f.next"
done
# Introduce definitions before the currency getter starts using them.
for f in includes/pkr-editor.php instapass-control-center.php includes/currencies.php includes/bulk-pricing.php;do mv "$target/$f.next" "$target/$f";done
wp -e IP_PRICE_MODE=stage wpcli eval-file /tmp/configure.php > "$recovery/staged-offers.json"
wp -e IP_PRICE_MODE=publish -e IP_PLUS_MONTHLY=yes wpcli eval-file /tmp/configure.php > "$recovery/published-offers.json"
wp wpcli eval-file /tmp/state.php > "$recovery/protected-after.json"
cmp "$recovery/protected-before.json" "$recovery/protected-after.json"
for f in instapass-control-center.php includes/pkr-editor.php includes/currencies.php includes/bulk-pricing.php;do cmp "$stage/$f" "$target/$f";done
cat "$recovery/published-offers.json"
printf '\nUnrelated catalog, all historical orders, rate, coupons and payment settings unchanged. Recovery: %s\n' "$recovery"
