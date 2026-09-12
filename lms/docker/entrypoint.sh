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

# Only chown when needed (full recursive chown on every cold start is expensive).
if [ "$(stat -c %U storage 2>/dev/null || echo root)" != "www-data" ]; then
    chown -R www-data:www-data storage bootstrap/cache || true
fi
chmod -R ug+rwx storage bootstrap/cache || true

if [ -z "${APP_KEY}" ]; then
    echo "APP_KEY is not set. Generate one locally with: php artisan key:generate --show"
    echo "Then add it as an environment variable in Render."
    exit 1
fi

php artisan storage:link --force || true

# Config + routes only at boot (fast). View/event caches are optional and slower.
php artisan config:cache
php artisan route:cache || true
if [ "${CACHE_VIEWS_ON_BOOT:-false}" = "true" ]; then
    php artisan view:cache || true
    php artisan event:cache || true
fi

# Default OFF so cold starts / free-tier wake-ups are not blocked by migrate.
# Set RUN_MIGRATIONS=true once after schema changes, then turn it off again.
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force
fi

if [ "${SEED_ADMIN:-false}" = "true" ]; then
    php artisan db:seed --class=AdminUserSeeder --force
fi

exec apache2-foreground
