#!/bin/bash
# Usage: wait-for-shop.sh <http-port> <timeout-seconds> [db-port]
#
# Waits for BOTH the shop and its database, then exits non-zero if either never
# turns up. Waiting on HTTP alone is not enough: Apache answers well before
# MySQL finishes its first-run initialisation, so `make seeding-customized-sql`
# would race it and die with "Lost connection to MySQL server at 'reading
# initial communication packet'".
set -u

PORT="${1:-8002}"
TIMEOUT="${2:-150}"
DB_PORT="${3:-9002}"

start=$(date +%s)

db_ready() {
  mysql -h 127.0.0.1 -P "$DB_PORT" --protocol=tcp -uroot -pprestashop \
    -e "SELECT 1" prestashop >/dev/null 2>&1
}

while true; do
  code=$(curl -s -o /dev/null -w '%{http_code}' "http://localhost:${PORT}/" || true)

  case "$code" in
    200 | 302)
      if db_ready; then
        echo "Shop on port ${PORT} is up (HTTP ${code}); MySQL on ${DB_PORT} accepts connections."
        exit 0
      fi
      ;;
  esac

  now=$(date +%s)
  if [ $((now - start)) -ge "$TIMEOUT" ]; then
    if db_ready; then db_state=ready; else db_state="not accepting connections"; fi
    echo "Timed out after ${TIMEOUT}s waiting for the shop on port ${PORT}." >&2
    echo "  last HTTP status: ${code:-none}" >&2
    echo "  MySQL on ${DB_PORT}: ${db_state}" >&2
    exit 1
  fi

  sleep 3
done
