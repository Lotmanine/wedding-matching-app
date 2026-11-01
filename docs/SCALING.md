# Wedding Matching App: Scaling Checkpoints

Rough signals and the corresponding upgrade path. Don't do any of these *before* hitting the threshold: premature optimization wastes effort.

| Load / scale threshold | Symptom | Action |
|------------------------|---------|--------|
| ~1000 queued jobs/day sustained | Queue depth grows, worker log busy | Switch `QUEUE_CONNECTION=redis`, install Horizon for a dashboard |
| ~100k wed_requests/year | Admin reporting queries slow | MySQL read replica; point the admin dashboard at it |
| ~10k active providers | Eligibility scope query slow | Add covering indexes on `provider_services(service_type, user_id)` and `provider_coverage(city_code, user_id)`; consider a materialized view |
| Bandwidth > 100 GB/mo | CDN hits start costing | Put `/storage/providers/*` behind Cloudflare R2 or Bunny CDN |
| Storage > 50 GB | Backups take > 30 min | Offsite S3 or DO Spaces via `rclone`, prune portfolio images > 2y old |
| Webhook deliveries backlog | `delivered_at IS NULL AND failed_at IS NULL` rows growing | More queue workers (numprocs=4+), confirm the n8n receiving end is healthy |
| Payment gateway 429s | Provider purchases hit delays | Request a limit increase from Chargily |
| 50+ concurrent logged-in providers | Livewire requests taxing PHP-FPM | More php-fpm children, consider Octane |

## DB index review

Run monthly:

```bash
php artisan wedding:audit-indexes
```

It checks live indexes against the expected list held in the command. The full set is documented in `database/indexes-audit.md`.

## Caching

`.env.example` uses the database cache; `.env.production.example` already uses Redis for cache and sessions. Move a database-backed environment to Redis when:

- Cache hit latency goes above 5 ms (a database cache in shared MySQL adds round-trips)
- Session storage competes with cache keys

## Search

No full-text search requirement yet. If wed_request volume grows past 50k, consider:

- MySQL `FULLTEXT` index on `description` (simplest)
- Algolia or Meilisearch (nicer UX, external service)

## Horizontal scaling

A single-container deploy is fine until:

- Queue worker CPU is capped
- PHP-FPM workers are exhausted (504s)

Then split the app into:

- Web tier (nginx + php-fpm) × 2
- Worker tier (queue only) × 2
- Scheduler tier × 1 (never multiply; the scheduled commands already use `->onOneServer()` as a safety net)

Shared state (cache, sessions) must be in Redis at this point.
