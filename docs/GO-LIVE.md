# Zawaj Pro — Go-Live Status & Checklist

_Last updated: 2026-06-22_

This tracks the path to production. Code/design work that could be done in-repo is **done**
(see "Completed" below). What remains is provisioning that requires the client's own
accounts, secrets, and infrastructure — Claude cannot do these.

---

## ✅ Completed (in-repo)

### Full faithful re-skin → clay/ink editorial identity
The app was re-skinned from the old green/gold theme to the `claude-design` mockups
(near-black `#1A1A1A` ink + clay `#C8956C`, Noto Serif + Plus Jakarta Sans, Material Symbols).

- **Foundation:** `tailwind.config.js` (named `z-*` tokens + fonts), `resources/css/app.css`
  (base styles, scrollbar, focus, Material Symbols), reusable components in
  `resources/views/components/z/` (`btn`, `badge`, `card`, `icon`). Documented in `docs/DESIGN-SYSTEM.md`.
- **Layouts:** public (`couple`), `provider` (sidebar + credit card + mobile bottom nav),
  `admin` (ink sidebar, clay active), `guest`/auth, plus `partials/public-footer`.
- **Screens:** landing, `/demande` multi-step Livewire form + confirmation, all provider
  screens (dashboard, leads, credits, profile), all admin screens, all auth/Breeze/profile
  screens, receipt PDF. All `wire:`/route/validation logic preserved; the previously
  English profile delete form is now French.
- Verified: `php artisan view:cache` compiles all views; `npm run build` succeeds.

### Phase-0 functional fixes
- **Workflow-2 event-name mismatch (was a hard blocker):** n8n workflow-2 + its fixtures +
  doc now route on Laravel's real event names (`service_request.round_started` / `.accepted`
  / `.opened_to_marketplace`). WhatsApp outbound will now actually fire.
- **n8n error webhook hardened:** `POST /api/webhooks/n8n/error` now requires a constant-time
  `X-Zawaj-Token` shared secret (`config('zawaj.n8n.webhook_secret')` ← `ZAWAJ_WEBHOOK_SECRET`)
  when configured. The shared error-handler workflow sends the header. New tests cover accept + reject.
- **Legal/ANPDP pages:** `/confidentialite` and `/conditions-utilisation` (routes `legal.privacy`,
  `legal.terms`) built from the Legal.jsx content; footer links wired (no more dead `#`).
- **Branded error pages:** 404, 500, 403, 419, 429, 503 (shared `errors/layout`).
- **Admin seeder:** no more hardcoded `'password'` in production — uses `ADMIN_PASSWORD` or
  generates a strong random one and prints it once.
- **Backups:** guarded schedule in `routes/console.php` (auto-activates once
  `spatie/laravel-backup` is installed).
- **CI:** `.github/workflows/ci.yml` (install, build, Pint, tests on every push/PR).
- **Data check:** the wilaya dataset is the **2025 reform set with 69 wilayas** (intentional,
  not stale); copy corrected to "69 wilayas".

### Tests
`php artisan test` → **223 passing (624 assertions)**.

> Note: a "couple confirmation email" was intentionally NOT built — `wed_requests` has no
> email field; couples are contacted by WhatsApp by design, with an on-screen confirmation.
> Password-reset/receipt emails work via Breeze/n8n once SMTP is configured.

---

## ⛔ Remaining — requires the client (provisioning & deploy)

These need real accounts/secrets and cannot be done from the repo. Start the WhatsApp
templates first (multi-day approval).

### 1. External accounts & secrets
- [ ] **Meta WhatsApp Cloud API:** business number, system-user access token, verify token,
      app secret; submit the **4 message templates** (ar+fr) for approval — _longest lead time_.
- [ ] **Anthropic API key** (matching ranking in workflow-1). Without it, no matching at all.
- [ ] **Chargily** live account → `CHARGILY_API_KEY`, `CHARGILY_SECRET_KEY`,
      `CHARGILY_WEBHOOK_SECRET`; set `CHARGILY_MODE=live`; register webhook URL.
- [ ] **SMTP** provider (+ SPF/DKIM/DMARC) and **Telegram** bot (token + chat id).
- [ ] Generate `ZAWAJ_WEBHOOK_SECRET` (used for outbound webhook signing AND the n8n
      error-webhook token) and set it in both Laravel `.env` and n8n env.

### 2. Infrastructure
- [ ] Provision DB (MySQL) + Redis; register domain `zawajpro.dz` + DNS + TLS.
- [ ] Fill all of `.env.production.example`; `php artisan key:generate`; set `APP_ENV=production`,
      `APP_DEBUG=false`, `APP_URL=https://…`, `SESSION_SECURE_COOKIE=true`.

### 3. Deploy (Coolify) — see `deployment/RUNBOOK.md`
- [ ] Deploy; run `migrate` + `db:seed` (seeds 69 wilayas, 1541 communes, roles, packs, admin).
- [ ] `php artisan storage:link`; confirm **scheduler** AND **queue worker** are running.
- [ ] `composer require spatie/laravel-backup` + publish config (backup schedule auto-activates).
- [ ] Create the n8n Sanctum token (`php artisan zawaj:create-api-token --abilities=n8n-service`).
- [ ] Import the 5 n8n workflows; set n8n env vars; register webhook endpoints in `/admin/webhooks`.
- [ ] Reset the admin password; run `php artisan zawaj:smoke-test` + `zawaj:audit-production` (green).

### 4. End-to-end soak (staging, 24–48h)
- [ ] Full trace: submit wedding → workflow-1 ranks (Claude) → round starts →
      **WhatsApp sends** → provider taps Accept → credit deducted → confirmations →
      buy a pack via Chargily test → receipt email. Confirm round timeout auto-advances.

### 5. Cutover
- [ ] Flip Chargily to live, point DNS, deploy, one real low-value transaction, announce.
