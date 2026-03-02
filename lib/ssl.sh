#!/bin/bash
#############################################
# Cipi — SSL (Let's Encrypt)
#############################################

ssl_command() {
    local sub="${1:-}"; shift||true
    case "$sub" in
        install) _ssl_install "$@" ;;
        renew)   _ssl_renew ;;
        status)  _ssl_status ;;
        *) error "Use: install renew status"; exit 1 ;;
    esac
}

_ssl_install() {
    local app="${1:-}"; [[ -z "$app" ]] && { error "Usage: cipi ssl install <app>"; exit 1; }
    app_exists "$app" || { error "App '$app' not found"; exit 1; }
    local d; d=$(app_get "$app" domain)
    [[ -z "$d" ]] && { error "No domain for app '$app'"; exit 1; }
    local domains="-d ${d}"
    local aliases
    aliases=$(jq -r --arg a "$app" '.[$a].aliases[]?//empty' "${CIPI_CONFIG}/apps.json" 2>/dev/null || true)
    while read -r a; do
        [[ -n "$a" ]] && domains+=" -d ${a}"
    done <<< "${aliases:-}"
    echo ""
    step "Installing SSL for ${d}..."
    if certbot --nginx $domains --non-interactive --agree-tos --register-unsafely-without-email --redirect; then
        sed -i "s|^APP_URL=http://|APP_URL=https://|" "/home/${app}/shared/.env" 2>/dev/null || true
        log_action "SSL INSTALLED: $app"
        echo ""
        success "SSL installed for ${d}"
    else
        echo ""
        error "SSL failed. Check: DNS points to this server, port 80 is open, domain is correct."
        exit 1
    fi
}

_ssl_renew() {
    step "Renewing certificates..."
    certbot renew --nginx --non-interactive 2>&1
    systemctl reload nginx 2>/dev/null
    success "Renewal complete"
}

_ssl_status() {
    echo -e "\n${BOLD}SSL Certificates${NC}"
    certbot certificates 2>/dev/null | while IFS= read -r line; do
        case "$line" in
            *"Certificate Name:"*) echo -e "\n  ${BOLD}${line##*: }${NC}" ;;
            *"Domains:"*)          echo -e "    Domains: ${CYAN}${line##*: }${NC}" ;;
            *"Expiry Date:"*)
                local exp; exp=$(echo "${line##*: }"|awk '{print $1}')
                local days; days=$(( ($(date -d "$exp" +%s) - $(date +%s)) / 86400 ))
                local c="${GREEN}"; [[ $days -lt 14 ]] && c="${RED}"; [[ $days -lt 30 && $days -ge 14 ]] && c="${YELLOW}"
                echo -e "    Expiry:  ${c}${exp} (${days}d)${NC}" ;;
        esac
    done; echo ""
}
