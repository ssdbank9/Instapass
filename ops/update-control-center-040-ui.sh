#!/usr/bin/env bash
set -euo pipefail
umask 077
source=/home/ubuntu/bulk-040-code
target=/opt/instapass/wordpress/wp-content/plugins/instapass-control-center
grep -q 'Version: 0.4.0' "$target/instapass-control-center.php"
test "$(sha256sum "$target/includes/bulk-pricing.php" | cut -d ' ' -f1)" = 7401147384dfcf37dbb8e883144d4ae2ba964ecbeff616a86e536480313e06e3
test "$(sha256sum "$target/assets/bulk-pricing.js" | cut -d ' ' -f1)" = 0c3773e26300205c9edb420bb1791b52f5b241cd0448809076c2dc2748d35532
test "$(sha256sum "$source/includes/bulk-pricing.php" | cut -d ' ' -f1)" = cca5633dad5aece908c3a4195428646e7330eac98a14d29c179b79ff86da4b57
test "$(sha256sum "$source/assets/bulk-pricing.js" | cut -d ' ' -f1)" = 43b6ff99f63b45f39208716aaca8ac4e32b61b54160102c85d78da3ae48dd87f
backup=/opt/instapass/recovery/control-center-before-040-ui-$(date -u +%Y%m%dT%H%M%SZ).tar.gz
tar -czf "$backup" -C /opt/instapass/wordpress/wp-content/plugins instapass-control-center
sha256sum "$backup"
install -o 33 -g 33 -m 644 "$source/includes/bulk-pricing.php" "$target/includes/bulk-pricing.next"
docker exec instapass-wordpress-1 php -l /var/www/html/wp-content/plugins/instapass-control-center/includes/bulk-pricing.next
install -o 33 -g 33 -m 644 "$source/assets/bulk-pricing.js" "$target/assets/bulk-pricing.js"
mv "$target/includes/bulk-pricing.next" "$target/includes/bulk-pricing.php"
touch "$target/includes/bulk-pricing.php"
sha256sum "$target/includes/bulk-pricing.php" "$target/assets/bulk-pricing.js"
