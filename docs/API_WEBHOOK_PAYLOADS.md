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
{
  "purchase_uid": "PUR-20260421-XXXX",
  "user_id": 42,
  "provider_id": 42,
  "provider_email": "...",
  "provider_name": "Studio Photo Alger",
  "preferred_language": "fr",
  "pack_name": "Pack 6 déblocages",
  "amount_dzd": 7650,
  "credits_granted": 6,
  "unlocks": 6,
  "completed_at": "2026-04-21T14:00:00+01:00",
  "receipt_pdf_url": "https://.../api/v1/n8n/receipts/PUR-20260421-XXXX.pdf"
}
```

`unlocks` is a backwards-compat alias of `credits_granted`. Not re-dispatched
once `purchases.receipt_emailed_at` is set (idempotency).

---

## Enriched WhatsApp events

Separate from the thin domain events above, `App\Listeners\Whatsapp\*` emits
three **enriched** events carrying everything the messaging channel needs:
E.164 phone numbers, preferred language, and **signed button payloads**.

Button payloads are `Crypt::encryptString` tokens minted by
`App\Support\Whatsapp\SignedButtonPayload` — n8n cannot generate them, so they
must travel in the payload and return verbatim on the button tap.

> ⚠️ The n8n WhatsApp workflow must subscribe to **these** names. The
> `service_request.*` equivalents carry identifiers only and contain nothing
> sendable.

### `round.started`

```json
{
  "service_request_id": "SRV-...",
  "round_number": 1,
  "expires_at": "2026-04-21T16:00:00+01:00",
  "couple": { "first_name": "...", "city": "..." },
  "service": { "type": "photo", "budget_hint_dzd": 1500000, "event_date": "2026-07-15" },
  "template_intent": "round_teaser",
  "recipients": [
    {
      "provider_id": 12,
      "phone_e164": "+213...",
      "preferred_language": "ar",
      "buttons": [
        { "id": "<encrypted>", "intent": "accept", "label_ar": "قبول", "label_fr": "Accepter" },
        { "id": "<encrypted>", "intent": "refuse", "label_ar": "رفض",  "label_fr": "Refuser"  }
      ]
    }
  ]
}
```

### `lead.accepted`

```json
{
  "service_request_id": "SRV-...",
  "winner": { "provider_id": 42, "business_name": "...", "phone_e164": "+213...", "preferred_language": "ar" },
  "couple": { "first_name": "...", "phone_e164": "+213...", "preferred_language": "fr" },
  "service": { "type": "photo", "event_date": "2026-07-15" },
  "template_intent": "winner_confirmation"
}
```

### `lead.opened_to_marketplace`

Same shape as `round.started` minus `round_number` / `expires_at`;
`template_intent` is `marketplace_broadcast`. Recipients are every eligible
provider not already notified, refused, or the winner.
