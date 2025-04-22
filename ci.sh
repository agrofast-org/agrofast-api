#!/bin/bash
set -e

echo "Running database migrations"
php artisan migrate --force

echo "Starting Laravel queue worker in background"
nohup php artisan queue:work > /dev/null 2>&1 &
disown

php artisan serve --host=0.0.0.0 --port=80
