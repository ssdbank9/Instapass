#!/usr/bin/env bash
set -euo pipefail
umask 077
chmod -R a+rX /home/ubuntu/notifications-010-code
stamp=$(date -u +%Y%m%dT%H%M%SZ)
work=/opt/instapass/recovery/notifications-010-test-$stamp
mkdir -p "$work"
printf '%s\n' "$work" > /home/ubuntu/notifications-010-state
net=instapass-notifications-010-$stamp
db=instapass-notifications-db-$stamp
cli=instapass-notifications-cli-$stamp
printf '%s\n%s\n%s\n' "$net" "$db" "$cli" > "$work/resources"
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
mkdir -p "$work/plugins"
cp -a /opt/instapass/wordpress/wp-content/plugins/. "$work/plugins/"
cp -a /home/ubuntu/notifications-010-code/. "$work/plugins/instapass-notifications/"
chmod -R a+rX "$work/plugins"
wpcli=wordpress@sha256:36243da432a0f0a3b56dc3240ecee9b159108d71305e7c064d987a20a465bfde
docker run --rm --network none --entrypoint sh -v /home/ubuntu/notifications-010-code:/tmp/next:ro "$wpcli" -c 'find /tmp/next -name "*.php" -exec php -l {} \;' > "$work/php-lint.txt"
cat "$work/php-lint.txt"
set +e
docker run --name "$cli" --network "$net" --env-file "$work/cli.env" --workdir /var/www/html -v /opt/instapass/wordpress:/var/www/html:ro -v "$work/plugins":/var/www/html/wp-content/plugins:ro -v /home/ubuntu/notifications-integration.php:/tmp/test.php:ro "$wpcli" eval-file /tmp/test.php > "$work/result.json" 2> "$work/php-stderr.txt"
status=$?
set -e
cat "$work/php-stderr.txt"
cat "$work/result.json"
printf '\nIntegration exit: %s\n' "$status"
exit "$status"
