#!/usr/bin/env bash
: "${PHP_CGI_PASS:=127.0.0.1}"

# shellcheck disable=SC2002
cat ./docker/nginx/etc/conf.d/app.conf \
| sed "s/{{ web_domain }}/${APP_WEB_DOMAIN}/" \
| sed "s/{{ api_domain }}/${APP_API_DOMAIN}/" \
| sed "s/{{ admin_api_domain }}/${APP_ADMIN_API_DOMAIN}/" \
| sed "s/{{ php_cgi_pass }}/${PHP_CGI_PASS}/" \
> /etc/nginx/conf.d/app.conf

# shellcheck disable=SC2002
cat ./docker/nginx/etc/conf.d/sources.conf \
| sed "s/{{ sources_domain }}/${APP_SOURCES_DOMAIN}/" \
> /etc/nginx/conf.d/sources.conf

nginx -g 'daemon off;'
