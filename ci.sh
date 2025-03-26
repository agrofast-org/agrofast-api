#!/bin/bash
set -e

echo "Running database migrations"
php artisan migrate --force

echo "Starting Laravel queue worker in background"
php artisan queue:work &
