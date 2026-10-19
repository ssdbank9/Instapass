#!/usr/bin/env bash
set -euo pipefail
work=$(cat /home/ubuntu/bundle-offers-state)
[[ "$work" =~ ^/opt/instapass/recovery/bundle-offers-test-[0-9TZ]+$ ]] || exit 1
mapfile -t resources < "$work/resources"
[[ "${resources[0]}" =~ ^instapass-bundle-test-[0-9TZ]+$ ]] || exit 1
[[ "${resources[1]}" =~ ^instapass-bundle-db-[0-9TZ]+$ ]] || exit 1
for name in instapass-bundle-setup instapass-bundle-web "${resources[1]}";do if docker inspect "$name" >/dev/null 2>&1;then docker rm -f "$name" >/dev/null;fi;done
docker network rm "${resources[0]}" >/dev/null
rm -f -- /home/ubuntu/bundle-private.json
printf 'Bundle fixture resources removed; protected evidence retained.\n'
