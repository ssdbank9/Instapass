#!/usr/bin/env bash
set -euo pipefail
umask 077
source=/home/ubuntu/control-currency-code
target=/opt/instapass/wordpress/wp-content/plugins/instapass-control-center
stamp=$(date -u +%Y%m%dT%H%M%SZ)
backup=/opt/instapass/recovery/control-center-before-030-$stamp.tar.gz
grep -q 'Version: 0.2.0' "$target/instapass-control-center.php"
tar -czf "$backup" -C /opt/instapass/wordpress/wp-content/plugins instapass-control-center
sha256sum "$backup"
docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm wpcli option get instapass_pkr_rate > "/opt/instapass/recovery/pkr-rate-before-030-$stamp.txt"
for file in money.php rate-settings.php currencies.php gbp-editor.php gbp-values.php; do
 install -o 33 -g 33 -m 644 "$source/includes/$file" "$target/includes/$file"
 docker exec instapass-wordpress-1 php -l "/var/www/html/wp-content/plugins/instapass-control-center/includes/$file"
done
install -o 33 -g 33 -m 644 "$source/assets/currency.css" "$target/assets/currency.css"
install -o 33 -g 33 -m 644 "$source/instapass-control-center.php" "$target/instapass-control-center.next"
docker exec instapass-wordpress-1 php -l /var/www/html/wp-content/plugins/instapass-control-center/instapass-control-center.next
mv "$target/instapass-control-center.next" "$target/instapass-control-center.php"
touch "$target/instapass-control-center.php"
docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm wpcli plugin get instapass-control-center --fields=name,version,status --format=json
printf 'Rollback archive: %s\n' "$backup"
