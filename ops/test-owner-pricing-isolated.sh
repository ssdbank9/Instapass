#!/usr/bin/env bash
set -euo pipefail
umask 077
chmod -R a+rX /home/ubuntu/owner-pricing-code
stamp=$(date -u +%Y%m%dT%H%M%SZ)
work=/opt/instapass/recovery/owner-pricing-test-$stamp
mkdir -p "$work"
printf '%s\n' "$work" > /home/ubuntu/owner-pricing-state
net=instapass-owner-pricing-$stamp
db=instapass-owner-db-$stamp
cli=instapass-owner-cli-$stamp
web=instapass-owner-web-$stamp
printf '%s\n%s\n%s\n%s\n' "$net" "$db" "$cli" "$web" > "$work/resources"
password=$(openssl rand -hex 24)
printf 'MYSQL_ROOT_PASSWORD=%s\n' "$password" > "$work/db.env"
printf 'WORDPRESS_DB_HOST=%s:3306\nWORDPRESS_DB_USER=root\nWORDPRESS_DB_PASSWORD=%s\nINSTAPASS_ISOLATED_TEST=yes\n' "$db" "$password" > "$work/cli.env"
image=$(docker inspect --format '{{.Image}}' instapass-db-1)
docker network create --internal "$net" >/dev/null
docker run -d --rm --name "$db" --network "$net" --memory 768m --tmpfs /var/lib/mysql:rw,size=512m --env-file "$work/db.env" "$image" >/dev/null
ready=0
for i in $(seq 1 90);do if docker exec "$db" sh -c 'mariadb -uroot -p"$MYSQL_ROOT_PASSWORD" -N -e "SELECT 1"' >/dev/null 2>&1;then ready=1;break;fi;sleep 1;done
test "$ready" -eq 1
docker exec instapass-db-1 sh -c 'exec mariadb-dump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction --quick --databases "$MYSQL_DATABASE"' > "$work/clone.sql"
docker exec -i "$db" sh -c 'exec mariadb -uroot -p"$MYSQL_ROOT_PASSWORD"' < "$work/clone.sql"
dbname=$(docker exec instapass-db-1 sh -c 'printf "%s" "$MYSQL_DATABASE"')
printf 'WORDPRESS_DB_NAME=%s\n' "$dbname" >> "$work/cli.env"
wpcli=wordpress@sha256:36243da432a0f0a3b56dc3240ecee9b159108d71305e7c064d987a20a465bfde
mounts=(-v /opt/instapass/wordpress:/var/www/html:ro -v /home/ubuntu/owner-pricing-code/control:/var/www/html/wp-content/plugins/instapass-control-center:ro -v /home/ubuntu/owner-pricing-code/control:/tmp/control-next:ro -v /home/ubuntu/owner-pricing-code/tenor:/var/www/html/wp-content/plugins/instapass-tenor-offers:ro)
docker run --rm --network none --entrypoint sh -v /home/ubuntu/owner-pricing-code:/tmp/next:ro "$wpcli" -c 'find /tmp/next -name "*.php" -exec php -l {} \;' > "$work/lint.txt"
cat "$work/lint.txt"
docker run --name "$cli" --network "$net" --env-file "$work/cli.env" --workdir /var/www/html "${mounts[@]}" -v /home/ubuntu/owner-pricing-integration.php:/tmp/test.php:ro "$wpcli" --skip-plugins=instapass-control-center eval-file /tmp/test.php > "$work/result.json"
cat "$work/result.json"
docker cp "$cli":/tmp/bulk-browser-private.json "$work/browser-private.json"
docker rm "$cli" >/dev/null
docker run --rm --network "$net" --env-file "$work/cli.env" --workdir /var/www/html "${mounts[@]}" -v /home/ubuntu/control-center-currency-integration.php:/tmp/test.php:ro "$wpcli" --skip-plugins=instapass-control-center eval-file /tmp/test.php > "$work/currency-result.json"
cat "$work/currency-result.json"
docker run --rm --network "$net" --env-file "$work/cli.env" --workdir /var/www/html "${mounts[@]}" -v /home/ubuntu/owner-pricing-browser-setup.php:/tmp/setup.php:ro "$wpcli" eval-file /tmp/setup.php
cp "$work/browser-private.json" /home/ubuntu/owner-pricing-private.json
chown ubuntu:ubuntu /home/ubuntu/owner-pricing-private.json
chmod 600 /home/ubuntu/owner-pricing-private.json
mkdir -p "$work/mu"
if [ -d /opt/instapass/wordpress/wp-content/mu-plugins ];then cp -a /opt/instapass/wordpress/wp-content/mu-plugins/. "$work/mu/";fi
install -m 644 /home/ubuntu/owner-pricing-no-mail.php "$work/mu/owner-pricing-no-mail.php"
chmod -R a+rX "$work/mu"
image=$(docker inspect --format '{{.Image}}' instapass-wordpress-1)
docker run -d --name "$web" --network "$net" --memory 768m --env-file "$work/cli.env" --env "WORDPRESS_CONFIG_EXTRA=define('DISABLE_WP_CRON',true);" "${mounts[@]}" -v "$work/mu":/var/www/html/wp-content/mu-plugins:ro "$image" >/dev/null
ip=$(docker inspect --format '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' "$web")
printf '%s\n' "$ip" > /home/ubuntu/owner-pricing-web-ip
chmod 644 /home/ubuntu/owner-pricing-web-ip
printf '\nIsolated owner-control clone ready: %s\n' "$work"
