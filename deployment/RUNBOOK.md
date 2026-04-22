# Zawaj Pro — First-Deploy Runbook

## Pre-deploy checklist

- [ ] Domain (zawajpro.dz or similar) pointed to Coolify VPS A record
- [ ] Cloudflare (optional) SSL set to "Full (strict)"
- [ ] Managed or self-hosted MySQL/MariaDB 10.4+ reachable from the app container
- [ ] Redis instance reachable
- [ ] Chargily Pay **live** keys copied from dashboard
- [ ] SMTP provider account + domain verified (Postmark / Resend / Mailgun) with SPF + DKIM in DNS
- [ ] Sentry project created, DSN copied
- [ ] `.env.production.example` copied to Coolify env vars, all blanks filled

## Coolify setup

1. New resource → Application → Public/Private Repository → select Zawaj Pro repo.
2. Build pack: **Dockerfile** at `deployment/coolify/Dockerfile`.
3. Exposed port: `80`.
4. Set all environment variables from `.env.production.example`.
5. Attach custom domain(s): apex + `www.` (redirect www → apex).
6. Healthcheck endpoint: `/up` · interval 30s · timeout 10s · retries 3 · grace 60s.
7. Deploy.

## First-deploy commands (run inside container via `coolify exec`)

```bash
php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --class=RolesSeeder --force
php artisan db:seed --class=UnlockPackSeeder --force
php artisan db:seed --class=WilayaSeeder --force
php artisan db:seed --class=SettingsSeeder --force

# Seed the admin password you want:
php artisan tinker --execute="App\Models\User::find(App\Models\User::role('admin')->value('id'))->forceFill(['password' => \Hash::make('CHANGE_ME')])->save();"

# n8n service account + API token (copy the plaintext output to n8n):
php artisan zawaj:create-api-token --name=n8n-service --abilities=n8n-service

php artisan storage:link
php artisan zawaj:smoke-test
php artisan zawaj:audit-production
```

## DNS records

| Type  | Name                | Value                                                 |
|-------|---------------------|-------------------------------------------------------|
| A     | zawajpro.dz         | VPS public IP                                         |
| CNAME | www                 | zawajpro.dz                                           |
| TXT   | @ (SPF)             | `v=spf1 include:<mail-provider-spf> ~all`             |
| CNAME/TXT | _dkim or provider-specific | per SMTP provider instructions          |
| TXT   | _dmarc              | `v=DMARC1; p=quarantine; rua=mailto:dmarc@zawajpro.dz`|

## Post-deploy smoke tests

- [ ] `https://zawajpro.dz` → landing page, HSTS header present
- [ ] `/demande` submits a request end-to-end
- [ ] `/admin/login` works with seeded admin
- [ ] `/api/v1/health` returns 200 JSON
- [ ] `php artisan zawaj:test-email you@example.com` arrives
- [ ] Sentry receives a test event: `php artisan sentry:test` (requires sentry install)
- [ ] Chargily test purchase flows through webhook

## Rollback

1. Coolify → Deployments → redeploy previous image tag.
2. If last deploy ran a migration: `php artisan migrate:rollback --step=1`.
3. If the migration was destructive or non-reversible: restore `storage/app/zawaj-pro-backups/` latest dump.

## Scaling checkpoints

| Threshold                          | Action                                         |
|------------------------------------|------------------------------------------------|
| ~1000 jobs/day sustained           | Switch queue to Redis + install Horizon        |
| ~100k wed_requests/year            | MySQL read replica for reporting               |
| Bandwidth > 100 GB/mo              | CDN for `/public/storage/` (Cloudflare R2 / Bunny) |
| Storage > 50 GB                    | Offsite backup to S3/DO Spaces via rclone      |
| Chargily rate-limit hits           | Ask Chargily for limit bump, queue purchases   |

## Optional: install Sentry + Spatie Backup in production

These are NOT installed by default in the repo to keep the test matrix lean. On the production VPS:

```bash
composer require sentry/sentry-laravel spatie/laravel-backup
php artisan sentry:publish --dsn="$SENTRY_LARAVEL_DSN"
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
```

Then add the Spatie backup schedule entries (see `docs/OPERATIONS.md`).
