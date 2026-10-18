#!/usr/bin/env bash
set -euo pipefail
umask 077
target=/opt/instapass/wordpress/wp-content
test ! -e "$target/plugins/instapass-guest-orders"
cmp -s "$target/themes/instapass/inc/manual-payments.php" /home/ubuntu/guest-commerce-live-inc/manual-payments.php
cmp -s "$target/themes/instapass/inc/payment-confirmation.php" /home/ubuntu/guest-commerce-live-inc/payment-confirmation.php
stamp=$(date -u +%Y%m%dT%H%M%SZ)
recovery=/opt/instapass/recovery/guest-commerce-deploy-$stamp
mkdir -p "$recovery"
cp "$target/themes/instapass/inc/manual-payments.php" "$recovery/"
cp "$target/themes/instapass/inc/payment-confirmation.php" "$recovery/"
printf '%s\n' "$recovery" > /home/ubuntu/guest-commerce-deploy-state
chmod a+r /home/ubuntu/guest-commerce-deploy-settings.php /home/ubuntu/audit-guest-commerce.php
wp(){ docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v /home/ubuntu/guest-commerce-deploy-settings.php:/tmp/settings.php:ro -v /home/ubuntu/audit-guest-commerce.php:/tmp/audit.php:ro "$@" ; }
wp -e IP_GUEST_CAPTURE=yes wpcli eval-file /tmp/settings.php > "$recovery/options-before.json"
wp wpcli eval-file /tmp/audit.php > "$recovery/audit-before.json"
cp -a /home/ubuntu/instapass-guest-orders "$target/plugins/instapass-guest-orders"
chown -R 33:33 "$target/plugins/instapass-guest-orders"
find "$target/plugins/instapass-guest-orders" -type d -exec chmod 755 {} \;
find "$target/plugins/instapass-guest-orders" -type f -exec chmod 644 {} \;
docker exec instapass-wordpress-1 php -l /var/www/html/wp-content/plugins/instapass-guest-orders/instapass-guest-orders.php
for part in manual-payments payment-confirmation; do
 install -o 33 -g 33 -m 644 "/home/ubuntu/guest-$part.php" "$target/themes/instapass/inc/$part.php.next"
 docker exec instapass-wordpress-1 php -l "/var/www/html/wp-content/themes/instapass/inc/$part.php.next"
done
for part in manual-payments payment-confirmation;do mv "$target/themes/instapass/inc/$part.php.next" "$target/themes/instapass/inc/$part.php";touch "$target/themes/instapass/inc/$part.php";done
wp wpcli plugin activate instapass-guest-orders
wp wpcli eval-file /tmp/settings.php
wp wpcli eval-file /tmp/audit.php > "$recovery/audit-after.json"
php_check='echo'
cmp_catalog=$(docker exec -i instapass-wordpress-1 php -r '$a=json_decode(file_get_contents("php://stdin"),true);echo $a["products"]["hash"]."|".$a["orders"]["hash"];' < "$recovery/audit-before.json")
cmp_after=$(docker exec -i instapass-wordpress-1 php -r '$a=json_decode(file_get_contents("php://stdin"),true);echo $a["products"]["hash"]."|".$a["orders"]["hash"];' < "$recovery/audit-after.json")
test "$cmp_catalog" = "$cmp_after"
sha256sum "$target/plugins/instapass-guest-orders/instapass-guest-orders.php" "$target/plugins/instapass-guest-orders/guest-orders.css" "$target/themes/instapass/inc/manual-payments.php" "$target/themes/instapass/inc/payment-confirmation.php" > "$recovery/deployed-hashes.txt"
cat "$recovery/audit-after.json"
printf '\nCatalog and original order hashes unchanged. Recovery: %s\n' "$recovery"
