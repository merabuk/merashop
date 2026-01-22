#!/bin/bash
set -e

DEFAULT_DB_NAME=${POSTGRES_DB}

databases=("$POSTGRES_DB_USERS" "$POSTGRES_DB_EMAIL_SENDER" "$POSTGRES_DB_IDENTITY_ACCESS")

for db in "${databases[@]}"; do
    if [ "$db" != "$DEFAULT_DB_NAME" ]; then
        echo "  Creating database: $db"
        psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "postgres" <<-EOSQL
            CREATE DATABASE "$db";
EOSQL
    else
        echo "  Skipping database: $db (already created as default)"
    fi
done
