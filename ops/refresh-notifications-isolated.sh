#!/usr/bin/env bash
set -euo pipefail
docker rm -f instapass-notifications-web >/dev/null
bash /home/ubuntu/rerun-notifications-isolated.sh
bash /home/ubuntu/serve-notifications-isolated.sh
