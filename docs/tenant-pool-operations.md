# Tenant Pool Operations

## Purpose

This project runs tenant provisioning on shared hosting by assigning each new subscriber to a pre-created tenant database from a managed pool.

## One-time deployment steps

1. Upload the latest project files.
2. Run system migrations:

```bash
php artisan migrate --path=database/migrations/system --force
```

3. Clear cached bootstrap and runtime caches:

```bash
php artisan optimize:clear
```

## Register prepared tenant databases

Use this command for each tenant database you create in cPanel:

```bash
php artisan tenants:pool-register DB_NAME --label="Pool XX" --ready=1
```

Example:

```bash
php artisan tenants:pool-register etoscoach5 --label="Pool 05" --ready=1
```

Notes:
- Use `--ready=1` when the database is already migrated and seeded.
- Use `--ready=0` when the database exists but still needs tenant migrations and seed data the first time it is assigned.

## View pool status

```bash
php artisan tenants:pool-list
```

Status meanings:
- `available`: ready to be assigned to a new subscriber.
- `allocated`: already assigned to a tenant.
- `maintenance`: excluded from automatic assignment.

## Provisioning flow

When a new customer subscribes:

1. The checkout endpoint creates a queued provisioning job.
2. The provisioning service picks the first `available` database from the pool.
3. A new record is created in `system.tenants`.
4. The selected pool database is marked as `allocated`.
5. Default tenant content is created for new tenants if the database is not already prepared.

## Queue worker

Run the queue worker via cron or manually:

```bash
php artisan queue:work --stop-when-empty --tries=3
```

Recommended cron pattern on shared hosting: every minute.

## Default starter content for new tenants

Fresh tenants now receive starter content automatically, including:
- active landing page
- starter meal plans
- starter testimonials
- starter training sessions
- starter nutrition discounts
- homepage display settings

## Useful maintenance commands

Clear caches:

```bash
php artisan optimize:clear
```

Check failed jobs:

```bash
php artisan queue:failed
```

Retry failed jobs:

```bash
php artisan queue:retry all
```
