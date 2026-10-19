#!/usr/bin/env bash
set -euo pipefail
umask 077
base=/opt/instapass/wordpress/wp-content/plugins
stage=/home/ubuntu/owner-pricing-code
manifest=/home/ubuntu/owner-pricing-manifest.tsv
# All source guards are checked before any production file changes.
while IFS=$'\t' read -r group rel old new;do
 case "$group" in control) plugin=instapass-control-center;;tenor) plugin=instapass-tenor-offers;;*)exit 2;;esac
 [[ "$rel" =~ ^[a-zA-Z0-9_./-]+$ && "$rel" != *..* && "$rel" != /* ]] || exit 2
 test "$(sha256sum "$base/$plugin/$rel"|cut -d' ' -f1)" = "$old"
 test "$(sha256sum "$stage/$group/$rel"|cut -d' ' -f1)" = "$new"
done < "$manifest"
r=/opt/instapass/recovery/owner-pricing-$(date -u +%Y%m%dT%H%M%SZ)
mkdir -p "$r"
cp -a "$base/instapass-control-center" "$r/control"
cp -a "$base/instapass-tenor-offers" "$r/tenor"
cp "$manifest" "$r/manifest.tsv"
wp(){ docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v /home/ubuntu/owner-pricing-audit.php:/tmp/audit.php:ro wpcli eval-file /tmp/audit.php; }
wp > "$r/before.json"
while IFS=$'\t' read -r group rel old new;do
 case "$group" in control)plugin=instapass-control-center;;tenor)plugin=instapass-tenor-offers;;esac
 install -o 33 -g 33 -m 644 "$stage/$group/$rel" "$base/$plugin/$rel.next"
 if [[ "$rel" == *.php ]];then docker exec instapass-wordpress-1 php -l "/var/www/html/wp-content/plugins/$plugin/$rel.next";fi
done < "$manifest"
while IFS=$'\t' read -r group rel old new;do
 case "$group" in control)plugin=instapass-control-center;;tenor)plugin=instapass-tenor-offers;;esac
 mv "$base/$plugin/$rel.next" "$base/$plugin/$rel"
 test "$(sha256sum "$base/$plugin/$rel"|cut -d' ' -f1)" = "$new"
done < "$manifest"
wp > "$r/after.json"
cmp "$r/before.json" "$r/after.json"
cat "$r/after.json"
printf '\nOwner pricing controls published. Entire catalog, historical orders and financial settings unchanged. Recovery: %s\n' "$r"
