#!/bin/bash

echo "Starting Laravel queue worker in background"
php artisan queue:work --daemon --sleep=3 --tries=3 &
