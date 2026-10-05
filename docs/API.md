# Zawaj Pro — REST API v1

Base: `/api/v1` — JSON Content-Type, UTF-8.

## Auth

All endpoints except `/health` require a Sanctum Bearer token with the `n8n-service` ability:

```
Authorization: Bearer <token>
Accept: application/json
```

Issue a token via:

```
php artisan zawaj:create-api-token --name=n8n-service --abilities=n8n-service
```

Plaintext token is printed once at creation — store in n8n credentials. Token is revocable by the admin (deleting the `personal_access_tokens` row).

## Rate limits

- Read endpoints: 120 req/min per token
- Mutation endpoints (POST): 30 req/min per token

## Response envelope

Success:

```json
{
  "data": { ... },
  "meta": {
    "timestamp": "2026-04-21T14:00:00+01:00",
    "api_version": "v1"
  }
}
```

Error:

```json
{
  "error": {
    "code": "insufficient_credits",
    "message": "Crédits insuffisants pour accepter cette demande.",
    "details": { "required": 1, "available": 0 }
  },
  "meta": { ... }
}
```

## Endpoints

### GET /health

Public. Returns DB/cache/queue status.

### GET /wed-requests/{wedUid}

Returns the full WedRequest including `service_requests` array. Contact info (couple.whatsapp) included — intended for n8n's WhatsApp relay.

### GET /service-requests

Query params: `status`, `service_type`, `wilaya_code`, `limit` (max 100).

### GET /service-requests/pending-matching

Returns all ServiceRequests with `status=matching` — poll this from n8n if not using webhooks. Response includes `all_notified_provider_ids` and previous-round arrays so n8n knows who to exclude.

### GET /service-requests/{uid}

### POST /service-requests/{uid}/start-round

Body:

```json
{
  "round": 1,
  "provider_ids": [12, 34, 56]
}
```

- Transitions ServiceRequest into the given round via `RoundStateMachine::startRound`.
- Merges `provider_ids` into `all_notified_provider_ids`.
- Fires `RoundStarted` event → outbound webhook `service_request.round_started`.
- **Idempotency:** if `current_round >= round`, returns `409 already_advanced` with current state.

### POST /service-requests/{uid}/accept

Body: `{ "provider_id": 42, "via": "whatsapp" | "api" | "dashboard" }`

Returns 200 on success, or:
- `409 already_taken`
- `410 expired`
- `403 not_eligible`
- `402 insufficient_credits` (with `details: { required, available }`)

### POST /service-requests/{uid}/refuse

Body: `{ "provider_id": 42, "reason": "..." }`

### POST /service-requests/{uid}/force-open

Opens the service_request directly to the marketplace (admin escape hatch). Fires `LeadOpenedToMarketplace`.

### GET /providers/eligible

Query params: `service_type` (req), `wilaya_code` (req, 2 chars), `exclude_ids[]`, `limit` (default 10, max 20).

Ordered by `acceptance_rate DESC, rating_avg DESC, leads_accepted DESC`. Response excludes email/phone (sensitive).

### GET /providers/{id}

### GET /n8n/whatsapp-config

Meta WhatsApp Cloud API credentials configured in `/admin/whatsapp`, fetched by the n8n `zawaj-whatsapp` workflow on every run. Throttle: 600/min. Sent with `Cache-Control: no-store`.

```json
{ "data": { "enabled": true, "phone_number_id": "123456789012345", "access_token": "EAAG...", "verify_token": "...", "app_secret": "..." } }
```

`enabled` is the effective state: the admin switch is on **and** `phone_number_id` + `access_token` are set. Unset values come back as empty strings. This is the only endpoint that returns decrypted secrets; it requires the `n8n-service` ability like the rest.

## Sensitive fields policy

Never exposed via API: `users.password`, `users.remember_token`, `wed_requests.ip_address`, `wed_requests.user_agent`, provider `email`/`phone` in public provider resources (admin-only via admin panel).
