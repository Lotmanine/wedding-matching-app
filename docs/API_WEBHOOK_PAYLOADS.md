# Wedding Matching App: outbound webhook payloads

Laravel fires events, `WebhookEventListener` and the `App\Listeners\Whatsapp\*` listeners build the payloads, and `WebhookDispatcher` queues one `DeliverWebhookJob` per active endpoint subscribed to the event.

Endpoints are managed in `/admin/webhooks`. An endpoint subscribes to exact event names; there are no wildcards.

## Envelope

All payloads share this envelope:

```json
{
  "event": "<event_type>",
  "timestamp": "2026-11-02T14:00:00+00:00",
  "data": { }
}
```

The examples below show the content of `data`.

## Headers

- `X-Wedding-Event: <event_type>`
- `X-Wedding-Signature: <hmac_sha256(raw_body, endpoint_secret)>`
- `X-Wedding-Delivery-Id: <webhook_deliveries row id>`
- `Content-Type: application/json`

Verify the HMAC on the raw request body before trusting a payload. Re-serialising the JSON can change the bytes that were signed.

## Retry policy

6 tries, with exponential backoff starting at 30 seconds and capped at 1 hour. A non-2xx response or a connection error counts as a failure. Deliveries are logged in `webhook_deliveries` and can be replayed from `/admin/webhooks/{id}/deliveries` or with `php artisan wedding:replay-webhook {deliveryId}`.

## Event types

### `wed_request.submitted`

```json
{
  "wed_uid": "WED-20261102-A3F2",
  "couple": { "first_name": "...", "whatsapp": "+1212...", "city_code": "NY", "district": "Manhattan" },
  "wedding": { "date": "2027-06-15", "season": "summer", "guest_count": "from_200_to_300", "budget_range": "from_20k_to_35k" },
  "services_needed": ["photo", "catering", "venue"],
  "description": "...",
  "service_requests": [
    { "service_uid": "SRV-20261102-B7C1", "service_type": "photo", "status": "new" }
  ],
  "expires_at": "2026-11-16T14:00:00+00:00"
}
```

`season` is `spring`, `summer`, `autumn` or `winter`. The other enum values are listed in [`API.md`](API.md#reference-values).

### `service_request.round_started`

```json
{ "service_uid": "SRV-...", "round": 1, "provider_ids": [12, 34, 56] }
```

### `service_request.round_timed_out`

```json
{ "service_uid": "SRV-...", "previous_round": 2, "current_status": "round2" }
```

The event fires before the round is advanced, so `current_status` is still the status of the round that timed out.

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
{ "user_id": 77, "business_name": "...", "email": "...", "city_code": "NY" }
```

`city_code` is the provider's primary city.

### `purchase.completed`

```json
{
  "purchase_uid": "PUR-20261102-XXXX",
  "user_id": 42,
  "provider_id": 42,
  "provider_email": "...",
  "provider_name": "Hudson Light Studio",
  "preferred_language": "en",
  "pack_name": "Essential",
  "amount": 99,
  "currency": "USD",
  "credits_granted": 6,
  "unlocks": 6,
  "completed_at": "2026-11-02T14:00:00+00:00",
  "receipt_pdf_url": "https://.../api/v1/n8n/receipts/PUR-20261102-XXXX.pdf"
}
```

- `amount` is in whole units of `currency` (`USD` or `EUR`).
- `preferred_language` is `fr` when the provider lists French but not English, otherwise `en`.
- `unlocks` is a backwards-compatible alias of `credits_granted`.
- The event is not sent once `purchases.receipt_emailed_at` is set (idempotency), and never in demo mode.

---

## Enriched WhatsApp events

Separate from the thin domain events above, `App\Listeners\Whatsapp\*` emits three **enriched** events carrying everything the messaging channel needs: E.164 phone numbers, preferred language and **signed button payloads**.

Button payloads are `Crypt::encryptString` tokens minted by `App\Support\Whatsapp\SignedButtonPayload`. n8n cannot generate them, so they travel in the payload and come back verbatim when the button is tapped.

The n8n WhatsApp workflow must subscribe to **these** names. The `service_request.*` equivalents carry identifiers only and contain nothing sendable.

### `round.started`

```json
{
  "service_request_id": "SRV-...",
  "round_number": 1,
  "expires_at": "2026-11-02T16:00:00+00:00",
  "couple": { "first_name": "...", "city": "..." },
  "service": { "type": "photo", "budget_hint": 27500, "event_date": "2027-06-15" },
  "template_intent": "round_teaser",
  "recipients": [
    {
      "provider_id": 12,
      "phone_e164": "+1212...",
      "preferred_language": "en",
      "buttons": [
        { "id": "<encrypted>", "intent": "accept", "label_en": "Accept", "label_fr": "Accepter" },
        { "id": "<encrypted>", "intent": "refuse", "label_en": "Refuse", "label_fr": "Refuser" }
      ]
    }
  ]
}
```

- `expires_at` is the end of the round.
- `couple.city` is the couple's district when they gave one, otherwise the city name.
- `service.budget_hint` is the midpoint of the couple's budget range in whole currency units, or `null` when the range is unknown. The payload has no currency field.
- `recipients` holds the round's providers who are active, verified and not suspended. If there are none, the event is not sent.
- `preferred_language` is `en` when the provider lists English, then `fr`, then their first listed language. It defaults to `en`.

### `lead.accepted`

```json
{
  "service_request_id": "SRV-...",
  "winner": { "provider_id": 42, "business_name": "...", "phone_e164": "+1212...", "preferred_language": "en" },
  "couple": { "first_name": "...", "phone_e164": "+1212...", "preferred_language": "fr" },
  "service": { "type": "photo", "event_date": "2027-06-15" },
  "template_intent": "winner_confirmation"
}
```

The couple's `preferred_language` is currently always `fr`: the form does not collect a language.

### `lead.opened_to_marketplace`

Same shape as `round.started` minus `round_number` and `expires_at`; `template_intent` is `marketplace_broadcast`. Recipients are the active, verified, non-suspended providers who offer the service and cover the couple's city, excluding anyone already notified, anyone who refused, and the winner. If there are none, the event is not sent.
