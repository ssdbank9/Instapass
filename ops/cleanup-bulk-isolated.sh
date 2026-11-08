#!/usr/bin/env bash
set -euo pipefail
work=$(cat /home/ubuntu/bulk-040-state)
[[ "$work" =~ ^/opt/instapass/recovery/bulk-040-test-[0-9]{8}T[0-9]{6}Z$ ]] || exit 2
mapfile -t resources < "$work/resources"
test "${#resources[@]}" -eq 4
[[ "${resources[0]}" =~ ^instapass-bulk-040-[0-9]{8}T[0-9]{6}Z$ ]] || exit 2
for i in 1 2 3;do
 [[ "${resources[$i]}" =~ ^instapass-bulk-(db|cli|web)-[0-9]{8}T[0-9]{6}Z$ ]] || exit 2
 if docker inspect "${resources[$i]}" >/dev/null 2>&1;then docker rm -f "${resources[$i]}" >/dev/null;fi
done
docker network rm "${resources[0]}" >/dev/null
printf 'Isolated bulk containers and network removed. Recovery artifacts retained.\n'
