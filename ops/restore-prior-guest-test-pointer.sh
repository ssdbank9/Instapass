#!/usr/bin/env bash
set -euo pipefail
work=/opt/instapass/recovery/guest-commerce-test-20261026T052426Z
expected_net=instapass-guest-test-20261026T052426Z
expected_db=instapass-guest-db-20261026T052426Z
test -f "$work/resources"
mapfile -t resources < "$work/resources"
test "${resources[0]:-}" = "$expected_net"
test "${resources[1]:-}" = "$expected_db"
docker inspect "$expected_db" >/dev/null
docker inspect instapass-guest-web >/dev/null
printf '%s\n' "$expected_net" | grep -Eq '^instapass-guest-test-[0-9]{8}T[0-9]{6}Z$'
printf '%s\n' "$work" > /home/ubuntu/guest-commerce-state.next
mv /home/ubuntu/guest-commerce-state.next /home/ubuntu/guest-commerce-state
cat /home/ubuntu/guest-commerce-state
