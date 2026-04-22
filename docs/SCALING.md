# Zawaj Pro — Scaling Checkpoints

Rough signals and the corresponding upgrade path. Don't do any of these *before* hitting the threshold — premature optimization wastes effort.

| Load / scale threshold | Symptom | Action |
|------------------------|---------|--------|
| ~1000 queued jobs/day sustained | Queue depth grows, worker log busy | Switch `QUEUE_CONNECTION=redis`, install Horizon for dashboard |
| ~100k wed_requests/year | Admin reporting queries slow | MySQL read replica; point admin dashboard at it |
| ~10k active providers | Eligibility scope query slow | Add covering indexes on `provider_services(service_type, user_id)` + `provider_coverage(wilaya_code, user_id)`; consider materialized view |
| Bandwidth > 100 GB/mo | CDN hits start costing | Put `/storage/providers/*` behind Cloudflare R2 or Bunny CDN |
| Storage > 50 GB | Backups take > 30 min | Offsite S3/DO Spaces via `rclone`, prune portfolio images > 2y old |
| Webhook deliveries backlog | `delivered_at IS NULL AND failed_at IS NULL` rows growing | More queue workers (numprocs=4+), confirm n8n receiving end is healthy |
| Chargily rate-limit 429s | Provider purchase UX hits delays | Request limit increase from Chargily; queue webhook replay |
| 50+ concurrent logged-in providers | Livewire requests taxing PHP-FPM | More php-fpm children, consider Octane |

## DB index review

Run monthly:

```bash
php artisan zawaj:audit-indexes
```

Compares live indexes against `database/indexes-audit.md` expected set.

## Caching

Currently database-backed cache. Move to Redis when:
- Cache hit latency > 5ms (database cache in shared MySQL adds round-trips)
- Session storage competes with cache keys

## Search

No full-text search requirement yet. If wed_request volume grows past 50k, consider:
- MySQL `FULLTEXT` index on `description` (simplest)
- Algolia / Meilisearch (nicer UX, external service)

## Horizontal scaling

Single-container deploy is fine until:
- Queue worker CPU capped
- PHP-FPM workers exhausted (504s)

Then split the app into:
- Web tier (nginx + php-fpm) × 2
- Worker tier (queue only) × 2
- Scheduler tier × 1 (never multiply — use `->onOneServer()` as safety net)

Shared state (cache, sessions) must already be in Redis at this point.
