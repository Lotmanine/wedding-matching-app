# Zawaj Pro — Operations

## Scheduled jobs

| Command | Frequency | Purpose |
|---------|-----------|---------|
| `zawaj:cleanup-expired`       | daily 03:00 Africa/Algiers | ANPDP 14-day soft-delete |
| `zawaj:advance-rounds`        | every 5 min | Round timeout → next round / marketplace |
| `zawaj:cleanup-orphan-uploads`| hourly | Remove provider files whose User was deleted + stale temp uploads |

## Queue worker

Supervisord/systemd template in `deployment/`. Key flags: `--sleep=3 --tries=3 --max-time=3600 --max-jobs=1000 --timeout=90`. `--max-time` and `--max-jobs` protect against memory leaks — worker self-restarts periodically.

After every deploy: `php artisan queue:restart`.

## Logs

- `storage/logs/laravel.log` — default (file) channel
- `storage/logs/zawaj-YYYY-MM-DD.log` — structured JSON via `zawaj_structured` channel, 14-day retention
- `storage/logs/cleanup.log` — ANPDP cleanup results
- `storage/logs/worker.log` — queue worker stdout
- `storage/logs/scheduler.log` — scheduler runs

Structured logging helper: `\App\Support\Logger::info('event.name', [context])`. PII is redacted automatically for common field names (password, whatsapp, phone, remember_token).

## Backups

Deploy-time install (not in repo):

```bash
composer require spatie/laravel-backup
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
```

Then in `routes/console.php`:

```php
Schedule::command('backup:clean')->daily()->at('01:00');
Schedule::command('backup:run')->daily()->at('01:30');
Schedule::command('backup:monitor')->daily()->at('02:00');
```

Retention recommendation: 7 daily · 4 weekly · 6 monthly. Notify `admin@zawajpro.dz` on failure.

For offsite: configure `rclone` to nightly copy `storage/app/zawaj-pro-backups/` to S3/DO Spaces.

## Monitoring

- Health probe: `GET /up` (Coolify), `GET /api/v1/health` (richer status)
- Sentry (install separately): captures exceptions with PII scrubbed by `App\Support\Logger` conventions
- Chargily webhook failures → visible in `/admin/webhooks/{id}/deliveries` with replay button

## Incident runbook

1. Service down → check Coolify deploy logs, `storage/logs/laravel.log`, Sentry
2. Chargily webhook stuck pending → admin → purchases → filter pending >1h → manual mark-paid with reason
3. Service requests stuck in `status=matching` → means n8n isn't picking them up. Check n8n workflow health. Emergency unblock: admin → service request → "Forcer ouverture marketplace".
4. Mass lead expiry surprise → check `zawaj:cleanup-expired` scheduler log; it may have been offline and caught up.

## Routine checks

- Daily: `php artisan zawaj:smoke-test`
- Weekly: review `/admin/logs/audit` for unusual admin actions
- Monthly: review MySQL slow query log; re-run `php artisan zawaj:audit-indexes`

## Secrets rotation

- Rotate `APP_KEY` → invalidates all existing sessions (force logout) + breaks encrypted DB columns if any. Coordinate with maintenance window.
- Rotate Chargily keys → new keys in `.env`, restart app. Existing pending checkouts continue against old gateway-side state; new ones use new keys.
- Rotate Sanctum tokens → `php artisan sanctum:prune-expired` + manually revoke by deleting `personal_access_tokens` rows.
