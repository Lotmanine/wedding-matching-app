# Zawaj Pro — Deployment

See [`deployment/RUNBOOK.md`](../deployment/RUNBOOK.md) for the step-by-step first-deploy guide.

## Topology

```
Cloudflare (optional) ──► Coolify Traefik ──► App container (Dockerfile)
                                                │
                                                ├── nginx (port 80)
                                                ├── php-fpm
                                                ├── supervisord:
                                                │     ├── queue-worker × 2
                                                │     └── scheduler (1s sleep loop)
                                                └── entrypoint.sh: migrate + config:cache
                                                
                                         MariaDB (managed or same host)
                                         Redis    (managed or same host)
```

## Files

| Path | Purpose |
|------|---------|
| `deployment/coolify/Dockerfile`       | Production image (multi-stage, no-dev composer, vite build) |
| `deployment/coolify/entrypoint.sh`    | Waits for DB, runs migrate + caches, then exec CMD |
| `deployment/coolify/supervisord.conf` | Runs php-fpm + nginx + 2 queue workers + scheduler loop |
| `deployment/coolify/nginx.conf`       | Laravel-style server block, 10MB upload, no dotfile access |
| `deployment/coolify/docker-compose.yml` | Local production simulation (app + mariadb + redis) |
| `deployment/supervisor/zawaj-worker.conf` | Host-supervised queue worker (non-container deploy) |
| `deployment/systemd/zawaj-worker.service` | systemd alternative |
| `.env.production.example` / `.env.staging.example` | Env templates |

## CI/deploy sequence

1. `composer install --no-dev --optimize-autoloader` (Dockerfile does this)
2. `npm ci && npm run build` (Dockerfile does this; generates `public/build/*`)
3. `php artisan config:cache route:cache view:cache event:cache` (entrypoint does this on every boot)
4. `php artisan migrate --force` (entrypoint)
5. `php artisan queue:restart` so workers pick up new code (entrypoint)
6. `php artisan zawaj:smoke-test` in Coolify pre-deploy hook — exit != 0 blocks promotion

## Vite assets

`public/build/` is gitignored. Build in CI before container bake.
