# Wedding Matching App: Operations

## Scheduled jobs

Defined in `routes/console.php`. Times use the application timezone (`APP_TIMEZONE`).

| Command | Frequency | Purpose |
|---------|-----------|---------|
| `wedding:cleanup-expired`       | daily 03:00 | Expires requests, then erases the couple's personal data |
| `wedding:demo-reset`            | daily 04:00, demo mode only | Restores the demo data |
| `wedding:advance-rounds`        | every 5 min | Round timeout: next round or marketplace |
| `wedding:cleanup-orphan-uploads`| hourly | Removes provider files whose user was deleted, and stale temp uploads |

## Queue worker

Supervisor and systemd templates are in `deployment/`. Key flags: `--sleep=3 --tries=3 --max-time=3600 --max-jobs=1000 --timeout=90`. `--max-time` and `--max-jobs` protect against memory leaks: the worker restarts itself periodically.

`DeliverWebhookJob` sets its own limits (6 tries, 30-second timeout), which take precedence over the worker flags.

After every deploy: `php artisan queue:restart`. The container entrypoint does this for you.

## Logs

- `storage/logs/laravel.log`: default channel
- `storage/logs/app-structured.log`: structured JSON through the `app_structured` channel. It uses the daily driver, so files are named `app-structured-YYYY-MM-DD.log` and kept 14 days
- `storage/logs/cleanup.log`: results of `wedding:cleanup-expired`
- `storage/logs/n8n-errors.log`: errors reported by the n8n error handler
- `storage/logs/worker.log`: queue worker output
- `storage/logs/scheduler.log`: scheduler runs (container deployment)

Structured logging helper: `\App\Support\Logger::info('event.name', [context])`. These top-level keys are redacted automatically: `password`, `password_confirmation`, `couple_whatsapp`, `whatsapp`, `phone`, `remember_token`.

## Demo mode

`WEDDING_DEMO_MODE=true` turns the app into the public demo. `.env.production.example` enables it; `.env.example` leaves it off.

- **Sample data:** `php artisan db:seed` also runs `DemoSeeder`: the demo accounts, three fictional providers per city, and requests in every state of the lifecycle.
- **Login pages:** `/provider/login` and `/admin/login` show the demo accounts from `config/wedding.php`.
- **Test checkout:** buying a pack marks the purchase paid at once, with gateway `demo` and payment method `online_demo`. Chargily is not called. Credits go through the normal ledger.
- **No receipts:** `purchase.completed` is not sent, because the demo addresses are not real.
- **`DemoGuard`:** restricts the demo accounts. See [`SECURITY.md`](SECURITY.md#public-demo).

`php artisan wedding:demo-reset` restores the initial state:

1. It empties the lead disputes, service requests, wedding requests, credit ledger, purchases, feedback, WhatsApp messages, webhook deliveries and provider data tables.
2. It deletes provider accounts that hold no API token.
3. It re-runs the settings, unlock pack and demo seeders, then removes orphan uploads.

Cities, webhook endpoints, API tokens and the service account that owns them are kept. The command refuses to run when demo mode is off. It erases data, so never enable demo mode on a database holding real data.

## Backups

Backups are optional and `spatie/laravel-backup` is not part of the repo. To install it at deploy time:

```bash
composer require spatie/laravel-backup
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
```

`routes/console.php` schedules `backup:clean` (01:00), `backup:run` (01:30) and `backup:monitor` (02:00), guarded by `class_exists(Spatie\Backup\Console\Commands\Backup::class)`. After installing, run `php artisan schedule:list` to confirm these entries appear.

Retention recommendation: 7 daily, 4 weekly, 6 monthly. For offsite copies, send the backup destination to object storage with `rclone`.

## Monitoring

- Health probe: `GET /up` (platform healthcheck), `GET /api/v1/health` (database and cache status)
- Ops snapshot: `GET /api/v1/admin/ops/health-snapshot`, with severity thresholds in `config/ops.php`. The `wedding-core` n8n workflow reads it every 30 minutes, e-mails alerts and records a heartbeat
- Outbound webhook failures: `/admin/webhooks/{id}/deliveries`, with a replay button, or `php artisan wedding:webhook-deliveries --failed`
- Chargily webhook problems: logged to the default channel; check `/admin/purchases` for purchases stuck in pending
- Sentry is optional and not installed by default (see `deployment/RUNBOOK.md`)

## Incident runbook

1. Service down: check the deployment logs, `storage/logs/laravel.log` and Sentry if installed.
2. Purchase stuck in pending after a Chargily payment: in `/admin/purchases`, open the purchase and mark it paid manually, with a reason.
3. Service requests stuck in `status=matching`: n8n is not picking them up. Check the n8n workflows and `webhook_deliveries`. Emergency unblock: in `/admin/service-requests`, open the request and use "Force open to marketplace".
4. Mass lead expiry surprise: check `storage/logs/cleanup.log`. The scheduler may have been offline and caught up.

## Routine checks

- Daily: `php artisan wedding:smoke-test`
- Weekly: review `/admin/logs/audit` for unusual admin actions
- Monthly: review the MySQL slow query log; re-run `php artisan wedding:audit-indexes`

## Secrets rotation

- **`APP_KEY`:** rotating it signs everyone out and makes encrypted values unreadable: the Chargily and WhatsApp secrets saved in `/admin/payments` and `/admin/whatsapp`, and the WhatsApp button tokens already sent. Re-enter those secrets afterwards, in a maintenance window.
- **Chargily keys:** update them in `/admin/payments`, or in `.env` when the database values are empty (saved settings take precedence), then restart the app if you changed `.env`.
- **`WEDDING_WEBHOOK_SECRET`:** update it in Laravel, in n8n, and in the secret of each endpoint in `/admin/webhooks`. Outbound signatures use the endpoint secret; the n8n workflows verify with `WEDDING_WEBHOOK_SECRET`; the error webhook compares against it.
- **Sanctum tokens:** delete the `personal_access_tokens` row and issue a new token. `php artisan sanctum:prune-expired` removes expired tokens.
