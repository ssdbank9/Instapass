#!/usr/bin/env bash
set -euo pipefail
live=/opt/instapass/wordpress/wp-content/themes/instapass/patterns/page-refunds.php
candidate=/home/ubuntu/page-refunds-approved.php
expected=ddec78fcc818553c0fdc184e9e425bc359436e11e6df26540f80d7fb6fb3a4ca
test "$(sha256sum "$live" | cut -d ' ' -f1)" = "$expected"
test "$(sha256sum "$candidate" | cut -d ' ' -f1)" = 616c51a79dd467fbcd8be49813b0d5889ac84ad55c20693de620273e46d378c8
grep -qiE 'change of mind|changed your mind|change your mind|simply changed' "$candidate" && exit 21 || true
chmod a+r "$candidate"
docker run --rm --network none --entrypoint php -v "$candidate":/tmp/refunds.php:ro wordpress@sha256:36243da432a0f0a3b56dc3240ecee9b159108d71305e7c064d987a20a465bfde -l /tmp/refunds.php
recovery=/opt/instapass/recovery/refund-copy-20261020T$(date -u +%H%M%SZ)
install -d -m 700 "$recovery"
cp -a "$live" "$recovery/page-refunds.php"
install -m 644 "$candidate" "${live}.next"
mv "${live}.next" "$live"
printf 'Recovery: %s\n' "$recovery"
sha256sum "$live"
