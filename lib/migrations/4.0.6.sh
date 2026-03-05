#!/bin/bash
#############################################
# Cipi Migration 4.0.6 — API prerequisites
# - Sudoers rule for www-data to run cipi
# - Dedicated PHP-FPM pool for Cipi API
#############################################

set -e

# 1. Sudoers
if [[ -f /etc/sudoers.d/cipi-api ]]; then
    echo "API sudoers already present — skip"
else
    echo 'www-data ALL=(root) NOPASSWD: /usr/local/bin/cipi *' > /etc/sudoers.d/cipi-api
    chmod 440 /etc/sudoers.d/cipi-api
    echo "Added /etc/sudoers.d/cipi-api"
fi

# 2. PHP-FPM pool (only if API is already installed)
if [[ -d /opt/cipi/api ]] && [[ ! -f /etc/php/8.4/fpm/pool.d/cipi-api.conf ]]; then
    cat > /etc/php/8.4/fpm/pool.d/cipi-api.conf <<'POOL'
[cipi-api]
user = www-data
group = www-data
listen = /run/php/cipi-api.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660
pm = dynamic
pm.max_children = 10
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 4
pm.max_requests = 500
request_terminate_timeout = 300
php_admin_value[open_basedir] = /opt/cipi/api/:/tmp/:/etc/cipi/:/proc/
php_admin_value[upload_max_filesize] = 64M
php_admin_value[post_max_size] = 64M
php_admin_value[memory_limit] = 256M
php_admin_value[max_execution_time] = 300
php_admin_value[error_log] = /var/log/nginx/cipi-api-php-error.log
php_admin_flag[log_errors] = on
POOL
    systemctl restart php8.4-fpm 2>/dev/null || true
    echo "Created PHP-FPM pool cipi-api"

    # Update Nginx vhost to use dedicated socket
    if [[ -f /etc/nginx/sites-available/cipi-api ]]; then
        sed -i 's|fastcgi_pass unix:/run/php/php8.4-fpm.sock;|fastcgi_pass unix:/run/php/cipi-api.sock;|' /etc/nginx/sites-available/cipi-api
        nginx -t 2>/dev/null && systemctl reload nginx 2>/dev/null || true
        echo "Updated Nginx vhost to use cipi-api socket"
    fi
else
    echo "PHP-FPM pool cipi-api: skip (API not installed or pool exists)"
fi
