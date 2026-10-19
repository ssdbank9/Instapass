#!/usr/bin/env bash
set -euo pipefail
umask 077
stamp=$(date -u +%Y%m%dT%H%M%SZ)
work=/opt/instapass/recovery/currency-test-$stamp
mkdir -p "$work"
net=instapass-currency-test-$stamp
db=instapass-currency-db-$stamp
cli=instapass-currency-cli-$stamp
cleanup(){ docker rm -f "$cli" >/dev/null 2>&1 || true; docker stop "$db" >/dev/null 2>&1 || true; docker network rm "$net" >/dev/null 2>&1 || true; }
trap cleanup EXIT
password=$(openssl rand -hex 24)
printf 'MYSQL_ROOT_PASSWORD=%s\n' "$password" > "$work/db.env"
printf 'WORDPRESS_DB_HOST=%s:3306\nWORDPRESS_DB_USER=root\nWORDPRESS_DB_PASSWORD=%s\nINSTAPASS_ISOLATED_TEST=yes\n' "$db" "$password" > "$work/cli.env"
image=$(docker inspect --format '{{.Image}}' instapass-db-1)
docker network create --internal "$net" > /dev/null
docker run -d --rm --name "$db" --network "$net" --memory 768m --tmpfs /var/lib/mysql:rw,size=512m --env-file "$work/db.env" "$image" > /dev/null
ready=0
for i in $(seq 1 90); do if docker exec "$db" sh -c 'mariadb -uroot -p"$MYSQL_ROOT_PASSWORD" -N -e "SELECT 1"' >/dev/null 2>&1; then ready=1; break; fi; sleep 1; done
if [ "$ready" -ne 1 ]; then echo 'Isolated database initialization failed';exit 1;fi
docker exec instapass-db-1 sh -c 'exec mariadb-dump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction --quick --databases "$MYSQL_DATABASE"' > "$work/clone.sql"
docker exec -i "$db" sh -c 'exec mariadb -uroot -p"$MYSQL_ROOT_PASSWORD"' < "$work/clone.sql"
# Use production's own configured DB name, without exposing its value/password.
dbname=$(docker exec instapass-db-1 sh -c 'printf "%s" "$MYSQL_DATABASE"')
printf 'WORDPRESS_DB_NAME=%s\n' "$dbname" >> "$work/cli.env"
wpcli=wordpress@sha256:36243da432a0f0a3b56dc3240ecee9b159108d71305e7c064d987a20a465bfde
docker run --name "$cli" --network "$net" --env-file "$work/cli.env" --workdir /var/www/html -v /opt/instapass/wordpress:/var/www/html:ro -v /home/ubuntu/control-currency-code:/tmp/control-next:ro -v /home/ubuntu/control-center-currency-integration.php:/tmp/test.php:ro "$wpcli" --skip-plugins=instapass-control-center eval-file /tmp/test.php > "$work/result.json"
cat "$work/result.json"
docker cp "$cli":/tmp/currency-owner.html "$work/currency-owner.html"
docker rm "$cli" > /dev/null
printf '\nIsolated currency/checkout checks passed. No ports or external network access.\n'
