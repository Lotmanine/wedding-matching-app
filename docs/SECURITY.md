# Wedding Matching App: Security

## Threat model

| Actor | Capability | Mitigation |
|-------|-----------|-----------|
| Anonymous web visitor | form spam, scraping | couple form limited to 3 submissions/hour per IP, login limited to 5/min per IP and e-mail, production CSP |
| Malicious provider | scrape leads, hijack others' unlocks | eligibility checks per provider, race-safe `lockForUpdate` on accept, credit movements written through `CreditsService` as new ledger rows |
| Compromised provider account | run accepts on their behalf | `last_active_at` recorded at login; admin can suspend; lead actions limited to 10/min |
| Stolen Sanctum token | replay API calls | token ability (`n8n-service`), rate limits of 120 and 30 per minute, revocation by deleting the row |
| Chargily webhook spoofing | inject fake paid events | HMAC-SHA256 signature verification, 401 on mismatch |
| Forged WhatsApp button tap | accept a lead as someone else | encrypted button tokens, valid 7 days, sender number must match the provider |
| SQL injection | exfiltrate data | Eloquent and parameter binding |
| XSS | steal session | Blade `{{ }}` auto-escape; production CSP restricts script origins, though it still allows `'unsafe-inline'` and `'unsafe-eval'` |
| CSRF | trigger state changes | Laravel CSRF on web routes except `/webhooks/*`, which verify a signature instead |
| Clickjacking | embed admin pages | `X-Frame-Options: DENY`, CSP `frame-ancestors 'none'` |
| Mass assignment | overwrite sensitive fields | All models use explicit `$fillable` |

## Security headers

`App\Http\Middleware\SecurityHeaders` sets these on every response:

- `X-Frame-Options: DENY`
- `X-Content-Type-Options: nosniff`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: geolocation=(), microphone=(), camera=()`
- `X-XSS-Protection: 1; mode=block`

When the app environment is `production`:

- `Strict-Transport-Security: max-age=31536000; includeSubDomains; preload`
- `Content-Security-Policy`: an allowlist of cdn.jsdelivr.net, cdnjs.cloudflare.com, fonts.googleapis.com, fonts.gstatic.com and pay.chargily.net

## Session and cookies

- `SESSION_SECURE_COOKIE=true` in production
- httpOnly: true (Laravel default)
- sameSite: `lax` (Laravel default)
- Lifetime: 120 minutes
- Session regenerated on login through `$request->session()->regenerate()`

## Transport

- HTTPS URLs enforced with `URL::forceScheme('https')` in production
- Coolify terminates TLS at Traefik; Laravel trusts `X-Forwarded-*` through `trustProxies`

## Authentication

- Passwords: bcrypt, 12 rounds (`BCRYPT_ROUNDS`)
- Provider login: role check after authentication; a non-provider is logged out immediately
- Admin login: separate route and dark UI; only the `admin` and `manager` roles get in
- Password resets: Laravel password broker, 60-minute tokens

## Authorization

- Spatie Permission role middleware (`role:provider`, `role:admin|manager`, `role:admin`) on route groups
- Sanctum token ability (`n8n-service`) for the API
- Ownership checks on provider-facing purchases and leads
- Lead eligibility re-checked inside the `DB::transaction` on accept (race-safe)
- Mutating admin and API actions are recorded in `audit_logs`

## Privacy and data retention

- The couple must tick a consent checkbox to submit the form
- A request expires after the retention period (`WEDDING_RETENTION_DAYS`, 14 days by default, editable in `/admin/settings`). `wedding:cleanup-expired` then soft-deletes it and erases the couple's first name, WhatsApp number, district, description, IP address and user agent, once no provider access window is open. City, services and budget range stay as anonymous figures
- The same run clears the couple's number from WhatsApp logs and deletes webhook delivery logs older than the retention period
- IP addresses on couple requests are stored anonymized
- The API is for n8n only; providers never call it. In the provider area, contact details are blurred until unlock and shown only to the provider who unlocked them

## Secret management

- `.env` is never committed
- `.env.production.example` has only empty placeholders
- Sanctum token plaintexts are shown once at creation and stored only as a hash
- Chargily and WhatsApp secrets saved from the admin area are encrypted with Laravel `Crypt`
- Webhook endpoint secrets are stored in plain text (needed to compute the HMAC), so guard database access

## Public demo

`WEDDING_DEMO_MODE=true` publishes working logins on the login pages. `App\Http\Middleware\DemoGuard` keeps those sessions away from anything that could lock other visitors out or expose an integration. It applies only to the accounts listed in `config/wedding.php`:

- Webhook pages are hidden, because their forms display signing secrets
- In the back office, demo admins may only write to providers, requests, service requests, purchases, disputes, feedback and packs, and log out. Other admin writes (settings, payments, WhatsApp, catalog, users) are refused
- Demo accounts cannot change their password

Demo mode also bypasses the payment gateway with a test checkout, and the data is reset daily. See [`OPERATIONS.md`](OPERATIONS.md#demo-mode). Never enable it on a database holding real data.

## Incident response

1. Suspected compromised provider: in the admin area, suspend the account, then send a password reset link.
2. Suspected leaked Sanctum token: delete the `personal_access_tokens` row and issue a new one.
3. Suspected compromised admin account: rotate `APP_KEY`, which invalidates all sessions. See [`OPERATIONS.md`](OPERATIONS.md#secrets-rotation) first.
4. Data breach: follow the notification duties of the applicable privacy law, and export `audit_logs` before any cleanup.

## Reports

Please report security concerns privately to the repository owner rather than in a public issue.
