#!/usr/bin/env bash
set -euo pipefail
plugin=/opt/instapass/wordpress/wp-content/plugins/instapass-guest-orders/instapass-guest-orders.php
guest=/opt/instapass/wordpress/wp-content/themes/instapass/inc/guest-checkout.php
cmp -s "$guest" /home/ubuntu/guest-commerce-live-inc/guest-checkout.php
expected=$(awk '$2=="/opt/instapass/wordpress/wp-content/plugins/instapass-guest-orders/instapass-guest-orders.php"{print $1}' /home/ubuntu/guest-commerce-final-hashes.txt)
test -n "$expected"
printf '%s  %s\n' "$expected" "$plugin" | sha256sum -c -
recovery=/opt/instapass/recovery/guest-commerce-copy-$(date -u +%Y%m%dT%H%M%SZ)
mkdir -m 700 "$recovery"
cp "$plugin" "$recovery/instapass-guest-orders.php"
cp "$guest" "$recovery/guest-checkout.php"
install -o 33 -g 33 -m 644 /home/ubuntu/guest-orders-copy-next.php "$plugin.next"
install -o 33 -g 33 -m 644 /home/ubuntu/guest-checkout-copy-next.php "$guest.next"
docker exec instapass-wordpress-1 php -l /var/www/html/wp-content/plugins/instapass-guest-orders/instapass-guest-orders.php.next
docker exec instapass-wordpress-1 php -l /var/www/html/wp-content/themes/instapass/inc/guest-checkout.php.next
mv "$plugin.next" "$plugin";touch "$plugin"
mv "$guest.next" "$guest";touch "$guest"
printf 'Checkout copy corrected. Recovery: %s\n' "$recovery"
