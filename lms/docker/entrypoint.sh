#!/bin/bash
set -e

PORT="${PORT:-80}"

sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf

mkdir -p \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache/data \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache

if [ -z "${APP_KEY}" ]; then
    echo "APP_KEY is not set. Generate one locally with: php artisan key:generate --show"
    echo "Then add it as an environment variable in Render."
    exit 1
fi

php artisan package:discover --ansi || true
php artisan storage:link --force || true

# Cache config and routes. View cache is skipped because optional Breeze stubs can fail compile.
php artisan config:cache
php artisan route:cache || true

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force
fi

if [ "${SEED_ADMIN:-false}" = "true" ]; then
    php artisan db:seed --class=AdminUserSeeder --force
fi

exec apache2-foreground
