#!/bin/sh
set -e

# Wait for database to be ready (optional, but good practice)
# We assume the database is accessible if the container starts

echo "Optimizing configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "Running migrations..."
# For production, forcing migration
php artisan migrate --force

echo "Starting PHP-FPM..."
exec php-fpm
