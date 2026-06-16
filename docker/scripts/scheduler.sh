#!/bin/sh
set -e

echo "Starting Scheduler..."
while true; do
    php artisan schedule:run --verbose --no-interaction
    sleep 60
done
