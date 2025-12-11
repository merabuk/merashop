#!/usr/bin/env bash
: "${STATIC_PHP_FPM_POOL_WORKERS:=25}"
: "${USER_GROUP:=www}"
: "${USER_NAME:=www}"

# shellcheck disable=SC2002
cat ./docker/app/php/php-fpm.d/zz-docker.conf \
| sed "s/{{ static_php_fpm_pool_workers }}/${STATIC_PHP_FPM_POOL_WORKERS}/" \
| sed "s/{{ group_name }}/${USER_GROUP}/" \
| sed "s/{{ user_name }}/${USER_NAME}/" \
> /usr/local/etc/php-fpm.d/zz-docker.conf

if [ "${APP_RUNTIME_ENV}" = "local" ]; then
    echo "APP_RUNTIME_ENV is local"
else
    echo "APP_RUNTIME_ENV is not local"
fi

su-exec root:root /usr/local/sbin/php-fpm -F
