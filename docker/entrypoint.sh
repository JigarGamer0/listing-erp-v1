#!/bin/sh
set -e

echo "Booting Listing ERP..."

# Optimize Laravel cache
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

php artisan storage:link || true

echo "Starting Supervisord (PHP-FPM + Nginx)..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
