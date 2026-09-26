<?php

namespace App\Support;

use InvalidArgumentException;

class TenantPaymentCatalog
{
    public const INSTANT = 'instant';

    public const MANUAL = 'manual';

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function channels(): array
    {
        $channels = config('tenant_payments.channels', []);

        return is_array($channels) ? $channels : [];
    }

    public static function has(string $key): bool
    {
        return isset(self::channels()[$key]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function get(string $key): array
    {
        $channel = self::channels()[$key] ?? null;

        if (! is_array($channel)) {
            throw new InvalidArgumentException('Unknown payment channel.');
        }

        return $channel;
    }

    public static function settlement(string $key): string
    {
        return (self::get($key)['settlement'] ?? self::INSTANT) === self::MANUAL
            ? self::MANUAL
            : self::INSTANT;
    }

    public static function isManual(string $key): bool
    {
        return self::settlement($key) === self::MANUAL;
    }

    public static function label(?string $key): string
    {
        if ($key === null || $key === '') {
            return '—';
        }

        $label = self::channels()[$key]['label'] ?? null;

        return is_string($label) && $label !== '' ? $label : $key;
    }

    /**
     * الشعار الرسمي المُجهّز مسبقاً مع التطبيق لهذه القناة (لا يتم رفعه من المدرب/النادي).
     */
    public static function logoPath(string $key): ?string
    {
        $logo = self::channels()[$key]['logo'] ?? null;

        return is_string($logo) && $logo !== '' ? $logo : null;
    }

    public static function logoUrl(string $key): ?string
    {
        $path = self::logoPath($key);

        return $path !== null ? asset(ltrim($path, '/')) : null;
    }

    /**
     * هل هذه القناة تخزن قائمة عناصر متكررة (كحسابات بنكية متعددة) بدل حقول ثابتة؟
     */
    public static function isRepeatable(string $key): bool
    {
        return is_string(self::get($key)['repeatable'] ?? null) && (self::get($key)['repeatable'] ?? '') !== '';
    }

    public static function repeaterKey(string $key): ?string
    {
        $repeaterKey = self::get($key)['repeatable'] ?? null;

        return is_string($repeaterKey) && $repeaterKey !== '' ? $repeaterKey : null;
    }

    public static function halalas(float|int|string|null $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    /**
     * @param  array<string, mixed>  $incoming
     * @param  array<string, mixed>  $stored
     * @return array<string, mixed>
     */
    public static function mergeChannel(string $key, array $incoming, array $stored): array
    {
        $merged = [
            'enabled' => (bool) ($incoming['enabled'] ?? false),
        ];

        if (self::isRepeatable($key)) {
            $repeaterKey = self::repeaterKey($key);
            $items = is_array($incoming[$repeaterKey] ?? null) ? $incoming[$repeaterKey] : [];
            $merged[$repeaterKey] = self::mergeRepeaterItems($key, $items);

            return $merged;
        }

        foreach (self::get($key)['fields'] ?? [] as $name => $field) {
            if (! is_array($field)) {
                continue;
            }

            $value = trim((string) ($incoming[$name] ?? ''));
            $isSecret = (bool) ($field['secret'] ?? false);

            if ($isSecret && $value === '') {
                $value = trim((string) ($stored[$name] ?? ''));
            }

            $merged[$name] = $value;
        }

        return $merged;
    }

    /**
     * @param  array<int|string, mixed>  $items
     * @return array<int, array<string, string>>
     */
    private static function mergeRepeaterItems(string $key, array $items): array
    {
        $schema = self::get($key)['fields'] ?? [];
        $clean = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $row = [];
            $hasAnyValue = false;

            foreach ($schema as $name => $field) {
                if (! is_array($field)) {
                    continue;
                }

                $value = trim((string) ($item[$name] ?? ''));
                $row[$name] = $value;

                if ($value !== '') {
                    $hasAnyValue = true;
                }
            }

            // تجاهل الصفوف الفارغة تماماً (مثل صف تمت إضافته ثم إلغاؤه).
            if ($hasAnyValue) {
                $clean[] = $row;
            }
        }

        return $clean;
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<int, string>
     */
    public static function missingRequired(string $key, array $values): array
    {
        if (! ($values['enabled'] ?? false)) {
            return [];
        }

        if (self::isRepeatable($key)) {
            $repeaterKey = self::repeaterKey($key);
            $items = is_array($values[$repeaterKey] ?? null) ? $values[$repeaterKey] : [];

            if ($items === []) {
                return [$repeaterKey];
            }

            $schema = self::get($key)['fields'] ?? [];

            foreach ($items as $item) {
                if (! is_array($item)) {
                    return [$repeaterKey];
                }

                foreach ($schema as $name => $field) {
                    if (! is_array($field) || ! ($field['required'] ?? false)) {
                        continue;
                    }

                    if (trim((string) ($item[$name] ?? '')) === '') {
                        return [$repeaterKey];
                    }
                }
            }

            return [];
        }

        $missing = [];

        foreach (self::get($key)['fields'] ?? [] as $name => $field) {
            if (! is_array($field) || ! ($field['required'] ?? false)) {
                continue;
            }

            if (trim((string) ($values[$name] ?? '')) === '') {
                $missing[] = (string) $name;
            }
        }

        return $missing;
    }

    /**
     * @param  array<string, mixed>  $stored
     * @return array<string, mixed>
     */
    public static function formChannel(string $key, array $stored): array
    {
        $state = [
            'enabled' => (bool) ($stored['enabled'] ?? false),
        ];

        if (self::isRepeatable($key)) {
            $repeaterKey = self::repeaterKey($key);
            $items = is_array($stored[$repeaterKey] ?? null) ? $stored[$repeaterKey] : [];
            $state[$repeaterKey] = array_values($items);

            return $state;
        }

        foreach (self::get($key)['fields'] ?? [] as $name => $field) {
            if (! is_array($field)) {
                continue;
            }

            if ($field['secret'] ?? false) {
                $state[$name] = '';

                continue;
            }

            $value = trim((string) ($stored[$name] ?? ''));
            $state[$name] = $value !== '' ? $value : (string) ($field['default'] ?? '');
        }

        return $state;
    }

    /**
     * @param  array<string, mixed>  $storedChannel
     */
    public static function hasStoredSecret(array $storedChannel, string $field): bool
    {
        return trim((string) ($storedChannel[$field] ?? '')) !== '';
    }
}
