<?php

namespace Tests\Unit;

use App\Support\TenantPaymentCatalog;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TenantPaymentCatalogTest extends TestCase
{
    public static function instantChannels(): array
    {
        return [
            ['moyasar'],
            ['tap'],
            ['paytabs'],
            ['hyperpay'],
            ['myfatoorah'],
            ['stripe'],
        ];
    }

    #[DataProvider('instantChannels')]
    public function test_direct_channels_settle_immediately(string $channel): void
    {
        $this->assertFalse(TenantPaymentCatalog::isManual($channel));
        $this->assertSame(TenantPaymentCatalog::INSTANT, TenantPaymentCatalog::settlement($channel));
    }

    public function test_bank_transfer_stays_manual_until_confirmed(): void
    {
        $this->assertTrue(TenantPaymentCatalog::isManual('bank_transfer'));
        $this->assertSame(TenantPaymentCatalog::MANUAL, TenantPaymentCatalog::settlement('bank_transfer'));
    }

    public function test_blank_secret_keeps_the_stored_value(): void
    {
        $merged = TenantPaymentCatalog::mergeChannel('stripe', [
            'enabled' => true,
            'secret_key' => '',
            'publishable_key' => 'pk_live_new',
        ], [
            'enabled' => true,
            'secret_key' => 'sk_live_saved',
            'publishable_key' => 'pk_live_old',
        ]);

        $this->assertSame('sk_live_saved', $merged['secret_key']);
        $this->assertSame('pk_live_new', $merged['publishable_key']);
        $this->assertTrue($merged['enabled']);
    }

    public function test_disabled_channel_does_not_require_credentials(): void
    {
        $this->assertSame([], TenantPaymentCatalog::missingRequired('moyasar', [
            'enabled' => false,
            'secret_key' => '',
        ]));
    }

    public function test_enabled_channel_requires_its_secret(): void
    {
        $this->assertSame(['secret_key'], TenantPaymentCatalog::missingRequired('stripe', [
            'enabled' => true,
            'secret_key' => '  ',
        ]));
    }

    public function test_form_state_hides_stored_secrets(): void
    {
        $state = TenantPaymentCatalog::formChannel('stripe', [
            'enabled' => true,
            'secret_key' => 'sk_live_saved',
            'publishable_key' => 'pk_live_saved',
        ]);

        $this->assertSame('', $state['secret_key']);
        $this->assertSame('pk_live_saved', $state['publishable_key']);
        $this->assertTrue($state['enabled']);
    }

    public function test_bank_transfer_is_repeatable_with_multiple_accounts(): void
    {
        $this->assertTrue(TenantPaymentCatalog::isRepeatable('bank_transfer'));
        $this->assertSame('accounts', TenantPaymentCatalog::repeaterKey('bank_transfer'));
        $this->assertFalse(TenantPaymentCatalog::isRepeatable('stripe'));
    }

    public function test_bank_transfer_merges_multiple_accounts_and_drops_empty_rows(): void
    {
        $merged = TenantPaymentCatalog::mergeChannel('bank_transfer', [
            'enabled' => true,
            'accounts' => [
                ['bank_name' => 'الراجحي', 'account_name' => 'النادي', 'iban' => 'SA0001'],
                ['bank_name' => 'الأهلي', 'account_name' => 'النادي', 'iban' => 'SA0002', 'account_number' => '123'],
                ['bank_name' => '', 'account_name' => '', 'iban' => ''],
            ],
        ], []);

        $this->assertTrue($merged['enabled']);
        $this->assertCount(2, $merged['accounts']);
        $this->assertSame('الراجحي', $merged['accounts'][0]['bank_name']);
        $this->assertSame('الأهلي', $merged['accounts'][1]['bank_name']);
    }

    public function test_bank_transfer_requires_at_least_one_valid_account_when_enabled(): void
    {
        $this->assertSame(['accounts'], TenantPaymentCatalog::missingRequired('bank_transfer', [
            'enabled' => true,
            'accounts' => [],
        ]));

        $this->assertSame(['accounts'], TenantPaymentCatalog::missingRequired('bank_transfer', [
            'enabled' => true,
            'accounts' => [
                ['bank_name' => 'الراجحي', 'account_name' => '', 'iban' => 'SA0001'],
            ],
        ]));

        $this->assertSame([], TenantPaymentCatalog::missingRequired('bank_transfer', [
            'enabled' => true,
            'accounts' => [
                ['bank_name' => 'الراجحي', 'account_name' => 'النادي', 'iban' => 'SA0001'],
            ],
        ]));
    }

    public function test_form_channel_exposes_stored_accounts_list(): void
    {
        $state = TenantPaymentCatalog::formChannel('bank_transfer', [
            'enabled' => true,
            'accounts' => [
                ['bank_name' => 'الراجحي', 'account_name' => 'النادي', 'iban' => 'SA0001'],
            ],
        ]);

        $this->assertTrue($state['enabled']);
        $this->assertCount(1, $state['accounts']);
        $this->assertSame('الراجحي', $state['accounts'][0]['bank_name']);
    }

    public function test_every_channel_has_a_bundled_logo_path(): void
    {
        foreach (array_keys(TenantPaymentCatalog::channels()) as $key) {
            $this->assertNotNull(TenantPaymentCatalog::logoPath($key), "Channel {$key} is missing a bundled logo.");
        }
    }
}
