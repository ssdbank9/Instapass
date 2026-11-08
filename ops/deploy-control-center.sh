#!/usr/bin/env bash
set -euo pipefail
source=/home/ubuntu/instapass-control-center
target=/opt/instapass/wordpress/wp-content/plugins/instapass-control-center
if [ -e "$target" ]; then echo 'Destination already exists; inspect it before deploying'; exit 1; fi
cp -R "$source" "$target"
chown -R 33:33 "$target"
find "$target" -type d -exec chmod 755 {} +
find "$target" -type f -exec chmod 644 {} +
docker exec instapass-wordpress-1 php -l /var/www/html/wp-content/plugins/instapass-control-center/instapass-control-center.php
docker exec instapass-wordpress-1 php -l /var/www/html/wp-content/plugins/instapass-control-center/includes/money.php
docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm wpcli plugin activate instapass-control-center
docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm wpcli plugin get instapass-control-center --fields=name,status,version --format=json
