#!/bin/bash
#############################################
# Cipi Migration 4.4.5 — Install phpredis
# For servers provisioned before the redis
# PHP extension was added to the stack.
#############################################

set -e

echo "Installing phpredis for installed PHP versions..."

if ! command -v apt-get &>/dev/null; then
    echo "Not a Debian/Ubuntu system — skip"
    exit 0
fi

add-apt-repository -y ppa:ondrej/php &>/dev/null || true
apt-get update -qq

for v in 7.4 8.0 8.1 8.2 8.3 8.4 8.5; do
    if dpkg -l "php${v}-fpm" 2>/dev/null | grep -q '^ii'; then
        if dpkg -l "php${v}-redis" 2>/dev/null | grep -q '^ii'; then
            echo "  php${v}-redis already installed — skip"
        else
            apt-get install -y -qq "php${v}-redis"
            echo "  Installed php${v}-redis"
        fi
        systemctl reload "php${v}-fpm" 2>/dev/null || systemctl restart "php${v}-fpm" 2>/dev/null || true
    fi
done

echo "Migration 4.4.5 complete"
