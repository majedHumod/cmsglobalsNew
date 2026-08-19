<?php

namespace App\Services;

use App\Events\MembershipLifecycleChanged;
use App\Models\UserMembership;
use Carbon\Carbon;

class MembershipRenewalService
{
    public function activate(UserMembership $membership, ?string $paymentReference = null, bool $extendFromExpiry = false): UserMembership
    {
        $membership->loadMissing('subscriptionPlan');
        abort_unless($membership->subscriptionPlan, 404);

        UserMembership::query()
            ->where('user_id', $membership->user_id)
            ->where('id', '!=', $membership->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        $durationDays = (int) $membership->subscriptionPlan->duration_days;
        $startsAt = $extendFromExpiry && $membership->expires_at && $membership->expires_at->isFuture()
            ? $membership->expires_at->copy()
            : ($membership->expires_at && $membership->expires_at->isFuture()
                ? $membership->expires_at->copy()
                : now());

        if (! $membership->starts_at) {
            $startsAt = $extendFromExpiry ? ($membership->expires_at?->copy() ?? now()) : now();
        }

        $base = $extendFromExpiry
            ? ($membership->expires_at && $membership->expires_at->isFuture() ? $membership->expires_at->copy() : now())
            : now();

        $expiresAt = $base->copy()->addDays($durationDays);

        $membership->update([
            'membership_type_id' => $membership->subscriptionPlan->membership_type_id,
            'starts_at' => $membership->starts_at ?? ($extendFromExpiry ? $base : now()),
            'expires_at' => $expiresAt,
            'is_active' => true,
            'payment_status' => 'paid',
            'payment_reference' => $paymentReference ?: $membership->payment_reference ?: $membership->stripe_payment_intent_id,
        ]);

        $membership->user->forceFill([
            'membership_type_id' => $membership->subscriptionPlan->membership_type_id,
            'membership_expires_at' => $membership->expires_at,
        ])->save();

        $fresh = $membership->fresh(['user.clientProfile', 'subscriptionPlan']);

        app(TrainingProgramService::class)->activateForUser(
            $fresh->user,
            $fresh->starts_at ? Carbon::parse($fresh->starts_at) : now(),
            restartIfExists: false
        );

        event(new MembershipLifecycleChanged($fresh));

        return $fresh->fresh();
    }

    public function renew(UserMembership $membership): UserMembership
    {
        return $this->activate($membership, null, true);
    }
}
