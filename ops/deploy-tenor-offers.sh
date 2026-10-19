#!/usr/bin/env bash
set -euo pipefail
umask 077
target=/opt/instapass/wordpress/wp-content
test ! -e "$target/plugins/instapass-tenor-offers"
test "$(sha256sum "$target/themes/instapass/inc/promotions.php" | cut -d' ' -f1)" = ee0c8b0ec676b56d5b54b67352153a4c76706afe31631cbb152f2f4e600733fc
test "$(sha256sum "$target/plugins/instapass-guest-orders/instapass-guest-orders.php" | cut -d' ' -f1)" = 1d67eaec0229cc93dfc4cddf9627fd4fabb23d70f1c0bbc7abc832d03527a415
recovery=/opt/instapass/recovery/tenor-offers-deploy-$(date -u +%Y%m%dT%H%M%SZ)
mkdir -p "$recovery"
cp "$target/themes/instapass/inc/promotions.php" "$recovery/"
cp "$target/plugins/instapass-guest-orders/instapass-guest-orders.php" "$recovery/"
printf '%s\n' "$recovery" > /home/ubuntu/tenor-offers-deploy-state
wp(){ docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v /home/ubuntu/audit-guest-commerce.php:/tmp/audit.php:ro "$@"; }
wp wpcli eval-file /tmp/audit.php > "$recovery/audit-before.json"
wp wpcli option get active_plugins --format=json > "$recovery/active-plugins-before.json"
cp -a /home/ubuntu/instapass-tenor-offers "$target/plugins/"
chown -R 33:33 "$target/plugins/instapass-tenor-offers"
find "$target/plugins/instapass-tenor-offers" -type d -exec chmod 755 {} \;
find "$target/plugins/instapass-tenor-offers" -type f -exec chmod 644 {} \;
docker exec instapass-wordpress-1 php -l /var/www/html/wp-content/plugins/instapass-tenor-offers/instapass-tenor-offers.php
install -o 33 -g 33 -m 644 /home/ubuntu/bundle-promotions.php "$target/themes/instapass/inc/promotions.php.next"
install -o 33 -g 33 -m 644 /home/ubuntu/bundle-guest-orders.php "$target/plugins/instapass-guest-orders/instapass-guest-orders.php.next"
docker exec instapass-wordpress-1 php -l /var/www/html/wp-content/themes/instapass/inc/promotions.php.next
docker exec instapass-wordpress-1 php -l /var/www/html/wp-content/plugins/instapass-guest-orders/instapass-guest-orders.php.next
mv "$target/themes/instapass/inc/promotions.php.next" "$target/themes/instapass/inc/promotions.php"
mv "$target/plugins/instapass-guest-orders/instapass-guest-orders.php.next" "$target/plugins/instapass-guest-orders/instapass-guest-orders.php"
touch "$target/themes/instapass/inc/promotions.php" "$target/plugins/instapass-guest-orders/instapass-guest-orders.php"
wp wpcli plugin activate instapass-tenor-offers
wp wpcli eval-file /tmp/audit.php > "$recovery/audit-after.json"
before=$(docker exec -i instapass-wordpress-1 php -r '$a=json_decode(file_get_contents("php://stdin"),true);echo $a["products"]["hash"]."|".$a["orders"]["hash"]."|".json_encode($a["coupons"]);' < "$recovery/audit-before.json")
after=$(docker exec -i instapass-wordpress-1 php -r '$a=json_decode(file_get_contents("php://stdin"),true);echo $a["products"]["hash"]."|".$a["orders"]["hash"]."|".json_encode($a["coupons"]);' < "$recovery/audit-after.json")
test "$before" = "$after"
cmp /home/ubuntu/bundle-promotions.php "$target/themes/instapass/inc/promotions.php"
cmp /home/ubuntu/bundle-guest-orders.php "$target/plugins/instapass-guest-orders/instapass-guest-orders.php"
cmp /home/ubuntu/instapass-tenor-offers/instapass-tenor-offers.php "$target/plugins/instapass-tenor-offers/instapass-tenor-offers.php"
cmp /home/ubuntu/instapass-tenor-offers/tenor-offers.css "$target/plugins/instapass-tenor-offers/tenor-offers.css"
sha256sum "$target/themes/instapass/inc/promotions.php" "$target/plugins/instapass-guest-orders/instapass-guest-orders.php" "$target/plugins/instapass-tenor-offers/"* > "$recovery/deployed-hashes.txt"
cat "$recovery/audit-after.json"
printf '\nCatalog/orders/coupons unchanged; four source files match. Recovery: %s\n' "$recovery"
