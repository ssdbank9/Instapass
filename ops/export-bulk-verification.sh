#!/usr/bin/env bash
set -euo pipefail
latest=$(find /opt/instapass/recovery -maxdepth 1 -type d -name 'currency-test-*' -printf '%f\n' | sort | tail -n 1)
[[ "$latest" =~ ^currency-test-[0-9]{8}T[0-9]{6}Z$ ]] || exit 2
cp "/opt/instapass/recovery/$latest/result.json" /home/ubuntu/currency-bulk-040-result.json
chmod 644 /home/ubuntu/currency-bulk-040-result.json
docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v /home/ubuntu/audit-bulk-live.php:/tmp/audit.php:ro wpcli eval-file /tmp/audit.php > /home/ubuntu/bulk-040-live-audit.json
chmod 644 /home/ubuntu/bulk-040-live-audit.json
cat /home/ubuntu/bulk-040-live-audit.json
