#!/bin/bash
#############################################
# Cipi Migration 4.4.13 — Reclaim root-owned Laravel log files
#
# Old logrotate "create 0640 root root" left laravel-*.log owned by root,
# blocking app user reads (backup plugins, tail, etc.). copytruncate prevents
# future occurrences; this reclaims existing files.
#############################################

set -e

CIPI_CONFIG="${CIPI_CONFIG:-/etc/cipi}"
CIPI_LIB="${CIPI_LIB:-/opt/cipi/lib}"

echo "Migration 4.4.13 — Reclaim root-owned Laravel logs..."

if [[ -f "${CIPI_LIB}/common.sh" ]]; then
    source "${CIPI_LIB}/common.sh"
fi

if type ensure_app_logs_permissions &>/dev/null; then
    for home in /home/*/; do
        u=$(basename "$home")
        [[ "$u" == "cipi" ]] && continue
        id "$u" &>/dev/null || continue
        ensure_app_logs_permissions "$u"
    done
fi

echo "Migration 4.4.13 complete"
