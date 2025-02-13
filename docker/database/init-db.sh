#!/bin/bash

set -e

DB_HOST=${DB_HOST:-postgres}
DB_PORT=${DB_PORT:-5432}
DB_USERNAME=${DB_USERNAME:-postgres}
DB_PASSWORD=${DB_PASSWORD:-postgres}
DB_DATABASE=${DB_DATABASE:-estoque_template_db}

echo "Starting the database setup script..."

echo "Waiting for PostgreSQL at ${DB_HOST}:${DB_PORT}..."
until pg_isready -h "${DB_HOST}" -p "${DB_PORT}" -U "${DB_USERNAME}"; do
    echo "PostgreSQL is not ready yet. Trying again in 2 seconds..."
    sleep 2
done

echo "PostgreSQL is ready. Checking and creating the database if necessary..."

DB_EXISTS=$(PGPASSWORD="${DB_PASSWORD}" psql -h "${DB_HOST}" -U "${DB_USERNAME}" -d postgres -tAc "SELECT 1 FROM pg_database WHERE datname='${DB_DATABASE}'")

if [ "${DB_EXISTS}" != "1" ]; then
    echo "Database ${DB_DATABASE} not found. Creating..."
    PGPASSWORD="${DB_PASSWORD}" psql -h "${DB_HOST}" -U "${DB_USERNAME}" -d postgres -c "CREATE DATABASE \"${DB_DATABASE}\""
    echo "Database ${DB_DATABASE} created successfully."
else
    echo "Database ${DB_DATABASE} already exists."
fi

echo "Database setup completed."

tail -f /dev/null
