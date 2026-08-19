<?php

namespace App\Services;

use App\Models\NotificationPreference;
use App\Models\User;
use App\Services\Communication\CommunicationCatalog;

class NotificationPreferenceService
{
    /**
     * @return array<string, array{in_app: bool, push: bool, label_ar: string}>
     */
    public function defaults(): array
    {
        return [
            'message' => ['in_app' => true, 'push' => true, 'label_ar' => 'الرسائل'],
            'booking' => ['in_app' => true, 'push' => true, 'label_ar' => 'الحجوزات'],
            'membership' => ['in_app' => true, 'push' => true, 'label_ar' => 'العضوية'],
            'habit' => ['in_app' => true, 'push' => true, 'label_ar' => 'العادات'],
            'checkin' => ['in_app' => true, 'push' => true, 'label_ar' => 'المتابعة'],
            'community' => ['in_app' => true, 'push' => true, 'label_ar' => 'المجتمع'],
            'system' => ['in_app' => true, 'push' => true, 'label_ar' => 'النظام'],
        ];
    }

    /**
     * @return array<string, array{in_app: bool, push: bool, label_ar: string}>
     */
    public function forUser(User $user): array
    {
        $defaults = $this->defaults();
        $stored = NotificationPreference::query()->where('user_id', $user->id)->value('preferences') ?? [];

        foreach ($defaults as $category => $meta) {
            if (! isset($stored[$category]) || ! is_array($stored[$category])) {
                continue;
            }
            $defaults[$category]['in_app'] = (bool) ($stored[$category]['in_app'] ?? $meta['in_app']);
            $defaults[$category]['push'] = (bool) ($stored[$category]['push'] ?? $meta['push']);
        }

        return $defaults;
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, array{in_app: bool, push: bool, label_ar: string}>
     */
    public function updateForUser(User $user, array $input): array
    {
        $merged = $this->forUser($user);

        foreach ($merged as $category => $meta) {
            if (! isset($input[$category]) || ! is_array($input[$category])) {
                continue;
            }
            if (array_key_exists('in_app', $input[$category])) {
                $merged[$category]['in_app'] = (bool) $input[$category]['in_app'];
            }
            if (array_key_exists('push', $input[$category])) {
                $merged[$category]['push'] = (bool) $input[$category]['push'];
            }
        }

        $toStore = [];
        foreach ($merged as $category => $meta) {
            $toStore[$category] = [
                'in_app' => (bool) $meta['in_app'],
                'push' => (bool) $meta['push'],
            ];
        }

        NotificationPreference::query()->updateOrCreate(
            ['user_id' => $user->id],
            ['preferences' => $toStore]
        );

        return $merged;
    }

    public function allowsInApp(User|int $user, string $type): bool
    {
        $userModel = $user instanceof User ? $user : User::query()->find($user);
        if (! $userModel) {
            return true;
        }

        $category = app(CommunicationCatalog::class)->metaForType($type)['category'] ?? 'system';
        $prefs = $this->forUser($userModel);

        return (bool) ($prefs[$category]['in_app'] ?? true);
    }

    public function allowsPush(User|int $user, string $type): bool
    {
        $userModel = $user instanceof User ? $user : User::query()->find($user);
        if (! $userModel) {
            return true;
        }

        $category = app(CommunicationCatalog::class)->metaForType($type)['category'] ?? 'system';
        $prefs = $this->forUser($userModel);

        return (bool) ($prefs[$category]['push'] ?? true);
    }
}
