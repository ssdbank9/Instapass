#!/usr/bin/env bash
set -euo pipefail
umask 077
candidate=/home/ubuntu/instapass-access-safety-release
root=/opt/instapass/wordpress/wp-content/mu-plugins
php_live=$root/instapass-access-safety.php
js_live=$root/instapass-access-safety.js
wpcli=wordpress@sha256:36243da432a0f0a3b56dc3240ecee9b159108d71305e7c064d987a20a465bfde
test "$(sha256sum "$php_live" | cut -d ' ' -f1)" = f2ee0062bc4017089aa37c2df9d7e4304d79954731e75e115d5216a59759e123
test "$(sha256sum "$js_live" | cut -d ' ' -f1)" = cf5929fd0f32f34fb5f1aee62afadb4727fc96172c4bf13c6064f20bbace2e25
test "$(sha256sum "$candidate/instapass-access-safety.php" | cut -d ' ' -f1)" = a60d736381ae5951a7c45b528eff05330244bfd5e43b7c7a1fa6d52db822a600
test "$(sha256sum "$candidate/instapass-access-safety.js" | cut -d ' ' -f1)" = 99205e802f72884d01bb356d4924e1ffea1db21f357fae7786a8be874b285dc9
docker run --rm --network none --entrypoint php -v "$candidate/instapass-access-safety.php":/tmp/access-safety.php:ro "$wpcli" -l /tmp/access-safety.php
audit=$candidate/audit-guest-commerce.php
before=$candidate/commerce-patch-before.json
after=$candidate/commerce-patch-after.json
docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v "$audit":/tmp/audit.php:ro wpcli eval-file /tmp/audit.php > "$before"
recovery=/opt/instapass/recovery/access-safety-dismissal-$(date -u +%Y%m%dT%H%M%SZ)
install -d -m 700 "$recovery"
cp "$php_live" "$recovery/instapass-access-safety.php.before"
cp "$js_live" "$recovery/instapass-access-safety.js.before"
success=0
rollback() {
  status=$?
  if [[ "$success" != 1 ]]; then
    install -m 644 "$recovery/instapass-access-safety.php.before" "$php_live"
    install -m 644 "$recovery/instapass-access-safety.js.before" "$js_live"
  fi
  exit "$status"
}
trap rollback EXIT
install -m 644 "$candidate/instapass-access-safety.js" "$js_live.next"
mv "$js_live.next" "$js_live"
install -m 644 "$candidate/instapass-access-safety.php" "$php_live.next"
mv "$php_live.next" "$php_live"
if ! curl --fail --silent --show-error --max-time 20 https://instapass.online/ -o /dev/null; then
  echo 'Homepage health failed; exact prior PHP and JS files will be restored.' >&2
  exit 1
fi
docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v "$audit":/tmp/audit.php:ro wpcli eval-file /tmp/audit.php > "$after"
if ! cmp -s "$before" "$after"; then
  echo 'Commerce audit changed; exact prior PHP and JS files will be restored.' >&2
  exit 1
fi
success=1
sha256sum "$php_live" "$js_live"
printf 'Commerce audit: unchanged\nRecovery: %s\n' "$recovery"
