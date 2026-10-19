#!/usr/bin/env bash
set -euo pipefail
umask 077
target=/opt/instapass/wordpress/wp-content
test "$(sha256sum "$target/themes/instapass/inc/promotions.php" | cut -d' ' -f1)" = 62f14331e8ea79dfbca447290c02b715ca3445342d811f6e0c52d25b9ca9973c
test "$(sha256sum "$target/plugins/instapass-guest-orders/instapass-guest-orders.php" | cut -d' ' -f1)" = 354a4d4b03559a4e43a0d2a5b7d8701949b74d7b3bafb154420e722f7a39e2c6
test "$(sha256sum "$target/plugins/instapass-tenor-offers/instapass-tenor-offers.php" | cut -d' ' -f1)" = a03f3fbad04a53db33b55f59179fd66bed848fb0307377a39b8aeeb75c9b91e7
recovery=/opt/instapass/recovery/three-product-offer-$(date -u +%Y%m%dT%H%M%SZ)
mkdir -p "$recovery"
cp "$target/themes/instapass/inc/promotions.php" "$recovery/promotions.php"
cp "$target/plugins/instapass-guest-orders/instapass-guest-orders.php" "$recovery/guest-orders.php"
cp "$target/plugins/instapass-tenor-offers/instapass-tenor-offers.php" "$recovery/tenor-offers.php"
wp(){ docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v /home/ubuntu/configure-three-product-offer.php:/tmp/three.php:ro -v /home/ubuntu/audit-guest-commerce.php:/tmp/audit.php:ro "$@"; }
wp -e IP_PROMO_THREE_CAPTURE=yes wpcli eval-file /tmp/three.php > "$recovery/new-offer-before.json"
wp wpcli option get instapass_promotion --format=json > "$recovery/two-offer-before.json"
wp wpcli option get instapass_promotion_four --format=json > "$recovery/four-offer-before.json"
wp wpcli eval-file /tmp/audit.php > "$recovery/audit-before.json"
install -o 33 -g 33 -m 644 /home/ubuntu/bundle-promotions.php "$target/themes/instapass/inc/promotions.php.next"
install -o 33 -g 33 -m 644 /home/ubuntu/bundle-guest-orders.php "$target/plugins/instapass-guest-orders/instapass-guest-orders.php.next"
install -o 33 -g 33 -m 644 /home/ubuntu/instapass-tenor-offers/instapass-tenor-offers.php "$target/plugins/instapass-tenor-offers/instapass-tenor-offers.php.next"
docker exec instapass-wordpress-1 php -l /var/www/html/wp-content/themes/instapass/inc/promotions.php.next
docker exec instapass-wordpress-1 php -l /var/www/html/wp-content/plugins/instapass-guest-orders/instapass-guest-orders.php.next
docker exec instapass-wordpress-1 php -l /var/www/html/wp-content/plugins/instapass-tenor-offers/instapass-tenor-offers.php.next
mv "$target/themes/instapass/inc/promotions.php.next" "$target/themes/instapass/inc/promotions.php"
mv "$target/plugins/instapass-guest-orders/instapass-guest-orders.php.next" "$target/plugins/instapass-guest-orders/instapass-guest-orders.php"
mv "$target/plugins/instapass-tenor-offers/instapass-tenor-offers.php.next" "$target/plugins/instapass-tenor-offers/instapass-tenor-offers.php"
touch "$target/themes/instapass/inc/promotions.php" "$target/plugins/instapass-guest-orders/instapass-guest-orders.php" "$target/plugins/instapass-tenor-offers/instapass-tenor-offers.php"
wp wpcli eval-file /tmp/three.php > "$recovery/offers-after.json"
wp wpcli option get instapass_promotion --format=json > "$recovery/two-offer-after.json"
wp wpcli option get instapass_promotion_four --format=json > "$recovery/four-offer-after.json"
cmp "$recovery/two-offer-before.json" "$recovery/two-offer-after.json"
cmp "$recovery/four-offer-before.json" "$recovery/four-offer-after.json"
wp wpcli eval-file /tmp/audit.php > "$recovery/audit-after.json"
before=$(docker exec -i instapass-wordpress-1 php -r '$a=json_decode(file_get_contents("php://stdin"),true);echo $a["products"]["hash"]."|".$a["orders"]["hash"];' < "$recovery/audit-before.json")
after=$(docker exec -i instapass-wordpress-1 php -r '$a=json_decode(file_get_contents("php://stdin"),true);echo $a["products"]["hash"]."|".$a["orders"]["hash"];' < "$recovery/audit-after.json")
test "$before" = "$after"
cmp /home/ubuntu/bundle-promotions.php "$target/themes/instapass/inc/promotions.php"
cmp /home/ubuntu/bundle-guest-orders.php "$target/plugins/instapass-guest-orders/instapass-guest-orders.php"
cmp /home/ubuntu/instapass-tenor-offers/instapass-tenor-offers.php "$target/plugins/instapass-tenor-offers/instapass-tenor-offers.php"
sha256sum "$target/themes/instapass/inc/promotions.php" "$target/plugins/instapass-guest-orders/instapass-guest-orders.php" "$target/plugins/instapass-tenor-offers/instapass-tenor-offers.php" > "$recovery/deployed-hashes.txt"
cat "$recovery/offers-after.json"
printf '\nExisting offers and catalog/order fingerprints preserved. Runtime sources match. Recovery: %s\n' "$recovery"
