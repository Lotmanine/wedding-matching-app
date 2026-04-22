#!/bin/sh
set -e

echo "[entrypoint] waiting for database…"
for i in $(seq 1 60); do
    if php artisan db:show >/dev/null 2>&1; then
        break
    fi
    sleep 2
done

echo "[entrypoint] running migrations"
php artisan migrate --force

echo "[entrypoint] caching config/routes/views/events"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "[entrypoint] linking storage"
php artisan storage:link 2>/dev/null || true

echo "[entrypoint] restarting queue workers"
php artisan queue:restart || true

echo "[entrypoint] ready"
exec "$@"
