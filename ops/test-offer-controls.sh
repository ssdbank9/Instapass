#!/usr/bin/env bash
set -euo pipefail
umask 077
next=/home/ubuntu/offer-controls-next
stamp=$(date -u +%Y%m%dT%H%M%SZ)
work=/opt/instapass/recovery/offer-controls-test-$stamp
mkdir -p "$work"
printf '%s\n' "$work" > /home/ubuntu/offer-controls-state
trap 'rc=$?; if [ "$rc" -ne 0 ]; then cat "$work/integration.json" 2>/dev/null || true; bash "$next/cleanup.sh"; fi' EXIT
net=ip-offer-test-$stamp
db=ip-offer-db-$stamp
cli=ip-offer-cli-$stamp
web=ip-offer-web-$stamp
printf '%s\n%s\n%s\n%s\n' "$net" "$db" "$cli" "$web" > "$work/resources"
password=$(openssl rand -hex 24)
printf 'MYSQL_ROOT_PASSWORD=%s\n' "$password" > "$work/db.env"
printf 'WORDPRESS_DB_HOST=%s:3306\nWORDPRESS_DB_USER=root\nWORDPRESS_DB_PASSWORD=%s\nINSTAPASS_ISOLATED_TEST=yes\nWORDPRESS_CONFIG_EXTRA=define("DISABLE_WP_CRON",true);define("WPMU_PLUGIN_DIR","/tmp/mu");\n' "$db" "$password" > "$work/cli.env"
image=$(docker inspect --format '{{.Image}}' instapass-db-1)
docker network create --internal "$net" >/dev/null
docker run -d --rm --name "$db" --network "$net" --memory 768m --tmpfs /var/lib/mysql:rw,size=512m --env-file "$work/db.env" "$image" >/dev/null
ready=0;for i in $(seq 1 90);do if docker exec "$db" sh -c 'mariadb -uroot -p"$MYSQL_ROOT_PASSWORD" -N -e "SELECT 1"' >/dev/null 2>&1;then ready=1;break;fi;sleep 1;done;test "$ready" -eq 1
docker exec instapass-db-1 sh -c 'exec mariadb-dump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction --quick --databases "$MYSQL_DATABASE"' | docker exec -i "$db" sh -c 'exec mariadb -uroot -p"$MYSQL_ROOT_PASSWORD"'
dbname=$(docker exec instapass-db-1 sh -c 'printf "%s" "$MYSQL_DATABASE"');printf 'WORDPRESS_DB_NAME=%s\n' "$dbname" >> "$work/cli.env"
wpcli=wordpress@sha256:36243da432a0f0a3b56dc3240ecee9b159108d71305e7c064d987a20a465bfde
docker volume create ip-offer-uploads-$stamp >/dev/null
printf 'ip-offer-uploads-%s\n' "$stamp" >> "$work/resources"
docker run --rm --user 0 --network none --entrypoint sh -v ip-offer-uploads-$stamp:/media "$wpcli" -c 'chown 33:33 /media; chmod 777 /media'
mounts=(-v /opt/instapass/wordpress:/var/www/html:ro -v "$next/plugins/instapass-control-center":/var/www/html/wp-content/plugins/instapass-control-center:ro -v "$next/plugins/instapass-tenor-offers":/var/www/html/wp-content/plugins/instapass-tenor-offers:ro -v "$next/plugins/instapass-guest-orders":/var/www/html/wp-content/plugins/instapass-guest-orders:ro -v "$next/theme/promotions.php":/var/www/html/wp-content/themes/instapass/inc/promotions.php:ro -v "$next/theme/product-art.php":/var/www/html/wp-content/themes/instapass/inc/product-art.php:ro -v "$next/theme/page-faq.php":/var/www/html/wp-content/themes/instapass/patterns/page-faq.php:ro -v "$next/mu":/tmp/mu:ro --mount type=volume,src=ip-offer-uploads-$stamp,dst=/var/www/html/wp-content/uploads)
docker run --rm --network none --entrypoint sh -v "$next":/tmp/code:ro "$wpcli" -c 'find /tmp/code -name "*.php" -exec php -l {} \;'
docker run --name "$cli" --network "$net" --env-file "$work/cli.env" --workdir /var/www/html "${mounts[@]}" -v "$next/integration.php":/tmp/test.php:ro "$wpcli" eval-file /tmp/test.php > "$work/integration.json"
cat "$work/integration.json"
python3 -c 'import json,sys; assert json.load(open(sys.argv[1]))["pass_count"] >= 40' "$work/integration.json"
docker run --rm --network "$net" --env-file "$work/cli.env" --workdir /var/www/html "${mounts[@]}" -v "$next/plugins/instapass-control-center":/tmp/control-next:ro -v "$next/currency.php":/tmp/currency.php:ro "$wpcli" --skip-plugins=instapass-control-center eval-file /tmp/currency.php > "$work/currency.json"
cat "$work/currency.json"
docker cp "$cli":/tmp/offer-controls-private.json /home/ubuntu/offer-controls-private.json
chown ubuntu:ubuntu /home/ubuntu/offer-controls-private.json;chmod 600 /home/ubuntu/offer-controls-private.json;docker rm "$cli" >/dev/null
image=$(docker inspect --format '{{.Image}}' instapass-wordpress-1)
docker run -d --name "$web" --network "$net" --memory 768m --env-file "$work/cli.env" "${mounts[@]}" "$image" >/dev/null
docker inspect --format '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' "$web" > /home/ubuntu/offer-controls-web-ip
echo 'Isolated offer-controls test ready. No database export retained; database uses tmpfs; mail/network blocked.'
