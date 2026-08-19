<?php

namespace App\Services;

use App\Models\User;

class MembershipAccessService
{
    /**
     * @param  mixed  $value
     * @return array<int, int>
     */
    public static function normalizeMembershipTypeIds($value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($value)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map('intval', $value))));
    }

    public static function currentMembershipTypeId(?User $user): ?int
    {
        return self::currentMembershipTypeIds($user)[0] ?? null;
    }

    /**
     * @return array<int, int>
     */
    public static function currentMembershipTypeIds(?User $user): array
    {
        if (! $user) {
            return [];
        }

        $membershipTypeIds = [];

        if ($user->membership_type_id) {
            $membershipTypeIds[] = (int) $user->membership_type_id;
        }

        $activeMembership = $user->activeMembership();
        if ($activeMembership?->membership_type_id) {
            $membershipTypeIds[] = (int) $activeMembership->membership_type_id;
        }

        return array_values(array_unique($membershipTypeIds));
    }

    public static function hasTraineeRole(?User $user): bool
    {
        return (bool) $user?->hasAnyRole(['user', 'client']);
    }

    public static function matchesGender(?User $user, ?string $audienceGender): bool
    {
        if (! $audienceGender || $audienceGender === 'all') {
            return true;
        }

        if (! $user || ! $user->gender) {
            return false;
        }

        return $user->gender === $audienceGender;
    }

    /**
     * @param  array<int, int>  $requiredMembershipTypes
     */
    public static function matchesMembershipTypes(?User $user, array $requiredMembershipTypes): bool
    {
        $requiredMembershipTypes = self::normalizeMembershipTypeIds($requiredMembershipTypes);

        if ($requiredMembershipTypes === []) {
            return true;
        }

        if (! $user) {
            return false;
        }

        return count(array_intersect(
            self::currentMembershipTypeIds($user),
            $requiredMembershipTypes
        )) > 0;
    }
}
