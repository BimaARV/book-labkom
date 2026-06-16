#!/bin/sh
set -e

echo "Starting Queue Worker..."
exec php artisan queue:work --tries=3 --timeout=90
