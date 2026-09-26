<?php

namespace App\Services\Payments;

use App\Models\SiteSetting;
use App\Support\TenantPaymentCatalog;
use Illuminate\Validation\ValidationException;

class TenantPaymentSettings
{
    public const GROUP = 'payments';

    public const KEY = 'tenant_payment_channels';

    /**
     * @return array<string, array<string, mixed>>
     */
    public function stored(): array
    {
        $stored = SiteSetting::get(self::KEY, []);

        return is_array($stored) ? $stored : [];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function formState(): array
    {
        $stored = $this->stored();
        $state = [];

        foreach (array_keys(TenantPaymentCatalog::channels()) as $key) {
            $channel = $stored[$key] ?? [];
            $state[$key] = TenantPaymentCatalog::formChannel($key, is_array($channel) ? $channel : []);
        }

        return $state;
    }

    /**
     * @param  array<string, mixed>  $incoming
     */
    public function save(array $incoming): void
    {
        $stored = $this->stored();
        $channels = [];
        $errors = [];

        foreach (array_keys(TenantPaymentCatalog::channels()) as $key) {
            $previous = $stored[$key] ?? [];
            $merged = TenantPaymentCatalog::mergeChannel(
                $key,
                is_array($incoming[$key] ?? null) ? $incoming[$key] : [],
                is_array($previous) ? $previous : []
            );

            foreach (TenantPaymentCatalog::missingRequired($key, $merged) as $field) {
                $label = TenantPaymentCatalog::get($key)['fields'][$field]['label'] ?? $field;
                $errors['payments.'.$key.'.'.$field] = 'حقل '.$label.' مطلوب عند تفعيل القناة.';
            }

            $channels[$key] = $merged;
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        SiteSetting::set(
            self::KEY,
            $channels,
            self::GROUP,
            'json',
            'Coach and club payment channels. Separate from platform billing.',
            false,
            true
        );
        SiteSetting::clearGroupCache(self::GROUP);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function checkoutMethods(): array
    {
        $stored = $this->stored();
        $methods = [];

        foreach (TenantPaymentCatalog::channels() as $key => $definition) {
            $channel = $stored[$key] ?? [];
            if (! is_array($channel) || ! ($channel['enabled'] ?? false)) {
                continue;
            }

            if (TenantPaymentCatalog::missingRequired($key, $channel) !== []) {
                continue;
            }

            $method = [
                'key' => $key,
                'label' => $definition['label'] ?? $key,
                'description' => $definition['description'] ?? '',
                'settlement' => TenantPaymentCatalog::settlement($key),
                'region' => $definition['region'] ?? 'saudi',
            ];

            if ($key === 'bank_transfer') {
                $method['bank'] = [
                    'bank_name' => (string) ($channel['bank_name'] ?? ''),
                    'account_name' => (string) ($channel['account_name'] ?? ''),
                    'iban' => (string) ($channel['iban'] ?? ''),
                    'account_number' => (string) ($channel['account_number'] ?? ''),
                    'instructions' => (string) ($channel['instructions'] ?? ''),
                ];
            }

            $methods[] = $method;
        }

        return $methods;
    }

    /**
     * @return array<string, string>
     */
    public function credentials(string $key): array
    {
        $stored = $this->stored()[$key] ?? [];
        $credentials = [];

        foreach (TenantPaymentCatalog::get($key)['fields'] ?? [] as $name => $field) {
            $credentials[(string) $name] = trim((string) (is_array($stored) ? ($stored[$name] ?? '') : ''));
        }

        return $credentials;
    }

    public function hasStoredSecret(string $channel, string $field): bool
    {
        $stored = $this->stored()[$channel] ?? [];

        return is_array($stored) && TenantPaymentCatalog::hasStoredSecret($stored, $field);
    }
}
