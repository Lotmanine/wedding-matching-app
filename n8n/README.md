# n8n workflows

Importable n8n workflows for the Wedding Matching App: the matching, the WhatsApp channel and the monitoring. The files hold no credentials: secrets are read at run time from `$env` variables, or from Laravel for the Meta values.

| File | Workflow | Triggers | What it does |
|------|----------|----------|--------------|
| `workflow-shared-error-handler.json` | `wedding-error-handler` | Error Trigger | Receives the errors of the other two workflows and posts them to `POST /api/webhooks/n8n/error` |
| `workflow-core.json` | `wedding-core` | `POST /webhook/wedding-core`, 30-minute schedule | Ranks providers with Claude, starts rounds, opens requests to the marketplace, e-mails purchase receipts, checks the app's health and records a heartbeat |
| `workflow-whatsapp.json` | `wedding-whatsapp` | `POST /webhook/wedding-whatsapp-outbound`, `GET` and `POST /webhook/wedding-whatsapp-inbound` | Sends WhatsApp templates through Meta, receives button taps and delivery statuses and forwards them to Laravel |

`.env.n8n.example` lists the variables.

## Import

The instance must allow `$env` in nodes (`N8N_BLOCK_ENV_ACCESS_IN_NODE=false`).

1. Set the variables from `.env.n8n.example` in the n8n environment.
2. In n8n, go to **Workflows → Import from File** and import, in this order: `workflow-shared-error-handler.json`, `workflow-core.json`, `workflow-whatsapp.json`. They arrive deactivated.
3. In `wedding-core` and `wedding-whatsapp`, open **Settings → Error Workflow** and select `wedding-error-handler`. The JSON holds a name, not an n8n id, so this has to be set by hand.
4. In `wedding-core`, attach an SMTP credential to the *Send Receipt Email* and *Send Alert Email* nodes.
5. Activate the three workflows.
6. In Laravel, create two endpoints in `/admin/webhooks`, each with `WEDDING_WEBHOOK_SECRET` as its secret:

   | Name | URL | Events |
   |------|-----|--------|
   | n8n Core | `https://<n8n>/webhook/wedding-core` | `wed_request.submitted`, `service_request.round_timed_out`, `purchase.completed` |
   | n8n WhatsApp | `https://<n8n>/webhook/wedding-whatsapp-outbound` | `round.started`, `lead.accepted`, `lead.opened_to_marketplace` |

7. For WhatsApp, enter the Meta values in `/admin/whatsapp` and turn the switch on. Then set `https://<n8n>/webhook/wedding-whatsapp-inbound` as the webhook URL in the Meta app. The four message templates must be approved by Meta: `round_teaser_v1`, `winner_confirmation_provider_v1`, `winner_confirmation_couple_v1` and `marketplace_broadcast_v1`. While the switch is off, sending stops at the *Gate WhatsApp Enabled* node without an error.

## Signature checks

`wedding-core` and `wedding-whatsapp` verify `X-Wedding-Signature` before doing anything else. A Crypto node computes the HMAC over the webhook's raw body, and a Code node compares it with the header. Meta's `X-Hub-Signature-256` is checked the same way, using the app secret from `/admin/whatsapp`.

The payload contracts are in [`docs/API_WEBHOOK_PAYLOADS.md`](../docs/API_WEBHOOK_PAYLOADS.md).
