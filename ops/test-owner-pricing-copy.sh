#!/usr/bin/env bash
set -euo pipefail
work=$(cat /home/ubuntu/owner-pricing-state)
[[ "$work" =~ ^/opt/instapass/recovery/owner-pricing-test-[0-9]{8}T[0-9]{6}Z$ ]] || exit 2
mapfile -t r < "$work/resources"
wpcli=wordpress@sha256:36243da432a0f0a3b56dc3240ecee9b159108d71305e7c064d987a20a465bfde
docker run --rm --network "${r[0]}" --env-file "$work/cli.env" --workdir /var/www/html -v /opt/instapass/wordpress:/var/www/html:ro -v /home/ubuntu/owner-pricing-code/control:/var/www/html/wp-content/plugins/instapass-control-center:ro -v /home/ubuntu/owner-pricing-code/tenor:/var/www/html/wp-content/plugins/instapass-tenor-offers:ro -v /home/ubuntu/owner-pricing-copy-integration.php:/tmp/test.php:ro "$wpcli" eval-file /tmp/test.php > "$work/copy-result.json"
cat "$work/copy-result.json"
