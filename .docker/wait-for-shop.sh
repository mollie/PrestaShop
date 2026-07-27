#!/bin/bash
# Usage: wait-for-shop.sh <port> <timeout-seconds>
PORT="${1:-8002}"
TIMEOUT="${2:-120}"
start=$(date +%s)
while true; do
  code=$(curl -s -o /dev/null -w '%{http_code}' "http://localhost:${PORT}/")
  if [ "$code" = "200" ] || [ "$code" = "302" ]; then
    echo "Shop on port ${PORT} is up (HTTP ${code})."
    exit 0
  fi
  now=$(date +%s)
  if [ $((now - start)) -ge "$TIMEOUT" ]; then
    echo "Timed out waiting for shop on port ${PORT} after ${TIMEOUT}s (last HTTP ${code})."
    exit 1
  fi
  sleep 3
done
