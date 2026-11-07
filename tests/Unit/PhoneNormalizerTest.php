<?php

namespace Tests\Unit;

use App\Support\PhoneNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PhoneNormalizerTest extends TestCase
{
    #[DataProvider('saudiVariants')]
    public function test_saudi_numbers_normalise_to_one_e164_value(string $raw): void
    {
        $this->assertSame('+966501234567', PhoneNormalizer::toE164($raw, '966'));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function saudiVariants(): array
    {
        return [
            'local with zero' => ['0501234567'],
            'local with spaces' => ['050 123 4567'],
            'local with dashes' => ['050-123-4567'],
            'no leading zero' => ['501234567'],
            'international plus' => ['+966501234567'],
            'international plus spaced' => ['+966 50 123 4567'],
            'double zero prefix' => ['00966501234567'],
            'country code only' => ['966501234567'],
            'extra zero after country code' => ['9660501234567'],
            'arabic indic digits' => ['٠٥٠١٢٣٤٥٦٧'],
        ];
    }

    public function test_foreign_numbers_keep_their_country_code(): void
    {
        $this->assertSame('+14155238886', PhoneNormalizer::toE164('+1 (415) 523-8886', '966'));
    }

    #[DataProvider('invalidNumbers')]
    public function test_invalid_numbers_are_rejected(?string $raw): void
    {
        $this->assertNull(PhoneNormalizer::toE164($raw, '966'));
    }

    /**
     * @return array<string, array{?string}>
     */
    public static function invalidNumbers(): array
    {
        return [
            'null' => [null],
            'empty' => [''],
            'letters' => ['abcdefgh'],
            'too short' => ['12345'],
            'bad saudi length' => ['1234567890'],
            'too long' => ['+1234567890123456'],
        ];
    }
}
