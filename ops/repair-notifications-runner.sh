#!/usr/bin/env bash
set -euo pipefail
install -m 644 /home/ubuntu/notifications-runner.php /opt/instapass/notifications-runner.php
cat > /usr/local/sbin/instapass-notifications-runner <<'RUNNER'
#!/usr/bin/env bash
set -euo pipefail
exec /usr/bin/flock -n /run/lock/instapass-notifications.lock /usr/bin/docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v /opt/instapass/notifications-runner.php:/tmp/notifications-runner.php:ro wpcli eval-file /tmp/notifications-runner.php
RUNNER
chmod 750 /usr/local/sbin/instapass-notifications-runner
systemctl reset-failed instapass-notifications.service
systemctl start instapass-notifications.service
systemctl is-active instapass-notifications.timer
systemctl show instapass-notifications.service -p Result -p ExecMainStatus
sha256sum /opt/instapass/wordpress/wp-content/plugins/instapass-notifications/instapass-notifications.php
ls -dt /opt/instapass/recovery/notifications-010-deploy-*
