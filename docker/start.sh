#!/bin/sh
set -e

# Create/update tables and demo data (safe to repeat)
php artisan migrate --force
php artisan db:seed --force

# Speed up the app
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run the SLA scheduler in the background
php artisan schedule:work &

# Start the web server. --no-reload makes it use Render's environment variables.
exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}" --no-reload