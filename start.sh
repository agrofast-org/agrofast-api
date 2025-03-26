#!/bin/sh
set -e

echo "Running migrations"

php artisan migrate

echo "Starting queue worker"

php artisan queue:work &

echo "Starting PHP-FPM"

exec php-fpm
