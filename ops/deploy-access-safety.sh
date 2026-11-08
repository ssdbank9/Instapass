#!/usr/bin/env bash
set -euo pipefail
umask 077

candidate=/home/ubuntu/instapass-access-safety-release
root=/opt/instapass/wordpress/wp-content/mu-plugins
php_live=$root/instapass-access-safety.php
css_live=$root/instapass-access-safety.css
js_live=$root/instapass-access-safety.js
wpcli=wordpress@sha256:36243da432a0f0a3b56dc3240ecee9b159108d71305e7c064d987a20a465bfde

test -d "$root"
test -f "$candidate/instapass-access-safety.php"
test -f "$candidate/instapass-access-safety.css"
test -f "$candidate/instapass-access-safety.js"
test -f "$candidate/audit-guest-commerce.php"
test ! -e "$php_live" && test ! -e "$css_live" && test ! -e "$js_live"
success=0
rollback_on_error() {
  status=$?
  if [[ "$success" != 1 ]]; then
    rm -f "$php_live.next" "$css_live.next" "$js_live.next" "$php_live" "$css_live" "$js_live"
  fi
  exit "$status"
}
trap rollback_on_error EXIT
echo 'F2EE0062BC4017089AA37C2DF9D7E4304D79954731E75E115D5216A59759E123  '"$candidate/instapass-access-safety.php" | sha256sum -c -
echo 'FD78C1EF41C481442ED35C932361B30DAC5DA916BED6A75E98A4637A56692A96  '"$candidate/instapass-access-safety.css" | sha256sum -c -
echo 'CF5929FD0F32F34FB5F1AEE62AFADB4727FC96172C4BF13C6064F20BBACE2E25  '"$candidate/instapass-access-safety.js" | sha256sum -c -
docker run --rm --network none --entrypoint php -v "$candidate/instapass-access-safety.php":/tmp/access-safety.php:ro "$wpcli" -l /tmp/access-safety.php

audit=/home/ubuntu/instapass-access-safety-release/audit-guest-commerce.php
before=/home/ubuntu/instapass-access-safety-release/commerce-before.json
after=/home/ubuntu/instapass-access-safety-release/commerce-after.json
chmod a+r "$audit"
docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v "$audit":/tmp/audit.php:ro wpcli eval-file /tmp/audit.php > "$before"

recovery=/opt/instapass/recovery/access-safety-deploy-$(date -u +%Y%m%dT%H%M%SZ)
install -d -m 700 "$recovery"
cp "$candidate/instapass-access-safety.php" "$recovery/instapass-access-safety.php"
cp "$candidate/instapass-access-safety.css" "$recovery/instapass-access-safety.css"
cp "$candidate/instapass-access-safety.js" "$recovery/instapass-access-safety.js"
sha256sum "$recovery/instapass-access-safety.php" "$recovery/instapass-access-safety.css" "$recovery/instapass-access-safety.js" > "$recovery/SHA256SUMS"

install -m 644 "$candidate/instapass-access-safety.css" "$css_live.next"
mv "$css_live.next" "$css_live"
install -m 644 "$candidate/instapass-access-safety.js" "$js_live.next"
mv "$js_live.next" "$js_live"
install -m 644 "$candidate/instapass-access-safety.php" "$php_live.next"
mv "$php_live.next" "$php_live"

if ! curl --fail --silent --show-error --max-time 20 https://instapass.online/ -o /dev/null; then
  rm -f "$php_live" "$css_live" "$js_live"
  echo 'Homepage health check failed; the three newly added files were removed.' >&2
  exit 1
fi
docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v "$audit":/tmp/audit.php:ro wpcli eval-file /tmp/audit.php > "$after"
if ! cmp -s "$before" "$after"; then
  echo 'Commerce audit changed; the three newly added files were removed.' >&2
  exit 1
fi

success=1
printf 'Live SHA256:\n'
sha256sum "$php_live" "$css_live" "$js_live"
printf 'Commerce audit: unchanged\nRecovery snapshot: %s\n' "$recovery"
