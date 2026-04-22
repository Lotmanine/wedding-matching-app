# Zawaj Pro — Security

## Threat model (V1)

| Actor | Capability | Mitigation |
|-------|-----------|-----------|
| Anonymous web visitor | form spam, scraping | form rate limit 3/hour/IP, login rate limit 5/min/IP+email, global 300/min/IP, CSP |
| Malicious provider | scrape leads, hijack others' unlocks | eligibility scope per provider, race-safe `lockForUpdate` on accept, credits/ledger append-only |
| Compromised provider account | run accepts on behalf | `last_active_at` telemetry; admin can suspend; rate limit 10/min/accept |
| Stolen Sanctum token | replay API calls | token abilities (n8n-service), rate limited 120/30 per min, easy revocation by deleting row |
| Chargily webhook spoofing | inject fake paid events | HMAC-SHA256 signature verification, 401 on mismatch |
| SQL injection | exfiltrate data | Eloquent + parameter binding everywhere, no raw interpolation |
| XSS | steal session | CSP `script-src 'self' + specific CDNs`, Blade `{{ }}` auto-escape |
| CSRF | trigger state changes | Laravel CSRF on all POST except `/webhooks/*` (those verify HMAC instead) |
| Clickjacking | embed admin pages | `X-Frame-Options: DENY`, CSP `frame-ancestors 'none'` |
| Mass assignment | overwrite sensitive fields | All models use explicit `$fillable` |

## Security headers

`App\Http\Middleware\SecurityHeaders` appends on every response:
- `X-Frame-Options: DENY`
- `X-Content-Type-Options: nosniff`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: geolocation=(), microphone=(), camera=()`
- `X-XSS-Protection: 1; mode=block`

Production-only (app environment = `production`):
- `Strict-Transport-Security: max-age=31536000; includeSubDomains; preload`
- `Content-Security-Policy` — allowlist of fonts.googleapis.com, cdn.jsdelivr.net, cdnjs.cloudflare.com, pay.chargily.net

## Session & cookies

- `SESSION_SECURE_COOKIE=true` in production
- httpOnly: true (Laravel default)
- sameSite: `lax` (Laravel default)
- Lifetime 120 min
- Rotate on login via `$request->session()->regenerate()`

## Transport

- HTTPS enforced via `URL::forceScheme('https')` in production
- Coolify terminates TLS at Traefik; Laravel trusts `X-Forwarded-*` via `trustProxies`

## Authentication

- Passwords: bcrypt rounds 12
- Provider login: role check post-auth (non-provider immediately logged out)
- Admin login: separate route + role check, dark UI to distinguish
- Password resets: signed URL tokens via Laravel Password broker, 60-minute TTL

## Authorization

- Spatie Permissions role middleware (`role:admin`, `role:provider`) on route groups
- Sanctum token abilities (`n8n-service`) for API
- Ownership checks on every provider-facing `show` action (purchase, lead, profile)
- Lead eligibility re-checked inside the `DB::transaction` on accept (race-safe)

## ANPDP (loi 18-07) compliance

- Couple consent checkbox required at form submission
- 14-day hard-cap retention — `zawaj:cleanup-expired` soft-deletes + cascades
- Couple data fields never exposed via API to providers who haven't unlocked
- Contact info blurred pre-unlock, fully revealed only to the unlocking provider
- Admin views of couple data are audit-logged per access

## Secret management

- `.env` never committed (gitignored from Prompt 1)
- `.env.production.example` has only empty placeholders
- Sanctum token plaintexts shown once on creation, stored only as hash
- Webhook endpoint secrets stored plain (needed for HMAC computation); guard DB access

## Incident response

1. Suspected compromised provider → admin → suspend + reset password link
2. Suspected leaked Sanctum token → delete `personal_access_tokens` row, issue new one
3. Suspected compromised admin account → force rotate `APP_KEY` (invalidates all sessions)
4. Data breach → ANPDP notification clock starts; export audit_logs before any cleanup

## Reports

Any security concerns → `security@zawajpro.dz`
