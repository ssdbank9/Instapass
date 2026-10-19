#!/usr/bin/env bash
set -euo pipefail
umask 077
base=/opt/instapass/recovery
stamp=$(date -u +%Y%m%dT%H%M%SZ)
b="$base/pre-commerce-$stamp"
mkdir -p "$b"
chmod 700 "$b"
# Keep credentials and backups root-only. No secrets are printed.
docker exec instapass-db-1 sh -c 'exec mariadb-dump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction --quick --routines --triggers --events --hex-blob --databases "$MYSQL_DATABASE"' > "$b/database.sql"
tar -czf "$b/wordpress.tar.gz" -C /opt/instapass wordpress
tar -czf "$b/config.tar.gz" -C / opt/instapass/compose.yaml opt/instapass/maintenance.yaml opt/instapass/.env etc/caddy/Caddyfile
(cd "$b" && sha256sum database.sql wordpress.tar.gz config.tar.gz > SHA256SUMS && sha256sum -c SHA256SUMS)
tar -tzf "$b/wordpress.tar.gz" > "$b/wordpress-files.txt"
tar -tzf "$b/config.tar.gz" > "$b/config-files.txt"
image=$(docker inspect --format '{{.Image}}' instapass-db-1)
name="instapass-restore-check-$stamp"
printf 'MYSQL_ROOT_PASSWORD=%s\n' "$(openssl rand -hex 24)" > "$b/restore.env"
cleanup() { docker stop "$name" >/dev/null 2>&1 || true; }
trap cleanup EXIT
docker run -d --rm --name "$name" --network none --memory 768m --tmpfs /var/lib/mysql:rw,size=512m --env-file "$b/restore.env" "$image" > "$b/restore-container-id.txt"
ready=0
for i in $(seq 1 90); do
 if docker exec "$name" sh -c 'mariadb -uroot -p"$MYSQL_ROOT_PASSWORD" -N -e "SELECT 1"' >/dev/null 2>&1; then ready=1; break; fi
 sleep 1
done
if [ "$ready" -ne 1 ]; then echo 'Restore database did not become ready'; exit 1; fi
docker exec -i "$name" sh -c 'exec mariadb -uroot -p"$MYSQL_ROOT_PASSWORD"' < "$b/database.sql"
docker exec "$name" sh -c 'exec mariadb-check -uroot -p"$MYSQL_ROOT_PASSWORD" --all-databases' > "$b/restore-integrity.txt"
db=$(docker exec instapass-db-1 sh -c 'printf "%s" "$MYSQL_DATABASE"')
# Counts use the actual restored database. No private rows are displayed.
docker exec "$name" sh -c 'exec mariadb -uroot -p"$MYSQL_ROOT_PASSWORD" -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=\"$1\""' sh "$db" > "$b/restored-table-count.txt"
printf 'Backup: %s\nRestored application tables: ' "$b"
cat "$b/restored-table-count.txt"
printf 'Integrity: '
if grep -qE 'error|corrupt|failed' "$b/restore-integrity.txt"; then echo FAILED; exit 1; else echo PASSED; fi
printf 'Restore database had no network access, no published ports, and no WordPress/email process.\n'
printf 'Database restore passed; file archive readability passed. Full application restore is a separate acceptance check.\n' | tee "$b/RESULT.txt"
