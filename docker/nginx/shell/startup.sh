#!/usr/bin/env bash
: "${PHP_CGI_PASS:=127.0.0.1}"
: "${NODE_JS_PASS:=node}"
: "${APP_RUNTIME_ENV:=prod}"

cat ./docker/nginx/etc/conf.d/app.conf \
| sed "s/{{ web_domain }}/${APP_WEB_DOMAIN}/" \
| sed "s/{{ api_domain }}/${APP_API_DOMAIN}/" \
| sed "s/{{ admin_api_domain }}/${APP_ADMIN_API_DOMAIN}/" \
| sed "s/{{ php_cgi_pass }}/${PHP_CGI_PASS}/" \
> /etc/nginx/conf.d/app.conf

RUNTIME_VITE_CONF="/etc/nginx/conf.d/vite-runtime.inc"

if [ "$APP_RUNTIME_ENV" = "local" ]; then
    echo "Applying Vite Development proxy configuration..."
    cat ./docker/nginx/etc/conf.d/includes/vite.conf \
    | sed "s/{{ node_js_pass }}/${NODE_JS_PASS}/" \
    > "$RUNTIME_VITE_CONF"
else
    echo "Clearing Vite proxy (Production/Testing mode)..."
    echo "# Vite proxy disabled in $APP_RUNTIME_ENV mode" > "$RUNTIME_VITE_CONF"
fi

cat ./docker/nginx/etc/conf.d/sources.conf \
| sed "s/{{ sources_domain }}/${APP_SOURCES_DOMAIN}/" \
> /etc/nginx/conf.d/sources.conf

nginx -g 'daemon off;'
