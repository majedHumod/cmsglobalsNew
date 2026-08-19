# Phase 4 Operability Runbook

## 1) Health Monitoring

- Run on demand:
  - `php artisan system:health-check`
  - `php artisan system:health-check --json`
- Automated schedule:
  - Hourly via scheduler in `app/Console/Kernel.php`.

Expected checks:
- database connectivity
- cache read/write
- queue backlog estimate
- webhook failure count (last 24h)

## 2) Security Secrets Validation

- Run:
  - `php artisan security:check-secrets --fail-on-missing`

Required env keys (all environments):
- `APP_KEY`
- `STRIPE_SECRET_KEY`
- `VAPID_PUBLIC_KEY`

`COMMUNICATION_WEBHOOK_SECRET` (in `config/services.php` as `services.communication_webhook.secret`):
- **Required in `APP_ENV=production`**: webhook signature verification is enforced; set the shared secret and send `X-Webhook-Signature: HMAC-SHA256` of the raw request body.
- **Optional in local / staging / testing**: requests pass without a secret; if the secret is set, signatures are still verified (useful for integration tests).

## 3) Performance Baseline

- Run:
  - `php artisan performance:baseline`
  - `php artisan performance:baseline --json`

Track over time:
- active client query duration
- habit logs query duration
- upcoming bookings query duration
- notification query duration

## 4) Tenant Migration Preflight

- Preflight only:
  - `php artisan tenant:preflight`
  - `php artisan tenant:preflight --fail-on-issue`
- Migrate all with preflight (default):
  - `php artisan tenant:migrate-all`
- Skip preflight only when needed:
  - `php artisan tenant:migrate-all --skip-preflight`

## 5) Incident Response

### Queue backlog spikes
- Check: `php artisan system:health-check`
- Confirm workers are running and consume queue.
- If backlog remains high, scale queue workers.

### Webhook failures spike
- Inspect `integration_webhook_logs` recent failed rows.
- Verify sender signature matches `COMMUNICATION_WEBHOOK_SECRET`.
- Reprocess failed payloads from logs if provider supports replay.

### Tenant migration failures
- Run `tenant:preflight` and resolve missing databases first.
- Retry single tenant migration with:
  - `php artisan tenant:migrate <tenant_domain>`

## 6) Rollback Validation (Phase 4)

Before release:
- Run feature tests for phase3/phase4.
- Run migration on staging tenant clone first.

Rollback steps:
- rollback the latest tenant migration batch:
  - `php artisan tenant:migrate-all --rollback`
- verify critical tables exist and app endpoints are healthy.
- run `php artisan system:health-check` after rollback.
