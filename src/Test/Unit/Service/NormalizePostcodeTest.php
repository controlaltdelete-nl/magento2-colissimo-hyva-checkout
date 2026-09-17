<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Test\Unit\Service;

use ControlAltDelete\ColissimoHyva\Service\NormalizePostcode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class NormalizePostcodeTest extends TestCase
{
    /** @return array<string, array{string, string, string}> */
    public static function postcodesInTheirLocalFormat(): array
    {
        return [
            'Dutch postcode with a space' => ['NL', '1795 AD', '1795AD'],
            'German postcode with a space' => ['DE', '101 15', '10115'],
            'Belgian postcode with surrounding spaces' => ['BE', ' 1000 ', '1000'],
            'Portuguese postcode with a dash' => ['PT', '1100-148', '1100148'],
            'Polish postcode with a dash' => ['PL', '00-001', '00001'],
            'Irish postcode with a space' => ['IE', 'D02 X285', 'D02X285'],
            'Luxembourg postcode with the L- prefix' => ['LU', 'L-1111', '1111'],
            'Luxembourg postcode with the L prefix' => ['LU', 'l1111', '1111'],
            'French postcode that is already valid' => ['FR', '30000', '30000'],
        ];
    }

    #[Test]
    #[DataProvider('postcodesInTheirLocalFormat')]
    public function itRemovesTheCharactersThatColissimoRejects(string $countryCode, string $postcode, string $expected): void
    {
        $normalizePostcode = new NormalizePostcode();

        $normalizedPostcode = $normalizePostcode->execute($countryCode, $postcode);

        $this->assertSame($expected, $normalizedPostcode);
    }

    #[Test]
    public function itOnlyRemovesTheLetterLPrefixForLuxembourg(): void
    {
        $normalizePostcode = new NormalizePostcode();

        $normalizedPostcode = $normalizePostcode->execute('GB', 'L1111');

        $this->assertSame('L1111', $normalizedPostcode);
    }
}
