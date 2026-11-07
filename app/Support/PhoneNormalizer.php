<?php

namespace App\Support;

/**
 * Normalises mobile numbers to E.164 (+<country><national>) so that
 * "0501234567", "+966 50 123 4567" and "00966501234567" compare as equal.
 */
class PhoneNormalizer
{
    public static function toE164(?string $raw, ?string $defaultCountryCode = null): ?string
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return null;
        }

        $defaultCountryCode = preg_replace('/\D+/', '', (string) ($defaultCountryCode ?? config('platform.signup.default_country_code', '966'))) ?: '966';

        // Arabic-Indic digits → ASCII
        $raw = strtr($raw, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        ]);

        $hasPlus = str_starts_with($raw, '+');
        $digits = preg_replace('/\D+/', '', $raw) ?? '';
        if ($digits === '') {
            return null;
        }

        if ($hasPlus) {
            // already international
        } elseif (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '0')) {
            $digits = $defaultCountryCode.ltrim($digits, '0');
        } elseif (! str_starts_with($digits, $defaultCountryCode) || strlen($digits) <= strlen($defaultCountryCode) + 8) {
            // national number without leading zero (e.g. 501234567)
            if (strlen($digits) <= 10) {
                $digits = $defaultCountryCode.$digits;
            }
        }

        // Saudi style "9660501234567" (extra zero after the country code)
        if ($defaultCountryCode === '966' && str_starts_with($digits, '9660')) {
            $digits = '966'.ltrim(substr($digits, 3), '0');
        }

        if (strlen($digits) < 8 || strlen($digits) > 15) {
            return null;
        }

        if (str_starts_with($digits, '966') && strlen($digits) !== 12) {
            return null;
        }

        return '+'.$digits;
    }
}
