# Wedding Matching App: First-Deploy Runbook

Replace `your-domain.example` with your own domain. The public demo runs at <https://wedding-app.oldev.site>.

## Pre-deploy checklist

- [ ] Domain pointed to the Coolify server's A record
- [ ] Cloudflare (optional) SSL set to "Full (strict)"
- [ ] Managed or self-hosted MySQL or MariaDB 10.4+ reachable from the app container
- [ ] Redis instance reachable
- [ ] Decision on demo mode: `.env.production.example` sets `WEDDING_DEMO_MODE=true`. Set it to `false` for a real deployment and copy the Chargily Pay **live** keys from the dashboard
- [ ] SMTP provider account and verified domain (Postmark, Resend, Mailgun) with SPF and DKIM in DNS
- [ ] Sentry project created and DSN copied (optional)
- [ ] `.env.production.example` copied to the Coolify environment variables, all blanks filled, including `ADMIN_PASSWORD`
- [ ] `APP_KEY` generated with `php artisan key:generate --show` and added to the environment variables

## Coolify setup

1. New resource → Application → Public/Private Repository → select this repository.
2. Build pack: **Dockerfile** at `deployment/coolify/Dockerfile`.
3. Exposed port: `80`.
4. Set all environment variables from `.env.production.example`.
5. Attach custom domain(s): apex and `www.` (redirect www to apex).
6. Healthcheck endpoint: `/up`, interval 30s, timeout 10s, retries 3, grace 60s.
7. Deploy. The container runs the migrations by itself once the database answers.

## First-deploy commands (run in the application terminal in Coolify)

```bash
# Cities, roles, settings, unlock packs, admin user, and the demo data in demo mode.
# If ADMIN_PASSWORD is blank, a random password is printed once.
php artisan db:seed --force

# n8n service account and API token (copy the plaintext output to n8n):
php artisan wedding:create-api-token --name=n8n-service --abilities=n8n-service

php artisan wedding:smoke-test
php artisan wedding:audit-production
```

`wedding:audit-production` also checks for live Chargily settings, which the public demo does not use. In demo mode, expect those checks to fail if you left the Chargily values blank.

## n8n

Follow [`n8n/README.md`](../n8n/README.md): import the workflows, set their variables, and create the two endpoints in `/admin/webhooks`.

## DNS records

| Type  | Name                | Value                                                 |
|-------|---------------------|-------------------------------------------------------|
| A     | your-domain.example | Server public IP                                      |
| CNAME | www                 | your-domain.example                                   |
| TXT   | @ (SPF)             | `v=spf1 include:<mail-provider-spf> ~all`             |
| CNAME/TXT | _dkim or provider-specific | per SMTP provider instructions          |
| TXT   | _dmarc              | `v=DMARC1; p=quarantine; rua=mailto:dmarc@your-domain.example` |

## Post-deploy smoke tests

- [ ] `https://your-domain.example`: landing page, HSTS header present
- [ ] `/request` submits a request end-to-end
- [ ] `/for-providers`, `/privacy` and `/terms` load
- [ ] `/admin/login` works with the seeded admin
- [ ] `/api/v1/health` returns 200 JSON
- [ ] `php artisan wedding:test-email you@example.com` arrives
- [ ] Sentry receives a test event: `php artisan sentry:test` (requires the Sentry install)
- [ ] A test purchase grants credits: through the test checkout in demo mode, otherwise through the Chargily webhook

## Rollback

1. Coolify → Deployments → redeploy the previous image tag.
2. If the last deploy ran a migration: `php artisan migrate:rollback --step=1`.
3. If the migration was destructive or non-reversible: restore the latest database backup.

## Scaling checkpoints

| Threshold                          | Action                                         |
|------------------------------------|------------------------------------------------|
| ~1000 jobs/day sustained           | Switch queue to Redis and install Horizon      |
| ~100k wed_requests/year            | MySQL read replica for reporting               |
| Bandwidth > 100 GB/mo              | CDN for `/public/storage/` (Cloudflare R2 or Bunny) |
| Storage > 50 GB                    | Offsite backup to S3 or DO Spaces via rclone   |
| Chargily rate-limit hits           | Ask Chargily for a limit increase              |

## Optional: install Sentry and Spatie Backup in production

These are not installed by default, to keep the test matrix lean. To add them:

```bash
composer require sentry/sentry-laravel spatie/laravel-backup
php artisan sentry:publish --dsn="$SENTRY_LARAVEL_DSN"
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
```

See the backup notes in `docs/OPERATIONS.md`.
