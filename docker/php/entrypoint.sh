#!/bin/sh
set -e

cd /var/www

if [ ! -d "vendor" ]; then
    composer install --no-interaction --prefer-dist
fi

php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache || true
chmod -R 775 /var/www/storage /var/www/bootstrap/cache || true

exec "$@"