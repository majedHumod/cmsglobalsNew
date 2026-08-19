# Database Boundaries

## Purpose

This project uses two database scopes that must stay isolated:

- `cmsglobals_restored` on the `system` connection is the platform database for coaches who buy or trial the product.
- Each `tenant` database belongs to one coach and stores that coach's trainees, content, settings, and internal operations.

## System Database (`cmsglobals_restored`)

These tables belong to the platform layer and must only be migrated on the `system` connection:

- `tenants`
- `tenant_database_pools`
- `plans`
- `subscriptions`
- `invoices`
- `payments`
- `billing_contacts`
- `events`
- `jobs`
- `failed_jobs`

Responsibilities:

- coach signup and trial lifecycle
- central billing and payment callbacks
- tenant registry and tenant domain lookup
- prepared tenant database pool allocation
- shared queue infrastructure for platform-side jobs

Key code paths:

- `config/database.php`
- `app/Models/Tenant.php`
- `app/Models/TenantDatabasePool.php`
- `app/Models/Billing/*`
- `app/Services/Tenant/TenantProvisioner.php`
- `app/Http/Middleware/TenantsMiddleware.php`

## Tenant Databases

These tables belong inside each coach database and must only be migrated on the `tenant` connection:

- `users`
- `cache`
- `jobs`
- `personal_access_tokens`
- `membership_types`
- `subscription_plans`
- `user_memberships`
- `pages`
- `notes`
- `articles`
- `meal_plans`
- `workouts`
- `workout_schedules`
- `training_sessions`
- `session_bookings`
- `nutrition_discounts`
- `faqs`
- `testimonials`
- `landing_pages`
- `site_settings`
- Spatie permission tables
- advanced permission tables

Responsibilities:

- coach admin panel and public coach site
- trainee accounts and access control
- trainee subscriptions inside the coach business
- coach-owned content and scheduling
- coach-specific permissions, branding, and settings

## Migration Rules

Never run plain `php artisan migrate` on this project.

Use only explicit scoped commands:

```bash
php artisan system:migrate
php artisan tenant:migrate coach.example.com
php artisan tenant:migrate-all
```

If a migration creates or alters a platform table, it belongs in `database/migrations/system`.
If a migration creates or alters coach-owned tables, it belongs in `database/migrations/tenants`.

The root `database/migrations` directory must not contain application table migrations.
