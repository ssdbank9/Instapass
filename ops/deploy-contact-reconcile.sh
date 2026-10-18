#!/usr/bin/env bash
set -euo pipefail
test ! -e /opt/instapass/wordpress/wp-content/mu-plugins/instapass-contact.php
wpcli=wordpress@sha256:36243da432a0f0a3b56dc3240ecee9b159108d71305e7c064d987a20a465bfde
chmod a+r /home/ubuntu/instapass-contact.php
docker run --rm --network none --entrypoint php -v /home/ubuntu/instapass-contact.php:/tmp/contact.php:ro "$wpcli" -l /tmp/contact.php
install -d -m 755 /opt/instapass/wordpress/wp-content/mu-plugins
install -m 644 /home/ubuntu/instapass-contact.php /opt/instapass/wordpress/wp-content/mu-plugins/instapass-contact.php
docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v /home/ubuntu/notifications-live-audit.php:/tmp/audit.php:ro wpcli eval-file /tmp/audit.php
sha256sum /opt/instapass/wordpress/wp-content/mu-plugins/instapass-contact.php
