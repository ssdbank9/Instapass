#!/usr/bin/env bash
set -euo pipefail
umask 077
work=$(cat /home/ubuntu/guest-commerce-state)
case "$work" in
  /opt/instapass/recovery/guest-commerce-test-*) ;;
  *) echo "Refusing unexpected isolated work path: $work" >&2; exit 2 ;;
esac
test -f "$work/cli.env"
test -f "$work/mu/instapass-access-safety.php"
test -f /home/ubuntu/instapass-access-safety-release/vpn-country-check.php
cp /home/ubuntu/instapass-access-safety-release/instapass-access-safety.php "$work/mu/instapass-access-safety.php"
chmod a+r "$work/mu/instapass-access-safety.php"
mapfile -t resources < "$work/resources"
net=${resources[0]:-}
[[ "$net" =~ ^instapass-guest-test-[0-9]{8}T[0-9]{6}Z$ ]]
wpcli=wordpress@sha256:36243da432a0f0a3b56dc3240ecee9b159108d71305e7c064d987a20a465bfde
docker run --rm --network none --entrypoint php -v /home/ubuntu/instapass-access-safety-release/instapass-access-safety.php:/tmp/access-safety.php:ro "$wpcli" -l /tmp/access-safety.php
docker run --rm --network "$net" --env-file "$work/cli.env" \
  -v /opt/instapass/wordpress:/var/www/html:ro \
  -v "$work/plugins":/var/www/html/wp-content/plugins:ro \
  -v "$work/mu":/tmp/mu:ro \
  -v /opt/instapass/wordpress/wp-content/themes/instapass/inc/manual-payments.php:/var/www/html/wp-content/themes/instapass/inc/manual-payments.php:ro \
  -v /opt/instapass/wordpress/wp-content/themes/instapass/inc/payment-confirmation.php:/var/www/html/wp-content/themes/instapass/inc/payment-confirmation.php:ro \
  -v /home/ubuntu/instapass-access-safety-release/vpn-country-check.php:/tmp/vpn-country-check.php:ro \
  "$wpcli" eval-file /tmp/vpn-country-check.php
