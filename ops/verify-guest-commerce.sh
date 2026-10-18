#!/usr/bin/env bash
set -euo pipefail
chmod a+r /home/ubuntu/audit-guest-commerce.php
docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v /home/ubuntu/audit-guest-commerce.php:/tmp/audit.php:ro wpcli eval-file /tmp/audit.php > /home/ubuntu/guest-commerce-final-audit.json
sha256sum /opt/instapass/wordpress/wp-content/plugins/instapass-guest-orders/instapass-guest-orders.php /opt/instapass/wordpress/wp-content/plugins/instapass-guest-orders/guest-orders.css /opt/instapass/wordpress/wp-content/themes/instapass/inc/manual-payments.php /opt/instapass/wordpress/wp-content/themes/instapass/inc/payment-confirmation.php > /home/ubuntu/guest-commerce-final-hashes.txt
chown ubuntu:ubuntu /home/ubuntu/guest-commerce-final-audit.json /home/ubuntu/guest-commerce-final-hashes.txt
chmod 600 /home/ubuntu/guest-commerce-final-audit.json /home/ubuntu/guest-commerce-final-hashes.txt
cat /home/ubuntu/guest-commerce-final-audit.json
