#!/usr/bin/env bash
set -euo pipefail
umask 077
chmod -R a+rX /home/ubuntu/instapass-guest-orders
chmod a+r /home/ubuntu/guest-manual-payments.php /home/ubuntu/guest-payment-confirmation.php /home/ubuntu/guest-commerce-setup.php
stamp=$(date -u +%Y%m%dT%H%M%SZ)
work=/opt/instapass/recovery/guest-commerce-test-$stamp
mkdir -p "$work"
printf '%s\n' "$work" > /home/ubuntu/guest-commerce-state
net=instapass-guest-test-$stamp
db=instapass-guest-db-$stamp
printf '%s\n%s\n' "$net" "$db" > "$work/resources"
password=$(openssl rand -hex 24)
printf 'MYSQL_ROOT_PASSWORD=%s\n' "$password" > "$work/db.env"
printf 'WORDPRESS_DB_HOST=%s:3306\nWORDPRESS_DB_USER=root\nWORDPRESS_DB_PASSWORD=%s\nINSTAPASS_ISOLATED_TEST=yes\n' "$db" "$password" > "$work/cli.env"
image=$(docker inspect --format '{{.Image}}' instapass-db-1)
docker network create --internal "$net" >/dev/null
docker run -d --rm --name "$db" --network "$net" --memory 768m --tmpfs /var/lib/mysql:rw,size=512m --env-file "$work/db.env" "$image" >/dev/null
ready=0
for i in $(seq 1 90); do if docker exec "$db" sh -c 'mariadb -uroot -p"$MYSQL_ROOT_PASSWORD" -N -e "SELECT 1"' >/dev/null 2>&1;then ready=1;break;fi;sleep 1;done
test "$ready" -eq 1
docker exec instapass-db-1 sh -c 'exec mariadb-dump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction --quick --databases "$MYSQL_DATABASE"' > "$work/clone.sql"
docker exec -i "$db" sh -c 'exec mariadb -uroot -p"$MYSQL_ROOT_PASSWORD"' < "$work/clone.sql"
dbname=$(docker exec instapass-db-1 sh -c 'printf "%s" "$MYSQL_DATABASE"')
printf 'WORDPRESS_DB_NAME=%s\nWORDPRESS_CONFIG_EXTRA=define("DISABLE_WP_CRON",true);define("WPMU_PLUGIN_DIR","/tmp/mu");\n' "$dbname" >> "$work/cli.env"
mkdir -p "$work/plugins" "$work/mu"
cp -a /opt/instapass/wordpress/wp-content/plugins/. "$work/plugins/"
cp -a /home/ubuntu/instapass-guest-orders "$work/plugins/"
cp /home/ubuntu/guest-commerce-fixture.php "$work/mu/fixture.php"
chmod -R a+rX "$work/plugins" "$work/mu"
wpcli=wordpress@sha256:36243da432a0f0a3b56dc3240ecee9b159108d71305e7c064d987a20a465bfde
run_guest(){ docker run --tmpfs /var/www/html/wp-content/uploads:rw,mode=1777 --network "$net" --env-file "$work/cli.env" -v /opt/instapass/wordpress:/var/www/html:ro -v "$work/plugins":/var/www/html/wp-content/plugins:ro -v "$work/mu":/tmp/mu:ro -v /home/ubuntu/guest-manual-payments.php:/var/www/html/wp-content/themes/instapass/inc/manual-payments.php:ro -v /home/ubuntu/guest-payment-confirmation.php:/var/www/html/wp-content/themes/instapass/inc/payment-confirmation.php:ro "$@"; }
docker run --rm --network none --entrypoint php -v /home/ubuntu/instapass-guest-orders:/tmp/code:ro "$wpcli" -l /tmp/code/instapass-guest-orders.php
docker run --rm --network none --entrypoint php -v /home/ubuntu/guest-payment-confirmation.php:/tmp/check.php:ro "$wpcli" -l /tmp/check.php
run_guest --name instapass-guest-setup --workdir /var/www/html -v /home/ubuntu/guest-commerce-setup.php:/tmp/setup.php:ro "$wpcli" eval-file /tmp/setup.php > "$work/integration.json"
cat "$work/integration.json"
docker cp instapass-guest-setup:/tmp/guest-commerce-private.json /home/ubuntu/guest-commerce-private.json
chown ubuntu:ubuntu /home/ubuntu/guest-commerce-private.json
chmod 600 /home/ubuntu/guest-commerce-private.json
docker rm instapass-guest-setup >/dev/null
image=$(docker inspect --format '{{.Image}}' instapass-wordpress-1)
run_guest -d --name instapass-guest-web --memory 768m "$image" >/dev/null
ip=$(docker inspect --format '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' instapass-guest-web)
printf '%s\n' "$ip" > /home/ubuntu/guest-commerce-web-ip
chmod 644 /home/ubuntu/guest-commerce-web-ip
printf '\nIsolated app ready; outbound network blocked; mail intercepted.\n'
