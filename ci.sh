#!/bin/bash
set -e

echo "Running database migrations"
php artisan migrate --force

# echo "Seeding the database"
# php artisan db:seed --force

echo "Starting Laravel queue worker in background"
php artisan queue:work &

php artisan serve --host=0.0.0.0 --port=80
