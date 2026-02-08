#!/bin/bash
set -e

DEFAULT_DB_NAME=${POSTGRES_DB}

databases=("$POSTGRES_DB_CUSTOMER" "$POSTGRES_DB_IDENTITY_ACCESS" "$POSTGRES_DB_EMAIL_SENDER" "$POSTGRES_DB_CATALOG")

for db in "${databases[@]}"; do
    if [ "$db" = "$DEFAULT_DB_NAME" ]; then
        echo "  Skipping database: $db (already created as default)"
        continue
    fi

    exists=$(psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "postgres" -tAc "SELECT 1 FROM pg_database WHERE datname='${db}';")
    if [ "$exists" = "1" ]; then
        echo "  Skipping database: $db (already exists)"
        continue
    fi

    echo "  Creating database: $db"
    psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "postgres" <<-EOSQL
        CREATE DATABASE "$db";
EOSQL
done
