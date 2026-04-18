<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Service;

class SelectedPickupPoint
{
    public function __construct(
        private readonly array $information
    ) {}

    public function getName(): string
    {
        return $this->information['name'] ?? 'Selected Relay Point';
    }

    public function getAddressLines(): array
    {
        $addressLines = [];

        if (!empty($this->information['address'])) {
            $addressLines[] = $this->information['address'];
        }

        $cityLine = trim(($this->information['post_code'] ?? '') . ' - ' . ($this->information['city'] ?? ''));
        if ($cityLine !== ' - ') {
            $addressLines[] = $cityLine;
        }

        return $addressLines;
    }
}
