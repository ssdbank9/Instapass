#!/usr/bin/env bash
set -euo pipefail
work=$(cat /home/ubuntu/owner-pricing-state)
[[ "$work" =~ ^/opt/instapass/recovery/owner-pricing-test-[0-9]{8}T[0-9]{6}Z$ ]] || exit 2
for pair in result.json:native currency-result.json:currency copy-result.json:copy;do src=${pair%%:*};dst=${pair##*:};install -o ubuntu -g ubuntu -m 644 "$work/$src" "/home/ubuntu/owner-pricing-$dst-result.json";done
