# Tenant Pool Operations

## Purpose

This project runs tenant provisioning on shared hosting by assigning each new subscriber to a pre-created tenant database from a managed pool.

## One-time deployment steps

1. Upload the latest project files.
2. Run system migrations:

```bash
php artisan system:migrate
```

3. Clear cached bootstrap and runtime caches:

```bash
php artisan optimize:clear
```

## Prepare a clean pool database (recommended)

Create the empty MySQL database in cPanel first, then:

```bash
php artisan tenants:pool-prepare DB_NAME --label="Pool XX"
```

This will:
- run all `database/migrations/tenants` on that DB
- seed **PoolBaselineSeeder** only (site settings, membership catalog, FAQs, roles/permissions)
- register the pool row as `available` + `is_ready=1`

It does **not** create coaches, clients, or `admin@tenant.com`.

Prepare every already-registered available pool DB:

```bash
php artisan tenants:pool-prepare --all-available
```

Schema only (no seed):

```bash
php artisan tenants:pool-prepare DB_NAME --no-seed
```

## Register without preparing (legacy)

```bash
php artisan tenants:pool-register DB_NAME --label="Pool XX" --ready=0
```

- `--ready=1` only when the DB was already migrated/seeded (prefer `pool-prepare` instead).
- `--ready=0` defers baseline seed until first subscription.

## View pool status

```bash
php artisan tenants:pool-list
```

Status meanings:
- `available`: ready to be assigned to a new subscriber.
- `allocated`: already assigned to a tenant.
- `maintenance`: excluded from automatic assignment.

## Fix mismatched schemas (existing DBs)

Audit:

```bash
php artisan tenants:audit --sync-system --only-issues
```

Apply pending migrations only (no wipe):

```bash
php artisan tenants:sync-schemas
# or scoped:
php artisan tenants:sync-schemas --allocated
php artisan tenants:sync-schemas --pool-available
```

Also available:

```bash
php artisan tenant:migrate-all
```

## Provisioning flow (etoscoach subscription)

When a customer pays for a platform plan:

1. Checkout creates a pending invoice and Paylink session.
2. Payment activation dispatches `ProvisionTenantJob`.
3. `TenantProvisioner`:
   - allocates the first available pool DB
   - creates `system.tenants` with subdomain `{slug}.{APP_DOMAIN}`
   - always runs tenant migrations
   - seeds `PoolBaselineSeeder` if the DB was not ready / missing roles
   - creates the subscriber user (email from checkout) with `admin` + `coach` roles
   - seeds `DefaultTenantContentSeeder` (landing + light starter content)
   - creates system subscription + billing contact
   - queues welcome email with password-reset link

Requirements on shared hosting:
- pre-created MySQL databases in the pool
- wildcard DNS (`*.etoscoach.com`) pointing at the app
- queue worker / cron for `queue:work`

cPanel does **not** auto-create MySQL databases or DNS records in this flow.

## Safe migration rules

Never run `php artisan migrate` without an explicit scope.

Use only:

```bash
php artisan system:migrate
php artisan tenants:audit --sync-system
php artisan tenants:sync-schemas
php artisan tenant:migrate DOMAIN
php artisan tenant:migrate-all
php artisan tenants:pool-prepare DB_NAME
```

Migration file placement:

- `database/migrations/system` → platform DB
- `database/migrations/tenants` → each coach tenant DB
- no application migrations in root `database/migrations`

## Queue worker

```bash
php artisan queue:work --stop-when-empty --tries=3
```

Recommended cron: every minute.

## Default content layers

| Layer | When | Contains |
|---|---|---|
| `PoolBaselineSeeder` | pool-prepare / first provision if needed | settings, memberships, plans, FAQs, permissions |
| Subscriber user | on paid provision | real checkout email only |
| `DefaultTenantContentSeeder` | after subscriber exists | landing, sample meals/testimonials/sessions |

Demo seeders (`ArabicFitnessSeeder`, `DemoTenantSeeder`) are **not** used for pool or production subscribe.

## Useful maintenance commands

```bash
php artisan optimize:clear
php artisan queue:failed
php artisan queue:retry all
php artisan tenants:pool-list
php artisan tenants:audit --only-issues
```
