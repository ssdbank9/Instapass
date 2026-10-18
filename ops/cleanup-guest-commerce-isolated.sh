#!/usr/bin/env bash
set -euo pipefail
for name in $(docker ps -a --format '{{.Names}}');do
 if [[ "$name" =~ ^instapass-guest-(db-[0-9]{8}T[0-9]{6}Z|web|setup)$ ]];then docker rm -f "$name" >/dev/null;fi
done
for name in $(docker network ls --format '{{.Name}}');do
 if [[ "$name" =~ ^instapass-guest-test-[0-9]{8}T[0-9]{6}Z$ ]];then docker network rm "$name" >/dev/null;fi
done
echo "Only named guest-commerce fixture containers/networks cleaned up."
