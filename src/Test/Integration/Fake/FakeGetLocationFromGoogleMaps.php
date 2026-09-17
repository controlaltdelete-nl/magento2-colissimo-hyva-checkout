<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Test\Integration\Fake;

use ControlAltDelete\ColissimoHyva\Service\GetLocationFromGoogleMaps;

class FakeGetLocationFromGoogleMaps extends GetLocationFromGoogleMaps
{
    public function __construct(
        private readonly array $location,
    ) {}

    public function byLatitudeLongitude(string $region, float $latitude, float $longitude): array
    {
        return $this->location;
    }
}
