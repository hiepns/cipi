#!/bin/bash
#############################################
# Cipi Migration 4.2.9
# - Add bin/composer to Deployer configs so
#   composer uses the app's PHP version
# - Update .bashrc aliases (composer, deploy)
#   to use explicit PHP paths
# - Update crontab deploy triggers
#############################################

set -e

CIPI_CONFIG="${CIPI_CONFIG:-/etc/cipi}"

source /opt/cipi/lib/vault.sh
vault_init

echo "Patching Deployer configs and shell aliases for per-app PHP..."

apps_json=$(vault_read apps.json 2>/dev/null) || apps_json="{}"
[[ "$apps_json" == "{}" ]] && { echo "  No apps — skip"; exit 0; }

echo "$apps_json" | jq -r 'to_entries[]|"\(.key)\t\(.value.php)"' | while IFS=$'\t' read -r app php_ver; do
    [[ -z "$app" || -z "$php_ver" ]] && continue
    home="/home/${app}"

    # 1. Add bin/composer to deploy.php if missing
    df="${home}/.deployer/deploy.php"
    if [[ -f "$df" ]]; then
        if ! grep -q "bin/composer" "$df" 2>/dev/null; then
            sed -i "/set('bin\/php'/a set('bin/composer', '/usr/bin/php${php_ver} /usr/local/bin/composer');" "$df"
            echo "  ${app}: added bin/composer to deploy.php"
        else
            echo "  ${app}: bin/composer already present — skip"
        fi
    fi

    # 2. Fix .bashrc composer alias (bare composer → explicit PHP)
    if [[ -f "${home}/.bashrc" ]]; then
        if grep -q "alias composer='/usr/local/bin/composer'" "${home}/.bashrc" 2>/dev/null; then
            sed -i "s|alias composer='/usr/local/bin/composer'|alias composer='/usr/bin/php${php_ver} /usr/local/bin/composer'|" "${home}/.bashrc"
            echo "  ${app}: fixed .bashrc composer alias"
        fi
        # Fix .bashrc deploy alias (bare dep → explicit PHP)
        if grep -q "alias deploy='dep deploy" "${home}/.bashrc" 2>/dev/null; then
            sed -i "s|alias deploy='dep deploy -f|alias deploy='/usr/bin/php${php_ver} /usr/local/bin/dep deploy -f|" "${home}/.bashrc"
            echo "  ${app}: fixed .bashrc deploy alias"
        fi
    fi

    # 3. Fix crontab deploy trigger (bare dep → explicit PHP)
    if crontab -u "$app" -l 2>/dev/null | grep -q "/usr/local/bin/dep deploy" 2>/dev/null; then
        if ! crontab -u "$app" -l 2>/dev/null | grep -q "php${php_ver} /usr/local/bin/dep" 2>/dev/null; then
            crontab -u "$app" -l 2>/dev/null | sed "s|/usr/local/bin/dep deploy|/usr/bin/php${php_ver} /usr/local/bin/dep deploy|g" | crontab -u "$app" -
            echo "  ${app}: fixed crontab deploy trigger"
        fi
    fi
done

echo "Migration 4.2.9 complete"
