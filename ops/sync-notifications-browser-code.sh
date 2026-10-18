#!/usr/bin/env bash
set -euo pipefail
work=$(cat /home/ubuntu/notifications-010-state)
[[ "$work" =~ ^/opt/instapass/recovery/notifications-010-test-[0-9]{8}T[0-9]{6}Z$ ]] || exit 2
install -m 644 /home/ubuntu/notifications-010-code/instapass-notifications.php "$work/plugins/instapass-notifications/instapass-notifications.php"
docker exec instapass-notifications-web php -l /var/www/html/wp-content/plugins/instapass-notifications/instapass-notifications.php
