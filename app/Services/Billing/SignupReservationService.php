<?php

namespace App\Services\Billing;

use App\Models\Billing\BillingContact;
use App\Models\Billing\SignupReservation;
use App\Models\Tenant;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * Holds the subdomain + email of an in-flight checkout so two buyers cannot pay for the same
 * name, and so an email that already owns a club cannot silently buy a second one.
 */
class SignupReservationService
{
    public static function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    public function slugInUse(string $slug): bool
    {
        $domain = $slug.'.'.config('app.domain', 'yourdomain.com');

        return Tenant::on('system')
            ->where(function ($query) use ($slug, $domain) {
                $query->where('subdomain', $slug)
                    ->orWhere('slug', $slug)
                    ->orWhere('domain', $domain);
            })
            ->exists();
    }

    public function emailInUse(string $email, ?int $ignoreTenantId = null): bool
    {
        $email = self::normalizeEmail($email);

        $tenant = Tenant::on('system')
            ->whereRaw('LOWER(email) = ?', [$email])
            ->when($ignoreTenantId, fn ($q) => $q->where('id', '!=', $ignoreTenantId))
            ->exists();
        if ($tenant) {
            return true;
        }

        return BillingContact::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->when($ignoreTenantId, fn ($q) => $q->where('tenant_id', '!=', $ignoreTenantId))
            ->exists();
    }

    public function mobileInUse(?string $e164): bool
    {
        if (! $e164) {
            return false;
        }

        return Tenant::on('system')->where('phone', $e164)->exists();
    }

    public function releaseExpired(): void
    {
        SignupReservation::query()
            ->where('status', SignupReservation::PENDING)
            ->where('expires_at', '<', now())
            ->update([
                'status' => SignupReservation::RELEASED,
                'active_slug' => null,
                'active_email' => null,
            ]);
    }

    /**
     * Reserve slug + email for a new checkout. Throws SignupConflictException when unavailable.
     */
    public function reserve(string $orderNumber, string $slug, string $email, ?string $mobileE164, ?string $planCode): SignupReservation
    {
        $email = self::normalizeEmail($email);
        $slug = strtolower($slug);

        return DB::connection('system')->transaction(function () use ($orderNumber, $slug, $email, $mobileE164, $planCode) {
            $this->releaseExpired();

            if ($this->slugInUse($slug)) {
                throw new SignupConflictException('slug_taken', 'هذا السب-دومين مستخدم بالفعل. جرّب اسماً آخر.');
            }
            if ($this->emailInUse($email)) {
                throw new SignupConflictException('email_in_use', 'هذا البريد الإلكتروني مرتبط بنادٍ قائم. سجّل الدخول لتجديد اشتراكك بدلاً من إنشاء نادٍ جديد.');
            }
            if (config('platform.signup.unique_mobile') && $this->mobileInUse($mobileE164)) {
                throw new SignupConflictException('mobile_in_use', 'رقم الجوال هذا مرتبط بنادٍ قائم. سجّل الدخول أو استخدم رقماً آخر.');
            }

            // The same buyer restarting an abandoned checkout supersedes their own unpaid attempt.
            SignupReservation::query()
                ->where('active_email', $email)
                ->where('status', SignupReservation::PENDING)
                ->update(['status' => SignupReservation::RELEASED, 'active_slug' => null, 'active_email' => null]);

            $live = SignupReservation::query()
                ->whereIn('status', [SignupReservation::PENDING, SignupReservation::PAID])
                ->where(function ($q) use ($slug, $email, $mobileE164) {
                    $q->where('active_slug', $slug)->orWhere('active_email', $email);
                    if (config('platform.signup.unique_mobile') && $mobileE164) {
                        $q->orWhere('mobile_e164', $mobileE164);
                    }
                })
                ->get();

            foreach ($live as $other) {
                if ($other->active_slug === $slug) {
                    throw new SignupConflictException('slug_pending', 'هذا السب-دومين محجوز مؤقتاً لعملية دفع قيد التنفيذ. جرّب اسماً آخر.');
                }
                if ($other->active_email === $email) {
                    throw new SignupConflictException('email_pending', 'لديك عملية اشتراك مدفوعة قيد التجهيز بهذا البريد. انتظر وصول رسالة التفعيل أو تواصل مع الدعم.');
                }
                throw new SignupConflictException('mobile_in_use', 'رقم الجوال هذا مرتبط بعملية اشتراك قيد التنفيذ.');
            }

            try {
                return SignupReservation::create([
                    'order_number' => $orderNumber,
                    'slug' => $slug,
                    'email' => $email,
                    'mobile_e164' => $mobileE164,
                    'plan_code' => $planCode,
                    'status' => SignupReservation::PENDING,
                    'active_slug' => $slug,
                    'active_email' => $email,
                    'expires_at' => now()->addMinutes(max(5, (int) config('platform.signup.reservation_minutes', 120))),
                ]);
            } catch (UniqueConstraintViolationException) {
                // Lost a race against a concurrent checkout.
                throw new SignupConflictException('slug_pending', 'هذا السب-دومين أو البريد محجوز لعملية دفع أخرى للتو. جرّب اسماً آخر.');
            }
        });
    }

    public function findByOrder(?string $orderNumber): ?SignupReservation
    {
        if (! $orderNumber) {
            return null;
        }

        // The payment path must keep working even if the reservations table has not been migrated yet.
        try {
            return SignupReservation::query()->where('order_number', $orderNumber)->first();
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    public function release(?string $orderNumber): void
    {
        if (! $orderNumber) {
            return;
        }

        try {
            SignupReservation::query()
                ->where('order_number', $orderNumber)
                ->whereIn('status', [SignupReservation::PENDING])
                ->update(['status' => SignupReservation::RELEASED, 'active_slug' => null, 'active_email' => null]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function markPaid(SignupReservation $reservation): void
    {
        if ($reservation->status === SignupReservation::PENDING) {
            $reservation->status = SignupReservation::PAID;
            $reservation->save();
        }
    }

    public function markProvisioned(?string $orderNumber, int $tenantId): void
    {
        if (! $orderNumber) {
            return;
        }

        SignupReservation::query()
            ->where('order_number', $orderNumber)
            ->update([
                'status' => SignupReservation::PROVISIONED,
                'tenant_id' => $tenantId,
                'active_slug' => null,
                'active_email' => null,
            ]);
    }

    public function markConflict(SignupReservation $reservation): void
    {
        $reservation->status = SignupReservation::CONFLICT;
        $reservation->active_slug = null;
        $reservation->active_email = null;
        $reservation->save();
    }

    /**
     * A customer paid after the reservation had been released (expired / superseded).
     * Re-claim the slug + email if nobody took them meanwhile; otherwise report failure.
     */
    public function reclaim(SignupReservation $reservation): bool
    {
        try {
            return DB::connection('system')->transaction(function () use ($reservation) {
                $this->releaseExpired();

                if ($this->slugInUse($reservation->slug) || $this->emailInUse($reservation->email)) {
                    return false;
                }

                $taken = SignupReservation::query()
                    ->where('id', '!=', $reservation->id)
                    ->whereIn('status', [SignupReservation::PENDING, SignupReservation::PAID])
                    ->where(function ($q) use ($reservation) {
                        $q->where('active_slug', $reservation->slug)->orWhere('active_email', $reservation->email);
                    })
                    ->exists();
                if ($taken) {
                    return false;
                }

                $reservation->status = SignupReservation::PAID;
                $reservation->active_slug = $reservation->slug;
                $reservation->active_email = $reservation->email;
                $reservation->save();

                return true;
            });
        } catch (UniqueConstraintViolationException) {
            return false;
        }
    }
}
