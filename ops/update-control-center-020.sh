#!/usr/bin/env bash
set -euo pipefail
umask 077
source=/home/ubuntu/instapass-control-center-next
target=/opt/instapass/wordpress/wp-content/plugins/instapass-control-center
stamp=$(date -u +%Y%m%dT%H%M%SZ)
backup=/opt/instapass/recovery/control-center-before-020-$stamp.tar.gz
test -f "$target/instapass-control-center.php"
grep -q 'Version: 0.1.0' "$target/instapass-control-center.php"
tar -czf "$backup" -C /opt/instapass/wordpress/wp-content/plugins instapass-control-center
sha256sum "$backup"
# New includes are inert until the main file is replaced last.
for file in gbp-values.php gbp-editor.php; do
 install -o 33 -g 33 -m 644 "$source/includes/$file" "$target/includes/$file"
 docker exec instapass-wordpress-1 php -l "/var/www/html/wp-content/plugins/instapass-control-center/includes/$file"
done
install -o 33 -g 33 -m 644 "$source/instapass-control-center.php" "$target/instapass-control-center.next.php"
docker exec instapass-wordpress-1 php -l /var/www/html/wp-content/plugins/instapass-control-center/instapass-control-center.next.php
mv "$target/instapass-control-center.next.php" "$target/instapass-control-center.php"
touch "$target/instapass-control-center.php"
install -o 33 -g 33 -m 644 "$source/README.md" "$target/README.md"
docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm wpcli plugin get instapass-control-center --fields=name,version,status --format=json
printf 'Rollback archive: %s\n' "$backup"
