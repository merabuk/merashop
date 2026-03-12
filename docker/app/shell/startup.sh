#!/usr/bin/env bash
set -e

# --- Defaults ---
: "${USER_NAME:=www}"
: "${USER_GROUP:=www}"
: "${STATIC_PHP_FPM_POOL_WORKERS:=5}"

# --- Helper Functions ---
require_env() {
    local var_name="$1"
    if [ -z "$(eval "printf '%s' \"\${${var_name}}\"")" ]; then
        echo "Missing required environment variable: ${var_name}" >&2
        exit 1
    fi
}

wait_for_rabbitmq() {
    local host="${RABBITMQ_HOST:-rabbitmq}"
    local port="${RABBITMQ_PORT:-5672}"
    local timeout="${RABBITMQ_WAIT_SECONDS:-30}"
    local end=$(( $(date +%s) + timeout ))

    echo "Waiting for RabbitMQ at ${host}:${port}..."
    while ! php -r "\$fp=@fsockopen('${host}', ${port}, \$errno, \$errstr, 1); if (\$fp) { fclose(\$fp); exit(0); } exit(1);" >/dev/null 2>&1; do
        if [ "$(date +%s)" -ge "${end}" ]; then
            echo "RabbitMQ is not reachable after ${timeout}s." >&2
            exit 1
        fi
        sleep 1
    done
    echo "RabbitMQ is up!"
}

# --- Execution Branching ---
if [ -n "${WORKER_TYPE}" ]; then
    # --- WORKER MODE ---
    require_env "MESSENGER_TRANSPORT"
    if [ "${WAIT_RABBITMQ}" = true ]; then wait_for_rabbitmq; fi

    # Disable Xdebug for workers by default for performance
    # If you need to debug, run the worker with XDEBUG_MODE=debug
    XDEBUG_OPTS="-dxdebug.mode=off"
    if [ "${XDEBUG_WORKER_ENABLED}" = "true" ]; then
        XDEBUG_OPTS="-dxdebug.mode=debug"

        touch /var/log/xdebug/xdebug.log
        chmod 777 /var/log/xdebug/xdebug.log
    fi

    VERBOSE=""
    if [ "${APP_RUNTIME_ENV}" = 'local' ]; then VERBOSE="-vv"; fi

    echo "Starting Worker [Type: ${WORKER_TYPE}, Transport: ${MESSENGER_TRANSPORT}] (Xdebug: ${XDEBUG_WORKER_ENABLED:-false})"

    if [ "${MESSENGER_QUEUES}" ]; then
        exec su-exec "${USER_NAME}:${USER_GROUP}" php $XDEBUG_OPTS bin/console messenger:consume "${MESSENGER_TRANSPORT}" --queues="${MESSENGER_QUEUES}" "${VERBOSE}" --limit=100 --memory-limit=128M
    else
        exec su-exec "${USER_NAME}:${USER_GROUP}" php $XDEBUG_OPTS bin/console messenger:consume "${MESSENGER_TRANSPORT}" "${VERBOSE}" --limit=100 --memory-limit=128M
    fi

else
    # --- FPM MODE ---
    echo "Starting PHP-FPM for ${APP_RUNTIME_ENV}..."

    if [ "${APP_RUNTIME_ENV}" = "local" ]; then
        # Before starting FPM, check the permissions for xdebug logs once again (FPM usually starts as root, then drops privileges)
        touch /var/log/xdebug/xdebug.log
        chmod 777 /var/log/xdebug/xdebug.log
    fi

    sed "s/{{ static_php_fpm_pool_workers }}/${STATIC_PHP_FPM_POOL_WORKERS}/; s/{{ group_name }}/${USER_GROUP}/; s/{{ user_name }}/${USER_NAME}/" \
        ./docker/app/php/php-fpm.d/zz-docker.conf > /usr/local/etc/php-fpm.d/zz-docker.conf

    exec su-exec root:root /usr/local/sbin/php-fpm -F
fi
