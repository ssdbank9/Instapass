#!/usr/bin/env bash
set -euo pipefail
umask 077
target=/opt/instapass/wordpress/wp-content
cmp -s "$target/themes/instapass/inc/promotions.php" /home/ubuntu/guest-commerce-live-inc/promotions.php
test "$(sha256sum "$target/plugins/instapass-guest-orders/instapass-guest-orders.php" | cut -d' ' -f1)" = d347ddf82c6a1c9d70ac52c5458f619329f53a442da7581a54fb4ff266c689c3
stamp=$(date -u +%Y%m%dT%H%M%SZ)
recovery=/opt/instapass/recovery/bundle-offers-deploy-$stamp
mkdir -p "$recovery"
cp "$target/themes/instapass/inc/promotions.php" "$recovery/promotions.php"
cp "$target/plugins/instapass-guest-orders/instapass-guest-orders.php" "$recovery/guest-orders.php"
printf '%s\n' "$recovery" > /home/ubuntu/bundle-offers-deploy-state
chmod a+r /home/ubuntu/bundle-offers-configure.php /home/ubuntu/audit-guest-commerce.php
wp(){ docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v /home/ubuntu/bundle-offers-configure.php:/tmp/configure.php:ro -v /home/ubuntu/audit-guest-commerce.php:/tmp/audit.php:ro "$@"; }
wp wpcli eval-file /tmp/audit.php > "$recovery/audit-before.json"
# Native settings/coupons are backed up in the verified full database snapshot as well.
wp wpcli option get instapass_promotion --format=json > "$recovery/promotion-before.json"
install -o 33 -g 33 -m 644 /home/ubuntu/bundle-promotions.php "$target/themes/instapass/inc/promotions.php.next"
install -o 33 -g 33 -m 644 /home/ubuntu/bundle-guest-orders.php "$target/plugins/instapass-guest-orders/instapass-guest-orders.php.next"
docker exec instapass-wordpress-1 php -l /var/www/html/wp-content/themes/instapass/inc/promotions.php.next
docker exec instapass-wordpress-1 php -l /var/www/html/wp-content/plugins/instapass-guest-orders/instapass-guest-orders.php.next
mv "$target/themes/instapass/inc/promotions.php.next" "$target/themes/instapass/inc/promotions.php"
mv "$target/plugins/instapass-guest-orders/instapass-guest-orders.php.next" "$target/plugins/instapass-guest-orders/instapass-guest-orders.php"
touch "$target/themes/instapass/inc/promotions.php" "$target/plugins/instapass-guest-orders/instapass-guest-orders.php"
wp wpcli eval-file /tmp/configure.php > "$recovery/offers-after.json"
wp wpcli eval-file /tmp/audit.php > "$recovery/audit-after.json"
before=$(docker exec -i instapass-wordpress-1 php -r '$a=json_decode(file_get_contents("php://stdin"),true);echo $a["products"]["hash"]."|".$a["orders"]["hash"];' < "$recovery/audit-before.json")
after=$(docker exec -i instapass-wordpress-1 php -r '$a=json_decode(file_get_contents("php://stdin"),true);echo $a["products"]["hash"]."|".$a["orders"]["hash"];' < "$recovery/audit-after.json")
test "$before" = "$after"
cmp -s /home/ubuntu/bundle-promotions.php "$target/themes/instapass/inc/promotions.php"
cmp -s /home/ubuntu/bundle-guest-orders.php "$target/plugins/instapass-guest-orders/instapass-guest-orders.php"
sha256sum "$target/themes/instapass/inc/promotions.php" "$target/plugins/instapass-guest-orders/instapass-guest-orders.php" > "$recovery/deployed-hashes.txt"
cat "$recovery/offers-after.json"
cat "$recovery/audit-after.json"
printf '\nSource hashes match; original catalog/orders unchanged. Recovery: %s\n' "$recovery"
