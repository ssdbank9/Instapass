#!/usr/bin/env bash
set -euo pipefail
umask 077
stamp=$(date -u +%Y%m%dT%H%M%SZ)
work=/opt/instapass/recovery/notifications-010-deploy-$stamp
mkdir -p "$work"
test ! -e /opt/instapass/wordpress/wp-content/plugins/instapass-notifications
docker exec instapass-db-1 sh -c 'exec mariadb-dump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction --quick --databases "$MYSQL_DATABASE"' > "$work/pre-notifications.sql"
test -s "$work/pre-notifications.sql"
sha256sum "$work/pre-notifications.sql" > "$work/backup.sha256"
docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v /home/ubuntu/notifications-live-audit.php:/tmp/audit.php:ro wpcli eval-file /tmp/audit.php > "$work/before.json"
install -d -m 755 /opt/instapass/wordpress/wp-content/plugins/instapass-notifications
install -m 644 /home/ubuntu/notifications-010-code/instapass-notifications.php /opt/instapass/wordpress/wp-content/plugins/instapass-notifications/instapass-notifications.php
docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm wpcli plugin activate instapass-notifications
docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v /home/ubuntu/notifications-live-audit.php:/tmp/audit.php:ro wpcli eval-file /tmp/audit.php > "$work/after.json"
python3 - "$work/before.json" "$work/after.json" <<'PY'
import json,sys
before,after=[json.load(open(p)) for p in sys.argv[1:]]
for key in ['product_count','product_fingerprint','order_count','order_fingerprint']:
    assert before[key]==after[key],key
assert after['installed'] and not after['enabled'] and not after['settings_enabled']
assert after['subscriptions']==0 and after['action_scheduler']
print(json.dumps({'unchanged_products_orders':True,'audit':after},indent=2))
PY
install -m 644 /home/ubuntu/notifications-runner.php /opt/instapass/notifications-runner.php
cat > /usr/local/sbin/instapass-notifications-runner <<'RUNNER'
#!/usr/bin/env bash
set -euo pipefail
exec /usr/bin/flock -n /run/lock/instapass-notifications.lock /usr/bin/docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v /opt/instapass/notifications-runner.php:/tmp/notifications-runner.php:ro wpcli eval-file /tmp/notifications-runner.php
RUNNER
chmod 750 /usr/local/sbin/instapass-notifications-runner
cat > /etc/systemd/system/instapass-notifications.service <<'SERVICE'
[Unit]
Description=Instapass stock-alert queue only
After=docker.service network-online.target
Requires=docker.service
[Service]
Type=oneshot
ExecStart=/usr/local/sbin/instapass-notifications-runner
TimeoutStartSec=300
SERVICE
cat > /etc/systemd/system/instapass-notifications.timer <<'TIMER'
[Unit]
Description=Run the Instapass stock-alert queue every two minutes
[Timer]
OnBootSec=3min
OnUnitActiveSec=2min
AccuracySec=15s
[Install]
WantedBy=timers.target
TIMER
systemctl daemon-reload
systemctl enable --now instapass-notifications.timer
systemctl start instapass-notifications.service
systemctl is-active instapass-notifications.timer
systemctl show instapass-notifications.service -p Result -p ExecMainStatus
sha256sum /opt/instapass/wordpress/wp-content/plugins/instapass-notifications/instapass-notifications.php
printf 'Recovery: %s\n' "$work"
