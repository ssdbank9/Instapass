#!/usr/bin/env bash
set -euo pipefail
live=/opt/instapass/wordpress/wp-content/mu-plugins/instapass-contact.php
candidate=/home/ubuntu/instapass-contact-public-email.php
expected=7731cff49e33202a46bc9496dcea6b38a7d6daa97ff32a10d475e6b0617ac8fd
test "$(sha256sum "$live" | cut -d ' ' -f1)" = "$expected"
test "$(sha256sum "$candidate" | cut -d ' ' -f1)" = 9506ce03a2aa8fd5265cb468574097f1e5437ed11818163b55396308925ddf6e
chmod a+r "$candidate"
docker run --rm --network none --entrypoint php -v "$candidate":/tmp/contact.php:ro wordpress@sha256:36243da432a0f0a3b56dc3240ecee9b159108d71305e7c064d987a20a465bfde -l /tmp/contact.php
recovery=/opt/instapass/recovery/public-contact-email-$(date -u +%Y%m%dT%H%M%SZ)
install -d -m 700 "$recovery"
cp -a "$live" "$recovery/instapass-contact.php"
install -m 644 "$candidate" "${live}.next"
mv "${live}.next" "$live"
printf 'Recovery: %s\n' "$recovery"
sha256sum "$live"
