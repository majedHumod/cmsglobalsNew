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
        $state = TenantPaymentCatalog::formChannel('bank_transfer', [
            'enabled' => true,
            'bank_name' => 'الراجحي',
            'account_name' => 'النادي',
            'iban' => 'SA000',
        ]);

        $this->assertSame('الراجحي', $state['bank_name']);
        $this->assertTrue($state['enabled']);
    }
}
