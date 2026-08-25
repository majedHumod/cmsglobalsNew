<?php

namespace App\Services\Platform;

use App\Models\Billing\Subscription;
use App\Models\Tenant;
use Carbon\CarbonInterface;

class TenantAccessService
{
    public const ACTIVE = 'active';

    public const GRACE = 'grace';

    public const SUSPENDED = 'suspended';

    public const ARCHIVED = 'archived';

    public function evaluate(Tenant $tenant): string
    {
        $subscription = Subscription::query()
            ->where('tenant_id', $tenant->id)
            ->latest('id')
            ->first();

        if (! $subscription || ! $subscription->current_period_end) {
            return self::ACTIVE;
        }

        $end = $subscription->current_period_end;
        $now = now();
        if ($end->isFuture() || $end->isToday()) {
            return self::ACTIVE;
        }

        $graceEnds = $end->copy()->addDays((int) config('platform.grace_days', 14));
        if ($now->lte($graceEnds)) {
            return self::GRACE;
        }

        $archiveAt = $end->copy()->addDays((int) config('platform.archive_days', 90));
        if ($now->gte($archiveAt)) {
            return self::ARCHIVED;
        }

        return self::SUSPENDED;
    }

    public function sync(Tenant $tenant): Tenant
    {
        $status = $this->evaluate($tenant);
        $subscription = Subscription::query()
            ->where('tenant_id', $tenant->id)
            ->latest('id')
            ->first();

        $end = $subscription?->current_period_end;
        $tenant->access_status = $status;
        $tenant->subscription_ends_at = $end;
        $tenant->grace_ends_at = $end?->copy()->addDays((int) config('platform.grace_days', 14));
        $tenant->suspended_at = $status === self::SUSPENDED || $status === self::ARCHIVED ? ($tenant->suspended_at ?: now()) : null;
        $tenant->archived_at = $status === self::ARCHIVED ? ($tenant->archived_at ?: now()) : null;
        $tenant->save();

        return $tenant;
    }

    public function canUseWorkspace(Tenant $tenant): bool
    {
        $status = $tenant->access_status ?: $this->evaluate($tenant);

        return in_array($status, [self::ACTIVE, self::GRACE], true);
    }

    public function dashboardUrl(Tenant $tenant): string
    {
        $host = $tenant->domain ?: ($tenant->subdomain.'.'.config('app.domain'));
        $scheme = app()->environment('local') ? 'http' : 'https';
        $port = app()->environment('local') ? ':8000' : '';

        return $scheme.'://'.$host.$port.'/dashboard';
    }

    public function loginUrl(Tenant $tenant): string
    {
        $host = $tenant->domain ?: ($tenant->subdomain.'.'.config('app.domain'));
        $scheme = app()->environment('local') ? 'http' : 'https';
        $port = app()->environment('local') ? ':8000' : '';

        return $scheme.'://'.$host.$port.'/login';
    }

    public function message(Tenant $tenant): string
    {
        return match ($tenant->access_status ?: $this->evaluate($tenant)) {
            self::GRACE => 'انتهت فترة الاشتراك. يمكنك متابعة العمل خلال فترة السماح، ويُرجى التجديد قريباً.',
            self::SUSPENDED => 'تم إيقاف مساحة العمل مؤقتاً لعدم التجديد. المحتوى محفوظ ولن يُحذف. جدّد الاشتراك لاستعادة الدخول الكامل.',
            self::ARCHIVED => 'الحساب مؤرشف بسبب توقف التجديد لفترة طويلة. المحتوى ما زال محفوظاً. جدّد الاشتراك أو تواصل مع الدعم لاستعادة الوصول.',
            default => '',
        };
    }

    public function periodEnd(Tenant $tenant): ?CarbonInterface
    {
        return $tenant->subscription_ends_at
            ?: Subscription::query()->where('tenant_id', $tenant->id)->latest('id')->value('current_period_end');
    }
}
