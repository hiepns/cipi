#!/bin/bash
#############################################
# Cipi Migration 4.4.6 — App logs: cipi access + logrotate
#
# - Home dirs are 750 (app:app): user cipi could not traverse to read logs.
# - logrotate used "create" as root, breaking nginx/php-fpm ownership after rotate.
# - Fix: ACLs for cipi, logs dir setgid www-data, copytruncate in logrotate.
#############################################

set -e

CIPI_CONFIG="${CIPI_CONFIG:-/etc/cipi}"
CIPI_LIB="${CIPI_LIB:-/opt/cipi/lib}"

echo "Migration 4.4.6 — app logs permissions and logrotate..."

if command -v apt-get &>/dev/null; then
    apt-get install -y -qq acl 2>/dev/null || true
fi

if [[ -f "${CIPI_LIB}/common.sh" ]]; then
    # shellcheck source=/dev/null
    source "${CIPI_LIB}/common.sh"
else
    echo "  WARN: common.sh not found — ACL fixes skipped"
fi

# Repair log files left root-owned by old logrotate "create" rules
if [[ -d /home ]]; then
    while IFS= read -r -d '' f; do
        chown www-data:www-data "$f" 2>/dev/null || true
    done < <(find /home -maxdepth 4 \( -path '*/logs/nginx-access.log' -o -path '*/logs/nginx-error.log' \) -user root -print0 2>/dev/null || true)
    while IFS= read -r -d '' f; do
        au=$(echo "$f" | awk -F/ '{print $3}')
        if id "$au" &>/dev/null; then
            chown "${au}:${au}" "$f" 2>/dev/null || true
        fi
    done < <(find /home -maxdepth 4 -path '*/logs/php-fpm-*.log' -user root -print0 2>/dev/null || true)
    while IFS= read -r -d '' f; do
        au=$(echo "$f" | awk -F/ '{print $3}')
        if id "$au" &>/dev/null; then
            chown "${au}:${au}" "$f" 2>/dev/null || true
        fi
    done < <(find /home -maxdepth 4 -path '*/logs/worker-*.log' -user root -print0 2>/dev/null || true)
    while IFS= read -r -d '' f; do
        au=$(echo "$f" | awk -F/ '{print $3}')
        if id "$au" &>/dev/null; then
            chown "${au}:${au}" "$f" 2>/dev/null || true
        fi
    done < <(find /home -maxdepth 4 -path '*/logs/deploy.log' -user root -print0 2>/dev/null || true)
fi

if type ensure_app_logs_permissions &>/dev/null; then
    if [[ -f "${CIPI_CONFIG}/apps.json" ]] && command -v jq &>/dev/null; then
        while IFS= read -r app; do
            [[ -n "$app" ]] && ensure_app_logs_permissions "$app"
        done < <(vault_read apps.json 2>/dev/null | jq -r 'keys[]' 2>/dev/null || true)
    fi
    for home in /home/*/; do
        u=$(basename "$home")
        [[ "$u" == "cipi" ]] && continue
        [[ -d "${home}/logs" ]] || continue
        id "$u" &>/dev/null || continue
        ensure_app_logs_permissions "$u"
    done
else
    echo "  WARN: ensure_app_logs_permissions not available — run self-update again"
fi

# GDPR log rotation — drop broken "create" rules (use copytruncate)
if [[ -d /etc/logrotate.d ]]; then
    cat > /etc/logrotate.d/cipi-app-logs <<'EOF'
/home/*/shared/storage/logs/*.log
/home/*/logs/php-fpm-*.log
/home/*/logs/worker-*.log
/home/*/logs/deploy.log
/var/log/cipi/*.log
/var/log/cipi-queue.log {
    daily
    missingok
    rotate 365
    compress
    delaycompress
    notifempty
    copytruncate
}
EOF

    cat > /etc/logrotate.d/cipi-http-logs <<'EOF'
/home/*/logs/nginx-access.log
/home/*/logs/nginx-error.log
/var/log/nginx/*.log {
    daily
    missingok
    rotate 90
    compress
    delaycompress
    notifempty
    sharedscripts
    copytruncate
    postrotate
        [ -f /var/run/nginx.pid ] && kill -USR1 $(cat /var/run/nginx.pid) 2>/dev/null || true
    endscript
}
EOF
    echo "  logrotate configs updated (cipi-app-logs, cipi-http-logs)"
fi

echo "Migration 4.4.6 complete"
