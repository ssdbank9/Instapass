#!/usr/bin/env bash
set -euo pipefail
target=/opt/instapass/wordpress/wp-content/plugins/instapass-guest-orders/instapass-guest-orders.php
stamp=$(date -u +%Y%m%dT%H%M%SZ)
recovery=/opt/instapass/recovery/guest-commerce-mail-$stamp
mkdir -m 700 "$recovery"
cp "$target" "$recovery/"
install -o 33 -g 33 -m 644 /home/ubuntu/guest-orders-mail-next.php "$target.next"
docker exec instapass-wordpress-1 php -l /var/www/html/wp-content/plugins/instapass-guest-orders/instapass-guest-orders.php.next
mv "$target.next" "$target"
touch "$target"
chmod a+r /home/ubuntu/guest-commerce-mail-headers-test.php
docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v /home/ubuntu/guest-commerce-mail-headers-test.php:/tmp/mail-headers.php:ro wpcli eval-file /tmp/mail-headers.php
printf '\nMail default Reply-To update installed. Recovery: %s\n' "$recovery"
