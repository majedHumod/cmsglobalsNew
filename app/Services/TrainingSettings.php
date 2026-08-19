<?php

namespace App\Services;

use App\Models\SiteSetting;

class TrainingSettings
{
    public const WEEK_START_SATURDAY = 'saturday';

    public const WEEK_START_SUNDAY = 'sunday';

    public const ADVANCE_AUTO = 'auto';

    public const ADVANCE_MANUAL = 'manual';

    public static function weekStartDay(): string
    {
        $value = strtolower((string) SiteSetting::get('training_week_start_day', self::WEEK_START_SUNDAY));

        return in_array($value, [self::WEEK_START_SATURDAY, self::WEEK_START_SUNDAY], true)
            ? $value
            : self::WEEK_START_SUNDAY;
    }

    public static function weekAdvanceMode(): string
    {
        $value = strtolower((string) SiteSetting::get('training_week_advance_mode', self::ADVANCE_AUTO));

        return in_array($value, [self::ADVANCE_AUTO, self::ADVANCE_MANUAL], true)
            ? $value
            : self::ADVANCE_AUTO;
    }

    public static function autoActivatePlanOnSubscription(): bool
    {
        return (bool) SiteSetting::get('training_auto_activate_plan_on_subscription', true);
    }

    /**
     * @return array<string, string>
     */
    public static function weekStartDayOptions(): array
    {
        return [
            self::WEEK_START_SUNDAY => 'الأحد',
            self::WEEK_START_SATURDAY => 'السبت',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function weekAdvanceModeOptions(): array
    {
        return [
            self::ADVANCE_AUTO => 'تلقائي (ينتقل للأسبوع التالي عند انتهاء الأسبوع)',
            self::ADVANCE_MANUAL => 'يدوي (المدرب يحدد أسبوع البرنامج)',
        ];
    }
}
