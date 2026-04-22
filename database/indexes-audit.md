# Expected indexes per table

Run `php artisan zawaj:audit-indexes` to verify these exist.

## users

- PRIMARY `id`
- UNIQUE `email`
- INDEX `role`
- INDEX `status`
- INDEX `wilaya_code`

## wed_requests

- PRIMARY `id`
- UNIQUE `wed_uid`
- INDEX `status`
- INDEX `couple_wilaya_code`
- INDEX `wedding_date`
- INDEX `expires_at`
- INDEX `deleted_at` (from SoftDeletes)

## service_requests

- PRIMARY `id`
- UNIQUE `service_uid`
- UNIQUE `svc_wed_type_unique (wed_request_id, service_type)`
- FK INDEX `wed_request_id`
- FK INDEX `unlocked_by_user_id` (`svc_unlocked_by_idx`)
- INDEX `status`
- INDEX `service_type`
- INDEX `current_round`
- INDEX `round_started_at`
- INDEX `deleted_at`

## provider_profiles

- PRIMARY `id`
- UNIQUE `user_id`
- INDEX `primary_wilaya_code`
- INDEX `price_tier`
- INDEX `verified`
- INDEX `rating_avg`

## provider_services

- PRIMARY `id`
- UNIQUE `prov_svc_unique (user_id, service_type)`
- INDEX `service_type`

## provider_coverage

- PRIMARY `id`
- UNIQUE `prov_cov_unique (user_id, wilaya_code)`
- INDEX `wilaya_code`

## credit_ledger

- PRIMARY `id`
- FK INDEX `user_id`, `created_by_user_id`
- INDEX `type`
- INDEX `ledger_reference_idx (reference_type, reference_id)`

## purchases

- PRIMARY `id`
- UNIQUE `purchase_uid`
- FK INDEX `user_id`, `unlock_pack_id`, `credit_ledger_id`
- INDEX `status`
- INDEX `pur_gateway_checkout_idx (gateway_checkout_id)`

## unlock_packs

- PRIMARY `id`
- UNIQUE `slug`

## wilayas / communes

- PRIMARY `id` (explicit, not auto-increment)
- UNIQUE `wilayas.code`
- FK INDEX `communes.wilaya_id`

## webhook_endpoints / webhook_deliveries

- PRIMARY `id`
- FK INDEX `webhook_endpoint_id` (`wh_delivery_endpoint_idx`)
- INDEX `event_type`
- COMPOSITE `wh_delivery_status_idx (delivered_at, failed_at)`

## audit_logs

- PRIMARY `id`
- INDEX `action`
- INDEX `audit_user_idx (user_id)`
- COMPOSITE `audit_subject_idx (subject_type, subject_id)`

## personal_access_tokens (Sanctum)

- PRIMARY `id`
- UNIQUE `token`
- INDEX `(tokenable_type, tokenable_id)`
