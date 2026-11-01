# Wedding Matching App: REST API v1

Base path: `/api/v1`. Requests and responses are JSON, UTF-8.

## Auth

All endpoints except `GET /health` require a Sanctum Bearer token with the `n8n-service` ability:

```
Authorization: Bearer <token>
Accept: application/json
```

Issue a token with:

```
php artisan wedding:create-api-token --name=n8n-service --abilities=n8n-service
```

The plaintext token is printed once, at creation. Store it in n8n. To revoke a token, delete its `personal_access_tokens` row.

## Rate limits

- Every authenticated route shares the `api` limiter: 120 requests/min per token.
- `POST /service-requests/{uid}/*` routes also use the `api-mutations` limiter: 30 requests/min per token.
- Some routes have their own limiter, listed below. The shared `api` limiter still applies to them, so a higher figure does not raise the limit above 120/min.

## Response envelope

Success:

```json
{
  "data": { },
  "meta": {
    "timestamp": "2026-11-02T14:00:00+00:00",
    "api_version": "v1"
  }
}
```

Error:

```json
{
  "error": {
    "code": "insufficient_credits",
    "message": "Not enough credits (needed: 1, available: 0). Buy credits to continue.",
    "details": { "required": 1, "available": 0 }
  },
  "meta": { }
}
```

Validation failures use Laravel's default 422 format (`message` and `errors`), not this envelope.

## Reference values

- `service_type`: `photo`, `video`, `catering`, `decoration`, `pastry`, `venue`, `music`, `beauty`
- `status`: `new`, `matching`, `round1`, `round2`, `round3`, `open_marketplace`, `accepted`, `expired`, `deleted`
- `city_code`: two characters. The seeded cities are `NY`, `LA`, `CH`, `MI` (USA) and `PA`, `LY`, `MA`, `NI` (France)
- `season`: `spring`, `summer`, `autumn`, `winter`
- `guest_count`: `under_100`, `from_100_to_200`, `from_200_to_300`, `from_300_to_500`, `over_500`
- `budget_range`: `under_10k`, `from_10k_to_20k`, `from_20k_to_35k`, `from_35k_to_50k`, `from_50k_to_100k`, `over_100k`

## Resources

### WedRequest

```json
{
  "wed_uid": "WED-20261102-A3F2",
  "status": "new",
  "couple": { "first_name": "...", "whatsapp": "+1212...", "city_code": "NY", "district": "Manhattan" },
  "wedding": { "date": "2027-06-15", "season": "summer", "guest_count": "from_100_to_200", "budget_range": "from_20k_to_35k" },
  "services_needed": ["photo", "catering", "venue"],
  "description": "...",
  "service_requests": [],
  "expires_at": "2026-11-16T14:00:00+00:00",
  "created_at": "2026-11-02T14:00:00+00:00"
}
```

`service_requests` is included only when the relation is loaded.

### ServiceRequest

```json
{
  "service_uid": "SRV-20261102-B7C1",
  "wed_request_uid": "WED-20261102-A3F2",
  "wed_request": { },
  "service_type": "photo",
  "status": "round1",
  "current_round": 1,
  "round_started_at": "2026-11-02T14:00:00+00:00",
  "round_1_provider_ids": [12, 34, 56],
  "round_2_provider_ids": [],
  "round_3_provider_ids": [],
  "all_notified_provider_ids": [12, 34, 56],
  "refused_by_provider_ids": [],
  "unlocked_by_user_id": null,
  "unlocked_at": null,
  "unlocked_via": null,
  "credits_charged": 0,
  "expires_at": "2026-11-16T14:00:00+00:00",
  "created_at": "2026-11-02T14:00:00+00:00"
}
```

`wed_request_uid` and `wed_request` are included only when the relation is loaded.

### Provider

```json
{
  "id": 12,
  "name": "...",
  "business_name": "...",
  "primary_city_code": "NY",
  "price_tier": "mid",
  "min_price": 1500,
  "max_price": 6000,
  "rating": { "average": 4.7, "count": 18 },
  "stats": { "leads_received": 40, "leads_accepted": 22, "acceptance_rate": 55.0 },
  "portfolio_url": "https://...",
  "portfolio_images": [],
  "languages": ["en"],
  "verified": true,
  "services": ["photo", "video"],
  "coverage": [{ "city_code": "NY", "travel_fee": 0 }]
}
```

`price_tier` is `budget`, `mid`, `premium` or `luxury`. `acceptance_rate` is a percentage. `services` and `coverage` are included only when the relations are loaded.

## Endpoints

### GET /health

Public. Returns `status` (`ok` or `degraded`), `app_version`, `db`, `cache` and `queue` (the name of the queue driver).

### GET /wed-requests/{wedUid}

Returns the WedRequest with its `service_requests`. It includes the couple's contact number (`couple.whatsapp`) for n8n's WhatsApp relay.

### GET /service-requests

Query params: `status`, `service_type`, `city_code`, `limit` (default 25, max 100). Each row includes its `wed_request`.

### GET /service-requests/pending-matching

Returns up to 100 ServiceRequests with `status=matching`. Each row includes `all_notified_provider_ids` and the per-round arrays, so a caller knows which providers to exclude.

### GET /service-requests/{uid}

Returns one ServiceRequest with its `wed_request`.

### POST /service-requests/{uid}/start-round

Body:

```json
{
  "round": 1,
  "provider_ids": [12, 34, 56]
}
```

`round` is 1 to 3. `provider_ids` holds 1 to 10 existing user ids.

- Moves the ServiceRequest into the given round through `RoundStateMachine::startRound`.
- Merges `provider_ids` into `all_notified_provider_ids`.
- Fires `RoundStarted`, which sends the `service_request.round_started` and `round.started` webhooks.
- **Idempotency:** if `current_round >= round`, returns `409 already_advanced` with `current_round` and `current_status` in `details`.

### POST /service-requests/{uid}/accept

Body: `{ "provider_id": 42, "via": "whatsapp" }`. `via` is `whatsapp`, `api` or `dashboard`.

On success, returns `status`, `success`, `message`, `service_uid`, `cost` and `available`. Otherwise:

- `409 already_taken`
- `410 expired`
- `403 not_eligible`
- `402 insufficient_credits` (with `details: { required, available }`)

### POST /service-requests/{uid}/refuse

Body: `{ "provider_id": 42, "reason": "..." }`. `reason` is optional, 500 characters at most.

On success, returns `status` (`success` or `already_refused`), `success`, `message` and `service_uid`. Returns `409 not_eligible` when the provider cannot refuse this request.

### POST /service-requests/{uid}/force-open

Opens the ServiceRequest to the marketplace (escape hatch) and fires `LeadOpenedToMarketplace`. Works from `new`, `matching`, `round1`, `round2` and `round3`; any other status returns `409 invalid_state`.

### GET /providers/eligible

Query params: `service_type` (required), `city_code` (required, 2 characters), `exclude_ids[]`, `limit` (default 10, max 20).

Returns active, verified, non-suspended providers offering the service and covering the city, as Provider resources. Ordered by `acceptance_rate DESC, rating_avg DESC, leads_accepted DESC`.

### GET /providers/ranking-candidates

The enriched list n8n sends to Claude for ranking.

Query params: `service_type` (required), `city_code` (required, 2 characters), `exclude_ids` (array or comma-separated string), `limit` (default 20, max 50), `service_request_uid` (optional).

```json
{
  "data": {
    "providers_per_round": 3,
    "service_type": "photo",
    "city_code": "NY",
    "city_name": "New York",
    "couple_district": "Manhattan",
    "wedding_date": "2027-06-15",
    "guest_count": "from_100_to_200",
    "budget_range": "from_20k_to_35k",
    "description": "...",
    "candidates": [
      {
        "id": 12,
        "business_name": "...",
        "business_description": "...",
        "price_tier": "mid",
        "min_price": 1500,
        "max_price": 6000,
        "years_experience": 8,
        "languages": ["en"],
        "rating_avg": 4.7,
        "rating_count": 18,
        "acceptance_rate": 55.0,
        "leads_accepted": 22,
        "portfolio_url": "https://...",
        "portfolio_images": [],
        "primary_city_code": "NY",
        "primary_city_name": "New York",
        "coverage_cities": ["NY"],
        "distance_from_couple": "same_city"
      }
    ]
  }
}
```

- `providers_per_round` comes from `/admin/settings`.
- `couple_district`, `wedding_date`, `guest_count`, `budget_range` and `description` are present only when `service_request_uid` is sent.
- `distance_from_couple` is `same_city`, `adjacent` (from `config/city-adjacency.php`) or `far`.

### GET /providers/{id}

Returns one Provider resource, or `404 not_found`.

### POST /whatsapp/inbound/button-tap

Route limiter: 240/min. n8n forwards a provider's button tap here.

```json
{
  "wa_message_id": "wamid...",
  "from_phone": "+12125550142",
  "button_payload": "<encrypted token>",
  "button_text": "Accept",
  "in_reply_to": "wamid...",
  "timestamp": "1793628000"
}
```

`button_text` and `in_reply_to` are optional. The endpoint always returns 200 once the body is valid, with one of these `status` values:

- `accepted` or `refused`, with `service_request_uid`, `provider_id` and `result` (the outcome from `LeadAcceptanceService`)
- `already_processed`, when this `wa_message_id` was already handled
- `rejected`, with `reason`: `tampered`, `mismatched_phone`, `round_closed`, `already_won` or `expired`

A button token is valid for 7 days, and the sender's number must match the provider's WhatsApp number.

### POST /whatsapp/inbound/status-update

Route limiter: 600/min. Body: `wa_message_id`, `status` (`sent`, `delivered`, `read`, `failed`), `recipient_id` (optional, E.164), `timestamp`, `error` (optional).

Returns `status`: `updated`, `no_change`, `regression_blocked` or `unknown_wamid`. `failed` always wins; other statuses only move forward.

### POST /webhooks/whatsapp/delivery-status

Route limiter: 120/min. Records the outcome of outbound sends.

```json
{
  "event": "round.started",
  "service_request_id": "SRV-20261102-B7C1",
  "results": [
    {
      "role": "provider",
      "recipient_phone_e164": "+12125550142",
      "template_intent": "round_teaser",
      "provider_id": 12,
      "wa_message_id": "wamid...",
      "status": "sent",
      "error": null
    }
  ]
}
```

`results` holds 1 to 200 items. `role` is `provider` or `couple`; `status` is `queued`, `sent`, `delivered`, `read` or `failed`. Returns `recorded` and `duplicate` counts.

### GET /admin/ops/health-snapshot

Route limiter: 4/min. Returns `severity` (`none`, `warn`, `critical`), `run_at`, `stuck_matching`, `failed_webhooks_24h`, `failed_whatsapp_24h` and `n8n_errors_24h`. Thresholds are in `config/ops.php`.

### POST /webhooks/n8n/heartbeat

Route limiter: 4/min. Body: `run_at`, `severity` (`none`, `warn`, `critical`), `snapshot_summary` (object with `stuck_matching`, `failed_webhooks_24h`, `failed_whatsapp_24h`, `n8n_errors_24h`), `alerts_sent` (array). Returns `status: recorded` and `id`.

### GET /n8n/receipts/{purchaseUid}.pdf

Route limiter: 60/min. Streams the purchase receipt as a PDF, sent with `Cache-Control: no-store`.

### PATCH /n8n/receipts/{purchaseUid}/mark-emailed

Route limiter: 60/min. Body: `sent_at`, `smtp_message_id` (optional). Returns `status`: `marked` or `already_marked`.

### GET /n8n/whatsapp-config

Route limiter: 600/min. Returns the Meta WhatsApp Cloud API credentials configured in `/admin/whatsapp`. The n8n `wedding-whatsapp` workflow fetches them on every run. Sent with `Cache-Control: no-store`.

```json
{ "data": { "enabled": true, "phone_number_id": "123456789012345", "access_token": "...", "verify_token": "...", "app_secret": "..." } }
```

`enabled` is the effective state: the admin switch is on **and** `phone_number_id` and `access_token` are set. Unset values come back as empty strings. This is the only endpoint that returns decrypted secrets.

## n8n error webhook

`POST /api/webhooks/n8n/error` sits outside `/api/v1` and does not use Sanctum. It is guarded by a shared secret in the `X-Wedding-Token` header, compared with `WEDDING_WEBHOOK_SECRET`, and limited to 60 requests/min.

If the secret is not set, the request is refused with 503, except in the `local` and `testing` environments. A wrong token returns 401.

The body is free-form JSON. The shared error-handler workflow sends `workflow`, `execution_id`, `error_message`, `failed_node`, `stack` and `timestamp`. The endpoint writes to `storage/logs/n8n-errors.log`, adds an `n8n.error` audit entry, and returns `received`, `recent_1h` and `alert_threshold` (true from 5 errors in the last hour).

## Sensitive fields policy

Never exposed through the API: `users.password`, `users.remember_token`, `wed_requests.ip_address`, `wed_requests.user_agent`, and provider `email` and `phone` (Provider resources and ranking candidates leave them out).
