#!/usr/bin/env bash
set -euo pipefail
umask 077
next=/home/ubuntu/offer-controls-next
live=/opt/instapass/wordpress
audit(){ docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v "$next/audit.php":/tmp/audit.php:ro wpcli eval-file /tmp/audit.php; }
validate(){
 test "$(wc -l < "$next/deploy.tsv")" -eq 12
 while IFS=$'\t' read -r src dest expected;do
  [[ "$src" =~ ^(plugins/instapass-(control-center|tenor-offers|guest-orders)/[a-zA-Z0-9_./-]+|theme/(promotions|product-art|page-faq)\.php)$ ]] || exit 2
  [[ "$dest" =~ ^wp-content/(plugins/instapass-(control-center|tenor-offers|guest-orders)/[a-zA-Z0-9_./-]+|themes/instapass/(inc/(promotions|product-art)|patterns/page-faq)\.php)$ ]] || exit 2
  test "$(realpath -m "$live/$dest")" = "$live/$dest"
  test "$(sha256sum "$next/$src" | cut -d' ' -f1)" = "$expected"
 done < "$next/deploy.tsv"
}
validate
if [ "${1:-}" = preflight ];then
 work=/opt/instapass/recovery/offer-controls-deploy-$(date -u +%Y%m%dT%H%M%SZ)
 mkdir -p "$work"
 printf '%s\n' "$work" > /home/ubuntu/offer-controls-release-state
 cp "$next/deploy.tsv" "$work/candidate.tsv"
 while IFS=$'\t' read -r src dest expected;do
  if [ -f "$live/$dest" ];then hash=$(sha256sum "$live/$dest" | cut -d' ' -f1);mkdir -p "$work/$(dirname "$dest")";cp -a "$live/$dest" "$work/$dest";else hash=absent;fi
  printf '%s\t%s\n' "$dest" "$hash" >> "$work/original.tsv"
 done < "$next/deploy.tsv"
 audit > "$work/before.json"
 cat "$work/before.json"
 echo "Recovery and source guard: $work"
elif [ "${1:-}" = apply ];then
 work=$(cat /home/ubuntu/offer-controls-release-state)
 [[ "$work" =~ ^/opt/instapass/recovery/offer-controls-deploy-[0-9]{8}T[0-9]{6}Z$ ]] || exit 2
 cmp "$next/deploy.tsv" "$work/candidate.tsv"
 while IFS=$'\t' read -r dest expected;do if [ "$expected" = absent ];then test ! -e "$live/$dest";else test "$(sha256sum "$live/$dest" | cut -d' ' -f1)" = "$expected";fi;done < "$work/original.tsv"
 audit > "$work/before-apply.json"
 while IFS=$'\t' read -r src dest expected;do
  install -o www-data -g www-data -m 0644 "$next/$src" "$live/$dest.next"
  if [[ "$dest" == *.php ]];then docker exec instapass-wordpress-1 php -l "/var/www/html/$dest.next";fi
 done < "$next/deploy.tsv"
 main=wp-content/plugins/instapass-control-center/instapass-control-center.php
 while IFS=$'\t' read -r src dest expected;do if [ "$dest" != "$main" ];then mv "$live/$dest.next" "$live/$dest";fi;done < "$next/deploy.tsv"
 mv "$live/$main.next" "$live/$main"
 while IFS=$'\t' read -r src dest expected;do test "$(sha256sum "$live/$dest" | cut -d' ' -f1)" = "$expected";done < "$next/deploy.tsv"
 audit > "$work/after.json"
 cmp "$work/before-apply.json" "$work/after.json"
 cat "$work/after.json"
 echo 'All 12 source hashes match. Catalog, orders, coupons, rate and payment settings unchanged.'
else echo 'Use preflight or apply';exit 2;fi
