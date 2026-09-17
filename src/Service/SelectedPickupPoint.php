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

        $postCode = $this->information['post_code'] ?? '';
        $city = $this->information['city'] ?? '';
        if ($postCode !== '' || $city !== '') {
            $addressLines[] = trim($postCode . ' - ' . $city, ' -');
        }

        return $addressLines;
    }
}
