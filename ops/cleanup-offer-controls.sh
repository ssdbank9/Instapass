#!/usr/bin/env bash
set -euo pipefail
work=$(cat /home/ubuntu/offer-controls-state)
[[ "$work" =~ ^/opt/instapass/recovery/offer-controls-test-[0-9]{8}T[0-9]{6}Z$ ]] || exit 2
stamp=${work##*test-}
readarray -t names < "$work/resources"
test "${names[0]}" = "ip-offer-test-$stamp"
test "${names[1]}" = "ip-offer-db-$stamp"
test "${names[2]}" = "ip-offer-cli-$stamp"
test "${names[3]}" = "ip-offer-web-$stamp"
for name in "${names[3]}" "${names[2]}" "${names[1]}";do if docker container inspect "$name" >/dev/null 2>&1;then docker rm -f "$name" >/dev/null;fi;done
if docker network inspect "${names[0]}" >/dev/null 2>&1;then docker network rm "${names[0]}" >/dev/null;fi
if [ "${#names[@]}" -gt 4 ];then test "${names[4]}" = "ip-offer-uploads-$stamp";if docker volume inspect "${names[4]}" >/dev/null 2>&1;then docker volume rm "${names[4]}" >/dev/null;fi;fi
if [ -f "$work/integration.json" ];then cp "$work/integration.json" /home/ubuntu/offer-controls-result.json;chmod 644 /home/ubuntu/offer-controls-result.json;fi
if [ -f "$work/currency.json" ];then cp "$work/currency.json" /home/ubuntu/offer-controls-currency-result.json;chmod 644 /home/ubuntu/offer-controls-currency-result.json;fi
rm -f -- "$work/resources" "$work/db.env" "$work/cli.env" "$work/integration.json" "$work/currency.json"
rmdir -- "$work"
rm -f /home/ubuntu/offer-controls-state /home/ubuntu/offer-controls-private.json /home/ubuntu/offer-controls-web-ip
echo 'Exact offer-controls fixture resources and credentials removed.'
