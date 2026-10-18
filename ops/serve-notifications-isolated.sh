#!/usr/bin/env bash
set -euo pipefail
work=$(cat /home/ubuntu/notifications-010-state)
[[ "$work" =~ ^/opt/instapass/recovery/notifications-010-test-[0-9]{8}T[0-9]{6}Z$ ]] || exit 2
net=$(sed -n '1p' "$work/resources")
wpcli=wordpress@sha256:36243da432a0f0a3b56dc3240ecee9b159108d71305e7c064d987a20a465bfde
docker run --name instapass-notifications-setup --network "$net" --env-file "$work/cli.env" --workdir /var/www/html -v /opt/instapass/wordpress:/var/www/html:ro -v "$work/plugins":/var/www/html/wp-content/plugins:ro -v /home/ubuntu/notifications-browser-setup.php:/tmp/setup.php:ro "$wpcli" eval-file /tmp/setup.php
docker cp instapass-notifications-setup:/tmp/notifications-browser-private.json /home/ubuntu/notifications-browser-private.json
chown ubuntu:ubuntu /home/ubuntu/notifications-browser-private.json
chmod 600 /home/ubuntu/notifications-browser-private.json
docker rm instapass-notifications-setup >/dev/null
mkdir -p "$work/mu"
cp /home/ubuntu/notifications-fixture.php "$work/mu/notifications-fixture.php"
chmod a+rx "$work/mu";chmod a+r "$work/mu/notifications-fixture.php"
image=$(docker inspect --format '{{.Image}}' instapass-wordpress-1)
docker run -d --name instapass-notifications-web --network "$net" --memory 768m --env-file "$work/cli.env" --env "WORDPRESS_CONFIG_EXTRA=define('DISABLE_WP_CRON',true);define('WPMU_PLUGIN_DIR','/tmp/mu');" -v /opt/instapass/wordpress:/var/www/html:ro -v "$work/plugins":/var/www/html/wp-content/plugins:ro -v "$work/mu":/tmp/mu:ro "$image" >/dev/null
sleep 2
ip=$(docker inspect --format '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' instapass-notifications-web)
printf '%s\n' "$ip" > /home/ubuntu/notifications-web-ip
chmod 644 /home/ubuntu/notifications-web-ip
curl -sS -o /dev/null -w 'Isolated login HTTP %{http_code}\n' "http://$ip/wp-login.php"
