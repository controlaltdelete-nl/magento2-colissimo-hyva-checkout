<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Service;

class NormalizePostcode
{
    private const LUXEMBOURG_COUNTRY_CODE = 'LU';
    private const LUXEMBOURG_POSTCODE_PREFIX = '/^L(?=\d{4}$)/i';
    private const EVERYTHING_EXCEPT_LETTERS_AND_DIGITS = '/[^A-Za-z0-9]/';

    public function execute(string $countryCode, string $postcode): string
    {
        $lettersAndDigits = (string)preg_replace(self::EVERYTHING_EXCEPT_LETTERS_AND_DIGITS, '', $postcode);

        if (strtoupper($countryCode) !== self::LUXEMBOURG_COUNTRY_CODE) {
            return $lettersAndDigits;
        }

        return (string)preg_replace(self::LUXEMBOURG_POSTCODE_PREFIX, '', $lettersAndDigits);
    }
}
