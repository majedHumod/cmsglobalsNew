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
     * @param  array<string, mixed>  $values
     * @return array<int, string>
     */
    public static function missingRequired(string $key, array $values): array
    {
        if (! ($values['enabled'] ?? false)) {
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
