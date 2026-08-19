<?php

namespace App\Support;

final class PageAudienceInput
{
    /**
     * @param  array<int|string|null>  $rawFromRequest
     * @return array<int>
     */
    public static function membershipTypeIdsForAccessLevel(string $accessLevel, array $rawFromRequest): array
    {
        if ($accessLevel !== 'membership') {
            return [];
        }

        return array_values(array_filter(array_map('intval', $rawFromRequest)));
    }
}
