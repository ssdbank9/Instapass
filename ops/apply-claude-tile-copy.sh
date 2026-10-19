#!/usr/bin/env bash
set -euo pipefail
umask 077
recovery=/opt/instapass/recovery/claude-tile-copy-$(date -u +%Y%m%dT%H%M%SZ)
mkdir -p "$recovery"
wp(){ docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v /home/ubuntu/claude-tile-copy.php:/tmp/tile.php:ro -v /home/ubuntu/audit-guest-commerce.php:/tmp/audit.php:ro "$@"; }
wp wpcli eval-file /tmp/tile.php > "$recovery/brief-before.json"
wp wpcli eval-file /tmp/audit.php > "$recovery/audit-before.json"
wp -e IP_CLAUDE_TILE_APPLY=yes wpcli eval-file /tmp/tile.php > "$recovery/brief-after.json"
wp wpcli eval-file /tmp/audit.php > "$recovery/audit-after.json"
before=$(docker exec -i instapass-wordpress-1 php -r '$a=json_decode(file_get_contents("php://stdin"),true);echo $a["products"]["hash"]."|".$a["orders"]["hash"];' < "$recovery/audit-before.json")
after=$(docker exec -i instapass-wordpress-1 php -r '$a=json_decode(file_get_contents("php://stdin"),true);echo $a["products"]["hash"]."|".$a["orders"]["hash"];' < "$recovery/audit-after.json")
test "$before" = "$after"
cat "$recovery/brief-after.json"
printf '\nProduct price/stock and order fingerprints unchanged. Recovery: %s\n' "$recovery"
