# Wedding Matching App: Deployment

The app is deployed with Coolify from the Dockerfile in `deployment/coolify/`. See [`deployment/RUNBOOK.md`](../deployment/RUNBOOK.md) for the step-by-step first deploy.

## Topology

```
Cloudflare (optional) ──► Coolify Traefik ──► App container (Dockerfile)
                                                │
                                                ├── nginx (port 80)
                                                ├── php-fpm
                                                ├── supervisord:
                                                │     ├── queue-worker × 2
                                                │     └── scheduler (60s sleep loop)
                                                └── entrypoint.sh: caches, then migrate in the background

                                         MariaDB (managed or same host)
                                         Redis    (managed or same host)
```

## Files

| Path | Purpose |
|------|---------|
| `deployment/coolify/Dockerfile`       | Production image (no-dev Composer install, Vite build) |
| `deployment/coolify/entrypoint.sh`    | Caches config, routes, views and events; starts the server; waits for the database and migrates in the background |
| `deployment/coolify/supervisord.conf` | Runs php-fpm, nginx, 2 queue workers and the scheduler loop |
| `deployment/coolify/nginx.conf`       | Laravel server block, 10 MB upload limit, no dotfile access |
| `deployment/coolify/docker-compose.yml` | Local production simulation (app, MariaDB, Redis) |
| `deployment/supervisor/wedding-worker.conf` | Host-supervised queue worker (non-container deploy) |
| `deployment/systemd/wedding-worker.service` | systemd alternative |
| `.env.production.example` / `.env.staging.example` | Environment templates |

## Build and boot sequence

1. `composer install --no-dev` and an optimized autoloader (Dockerfile)
2. `npm ci && npm run build`, which generates `public/build/*` (Dockerfile)
3. `php artisan config:cache`, `route:cache`, `view:cache`, `event:cache` (entrypoint, on every boot)
4. The web server starts straight away, so the `/up` healthcheck can pass before the database is reachable
5. `php artisan migrate --force` (entrypoint, in the background once the database answers)
6. `php artisan queue:restart`, so workers pick up new code (entrypoint, same background step)

After a deploy, run `php artisan wedding:smoke-test`. It exits non-zero if a check fails.

## Vite assets

`public/build/` is gitignored. The Docker build generates it.
