#!/usr/bin/env sh

set -e

require_env() {
    # shellcheck disable=SC3043
    local var_name="$1"

    if [ -z "$(eval "printf '%s' \"\${${var_name}}\"")" ]; then
        echo "Missing required environment variable: ${var_name}" >&2
        exit 1
    fi
}

validate_worker_type() {
    case "${WORKER_TYPE}" in
        queue|scheduler)
            ;;
        *)
            echo "Invalid WORKER_TYPE: ${WORKER_TYPE}. Expected 'queue' or 'scheduler'." >&2
            exit 1
            ;;
    esac
}

wait_for_rabbitmq() {
    # shellcheck disable=SC3043
    local host="${RABBITMQ_HOST:-rabbitmq}"
    # shellcheck disable=SC3043
    local port="${RABBITMQ_PORT:-5672}"
    # shellcheck disable=SC3043
    local timeout="${RABBITMQ_WAIT_SECONDS:-30}"
    # shellcheck disable=SC3043
    local end=$(( $(date +%s) + timeout ))

    while ! php -r "\$fp=@fsockopen('${host}', ${port}, \$errno, \$errstr, 1); if (\$fp) { fclose(\$fp); exit(0); } exit(1);" >/dev/null 2>&1; do
        if [ "$(date +%s)" -ge "${end}" ]; then
            echo "RabbitMQ is not reachable at ${host}:${port} after ${timeout}s." >&2
            exit 1
        fi
        sleep 1
    done
}

require_env "MESSENGER_TRANSPORT"
require_env "WORKER_TYPE"
validate_worker_type

if [ "${WAIT_RABBITMQ}" = true ]; then
    wait_for_rabbitmq
fi

if [ "${APP_RUNTIME_ENV}" = 'local' ]; then
    VERBOSE="-vv"
else
    VERBOSE=""
fi

if [ "${MESSENGER_QUEUES}" ]; then
    exec php bin/console messenger:consume "${MESSENGER_TRANSPORT}" --queues="${MESSENGER_QUEUES}" "${VERBOSE}"
else
    exec php bin/console messenger:consume "${MESSENGER_TRANSPORT}" "${VERBOSE}"
fi
