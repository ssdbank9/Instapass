#!/usr/bin/env bash
set -euo pipefail
umask 077
r=/opt/instapass/recovery/max-annual-seventy-$(date -u +%Y%m%dT%H%M%SZ)
mkdir -p "$r"
wp(){ docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v /home/ubuntu/set-max-annual-seventy.php:/tmp/max70.php:ro "$@"; }
wp wpcli eval-file /tmp/max70.php > "$r/before.json"
wp -e IP_MAX70_MODE=apply wpcli eval-file /tmp/max70.php > "$r/after.json"
docker exec -i instapass-wordpress-1 php -r '$r=json_decode(file_get_contents("php://stdin"),true);if($r["protected"]!==$r["protected_after"]||(float)$r["after_usd"]!==840.0)exit(1);echo json_encode(["annual_usd"=>$r["after_usd"],"protected"=>$r["protected"],"scope"=>$r["scope"]],JSON_PRETTY_PRINT);' < "$r/after.json"
printf '\nAnnual Max840 published; protected financial state unchanged. Recovery: %s\n' "$r"
