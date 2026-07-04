#!/bin/sh
set -e

# Cache config/routes/views/events FIRST — none of these touch the database,
# and they must be in place before the web server serves traffic.
echo "[entrypoint] caching config/routes/views/events"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan storage:link 2>/dev/null || true

# Database-dependent startup runs in the BACKGROUND so the web server can come
# up immediately and pass the platform healthcheck (/up needs no DB). During a
# Coolify rolling deploy the new container is only attached to the shared
# `coolify` network *after* it reports healthy, so migrations must NOT block
# startup — otherwise the healthcheck times out, the deploy rolls back, and the
# container never gets DB access (deadlock). Migrations are idempotent, so
# running them a few seconds after the server comes up is safe.
(
    echo "[entrypoint] (bg) waiting for database…"
    i=1
    while [ "$i" -le 150 ]; do
        if php artisan db:show >/dev/null 2>&1; then
            echo "[entrypoint] (bg) database reachable — running migrations"
            php artisan migrate --force || echo "[entrypoint] (bg) migrate failed"
            php artisan queue:restart || true
            echo "[entrypoint] (bg) database startup complete"
            exit 0
        fi
        i=$((i + 1))
        sleep 2
    done
    echo "[entrypoint] (bg) WARNING: database never reachable after 300s"
) &

echo "[entrypoint] starting web server"
exec "$@"
