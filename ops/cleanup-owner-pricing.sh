#!/usr/bin/env bash
set -euo pipefail
work=$(cat /home/ubuntu/owner-pricing-state)
[[ "$work" =~ ^/opt/instapass/recovery/owner-pricing-test-[0-9]{8}T[0-9]{6}Z$ ]] || exit 2
mapfile -t r < "$work/resources"
[[ "${r[0]}" =~ ^instapass-owner-pricing-[0-9]{8}T[0-9]{6}Z$ ]] || exit 2
[[ "${r[1]}" =~ ^instapass-owner-db-[0-9]{8}T[0-9]{6}Z$ ]] || exit 2
[[ "${r[2]}" =~ ^instapass-owner-cli-[0-9]{8}T[0-9]{6}Z$ ]] || exit 2
[[ "${r[3]}" =~ ^instapass-owner-web-[0-9]{8}T[0-9]{6}Z$ ]] || exit 2
for name in "${r[3]}" "${r[2]}";do if docker inspect "$name" >/dev/null 2>&1;then docker rm -f "$name" >/dev/null;fi;done
if docker inspect "${r[1]}" >/dev/null 2>&1;then docker stop "${r[1]}" >/dev/null;fi
if docker network inspect "${r[0]}" >/dev/null 2>&1;then docker network rm "${r[0]}" >/dev/null;fi
rm -f /home/ubuntu/owner-pricing-private.json "$work/browser-private.json"
echo "Scoped owner-pricing fixture stopped and temporary credential removed; restricted recovery results retained."
