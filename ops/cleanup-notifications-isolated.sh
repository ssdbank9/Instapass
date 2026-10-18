#!/usr/bin/env bash
set -euo pipefail
work=$(cat /home/ubuntu/notifications-010-state)
[[ "$work" =~ ^/opt/instapass/recovery/notifications-010-test-[0-9]{8}T[0-9]{6}Z$ ]] || exit 2
net=$(sed -n '1p' "$work/resources")
db=$(sed -n '2p' "$work/resources")
cli=$(sed -n '3p' "$work/resources")
[[ "$net" =~ ^instapass-notifications-010-[0-9]{8}T[0-9]{6}Z$ ]] || exit 2
[[ "$db" =~ ^instapass-notifications-db-[0-9]{8}T[0-9]{6}Z$ ]] || exit 2
[[ "$cli" =~ ^instapass-notifications-cli-[0-9]{8}T[0-9]{6}Z$ ]] || exit 2
cp "$work/result.json" /home/ubuntu/notifications-integration-result.json
chmod 644 /home/ubuntu/notifications-integration-result.json
for name in "$cli" "$db" instapass-notifications-web;do
 if docker inspect "$name" >/dev/null 2>&1;then docker rm -f "$name" >/dev/null;fi
done
docker network rm "$net" >/dev/null
rm -f /home/ubuntu/notifications-browser-private.json
systemctl is-active instapass-notifications.timer
systemctl show instapass-notifications.service -p Result -p ExecMainStatus
printf 'Isolated test containers and network removed; private recovery artifacts retained.\n'
