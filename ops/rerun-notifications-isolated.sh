#!/usr/bin/env bash
set -euo pipefail
work=$(cat /home/ubuntu/notifications-010-state)
[[ "$work" =~ ^/opt/instapass/recovery/notifications-010-test-[0-9]{8}T[0-9]{6}Z$ ]] || exit 2
net=$(sed -n '1p' "$work/resources")
cli=$(sed -n '3p' "$work/resources")
[[ "$cli" =~ ^instapass-notifications-cli-[0-9]{8}T[0-9]{6}Z$ ]] || exit 2
docker rm -f "$cli" >/dev/null 2>&1 || true
mkdir -p "$work/plugins"
cp -a /opt/instapass/wordpress/wp-content/plugins/. "$work/plugins/"
cp -a /home/ubuntu/notifications-010-code/. "$work/plugins/instapass-notifications/"
chmod -R a+rX "$work/plugins"
wpcli=wordpress@sha256:36243da432a0f0a3b56dc3240ecee9b159108d71305e7c064d987a20a465bfde
docker run --rm --network none --entrypoint sh -v /home/ubuntu/notifications-010-code:/tmp/next:ro "$wpcli" -c 'find /tmp/next -name "*.php" -exec php -l {} \;'
docker run --name "$cli" --network "$net" --env-file "$work/cli.env" --workdir /var/www/html -v /opt/instapass/wordpress:/var/www/html:ro -v "$work/plugins":/var/www/html/wp-content/plugins:ro -v /home/ubuntu/notifications-integration.php:/tmp/test.php:ro "$wpcli" eval-file /tmp/test.php > "$work/result.json" 2> "$work/php-stderr.txt" || { cat "$work/php-stderr.txt";cat "$work/result.json";exit 1; }
cat "$work/php-stderr.txt"
cat "$work/result.json"
