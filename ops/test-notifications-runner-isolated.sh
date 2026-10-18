#!/usr/bin/env bash
set -euo pipefail
work=$(cat /home/ubuntu/notifications-010-state)
[[ "$work" =~ ^/opt/instapass/recovery/notifications-010-test-[0-9]{8}T[0-9]{6}Z$ ]] || exit 2
net=$(sed -n '1p' "$work/resources")
wpcli=wordpress@sha256:36243da432a0f0a3b56dc3240ecee9b159108d71305e7c064d987a20a465bfde
docker run --rm --network "$net" --env-file "$work/cli.env" --workdir /var/www/html -v /opt/instapass/wordpress:/var/www/html:ro -v "$work/plugins":/var/www/html/wp-content/plugins:ro -v /home/ubuntu/notifications-runner-test.php:/tmp/test.php:ro -v /home/ubuntu/notifications-runner.php:/tmp/notifications-runner.php:ro "$wpcli" eval-file /tmp/test.php
