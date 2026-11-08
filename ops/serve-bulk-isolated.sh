#!/usr/bin/env bash
set -euo pipefail
work=$(cat /home/ubuntu/bulk-040-state)
[[ "$work" =~ ^/opt/instapass/recovery/bulk-040-test-[0-9]{8}T[0-9]{6}Z$ ]] || exit 2
cp "$work/result.json" /home/ubuntu/bulk-040-result.json
chmod 644 /home/ubuntu/bulk-040-result.json
cp "$work/browser-private.json" /home/ubuntu/bulk040-private.json
chown ubuntu:ubuntu /home/ubuntu/bulk040-private.json
chmod 600 /home/ubuntu/bulk040-private.json
mapfile -t resources < "$work/resources"
net=${resources[0]};web=${resources[3]}
wpcli=wordpress@sha256:36243da432a0f0a3b56dc3240ecee9b159108d71305e7c064d987a20a465bfde
docker run --rm --network "$net" --env-file "$work/cli.env" --workdir /var/www/html -v /opt/instapass/wordpress:/var/www/html:ro -v /home/ubuntu/bulk-040-code:/var/www/html/wp-content/plugins/instapass-control-center:ro -v /home/ubuntu/control-center-bulk-browser-setup.php:/tmp/setup.php:ro "$wpcli" --skip-plugins=instapass-control-center eval-file /tmp/setup.php
image=$(docker inspect --format '{{.Image}}' instapass-wordpress-1)
docker run -d --name "$web" --network "$net" --memory 768m --env-file "$work/cli.env" --env "WORDPRESS_CONFIG_EXTRA=define('DISABLE_WP_CRON',true);" -v /opt/instapass/wordpress:/var/www/html:ro -v /home/ubuntu/bulk-040-code:/var/www/html/wp-content/plugins/instapass-control-center:ro "$image" >/dev/null
sleep 2
ip=$(docker inspect --format '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' "$web")
printf '%s\n' "$ip" > /home/ubuntu/bulk-040-web-ip
chmod 644 /home/ubuntu/bulk-040-web-ip
curl -sS -o /dev/null -w 'Isolated login HTTP %{http_code}\n' "http://$ip/wp-login.php"
