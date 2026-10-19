#!/usr/bin/env bash
set -euo pipefail
umask 077
old=$1;new=$2
[[ "$old" =~ ^[0-9a-f]{64}$ && "$new" =~ ^[0-9a-f]{64}$ ]] || exit 2
target=/opt/instapass/wordpress/wp-content/plugins/instapass-tenor-offers/monthly-pricing.php
stage=/home/ubuntu/owner-pricing-monthly-eof.php
test "$(sha256sum "$target"|cut -d' ' -f1)" = "$old"
test "$(sha256sum "$stage"|cut -d' ' -f1)" = "$new"
wpcli=wordpress@sha256:36243da432a0f0a3b56dc3240ecee9b159108d71305e7c064d987a20a465bfde
docker run --rm --network none --entrypoint php -v "$target":/tmp/before:ro -v "$stage":/tmp/after:ro "$wpcli" -r 'if(rtrim(file_get_contents("/tmp/before"))!==rtrim(file_get_contents("/tmp/after")))exit(1);'
r=/opt/instapass/recovery/owner-pricing-eof-$(date -u +%Y%m%dT%H%M%SZ)
mkdir -p "$r"
cp -a "$target" "$r/monthly-pricing.php"
install -o 33 -g 33 -m 644 "$stage" "$target.next"
docker exec instapass-wordpress-1 php -l /var/www/html/wp-content/plugins/instapass-tenor-offers/monthly-pricing.php.next
mv "$target.next" "$target"
test "$(sha256sum "$target"|cut -d' ' -f1)" = "$new"
printf 'Verified EOF-only cleanup, PHP lint and final source hash; recovery %s\n' "$r"
