# Zawaj Pro — Outbound webhook payloads

Laravel fires events → `WebhookEventListener` → `WebhookDispatcher` queues `DeliverWebhookJob` per subscribed endpoint.

## Envelope

All payloads share this envelope:

```json
{
  "event": "<event_type>",
  "timestamp": "2026-04-21T14:00:00+01:00",
  "data": { ... event-specific payload ... }
}
```

## Headers

- `X-Zawaj-Event: <event_type>`
- `X-Zawaj-Signature: <hmac_sha256(body, endpoint_secret)>`
- `X-Zawaj-Delivery-Id: <uuid>`
- `Content-Type: application/json`

Verify HMAC on receipt before trusting payload.

## Retry policy

6 tries, exponential backoff starting at 30s, capped at 1 hour. Deliveries logged to `webhook_deliveries`.

## Event types

### `wed_request.submitted`

```json
{
  "wed_uid": "WED-20260421-A3F2",
  "couple": { "first_name": "...", "whatsapp": "+213...", "wilaya_code": "16", "commune": "..." },
  "wedding": { "date": "2026-07-15", "season": "Été", "guest_count": "from_200_to_300", "budget_range": "from_1m_to_2m" },
  "services_needed": ["photo", "traiteur", "salle"],
  "description": "...",
  "service_requests": [
    { "service_uid": "SRV-...", "service_type": "photo", "status": "new" }
  ],
  "expires_at": "2026-05-05T14:00:00+01:00"
}
```

### `service_request.round_started`

```json
{ "service_uid": "SRV-...", "round": 1, "provider_ids": [12, 34, 56] }
```

### `service_request.round_timed_out`

```json
{ "service_uid": "SRV-...", "previous_round": 2, "current_status": "matching" }
```

### `service_request.accepted`

```json
{ "service_uid": "SRV-...", "winner_id": 42, "winner_email": "...", "credits_charged": 1, "unlocked_via": "api" }
```

### `service_request.refused`

```json
{ "service_uid": "SRV-...", "refuser_id": 12 }
```

### `service_request.opened_to_marketplace`

```json
{ "service_uid": "SRV-..." }
```

### `provider.registered`

```json
{ "user_id": 77, "business_name": "...", "email": "...", "wilaya_code": "16" }
```

### `purchase.completed`

```json
{ "purchase_uid": "PUR-20260421-XXXX", "user_id": 42, "amount_dzd": 7650, "unlocks": 6 }
```
