#!/usr/bin/env bash
set -euo pipefail
backup=/opt/instapass/recovery/notifications-010-deploy-20261018T094014Z/notifications-before-proxy-guard.php
test ! -e "$backup"
cp -p /opt/instapass/wordpress/wp-content/plugins/instapass-notifications/instapass-notifications.php "$backup"
wpcli=wordpress@sha256:36243da432a0f0a3b56dc3240ecee9b159108d71305e7c064d987a20a465bfde
docker run --rm --network none --entrypoint php -v /home/ubuntu/notifications-010-code:/tmp/next:ro "$wpcli" -l /tmp/next/instapass-notifications.php
install -m 644 /home/ubuntu/notifications-010-code/instapass-notifications.php /opt/instapass/wordpress/wp-content/plugins/instapass-notifications/instapass-notifications.php
docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v /home/ubuntu/notifications-live-audit.php:/tmp/audit.php:ro wpcli eval-file /tmp/audit.php
sha256sum /opt/instapass/wordpress/wp-content/plugins/instapass-notifications/instapass-notifications.php
