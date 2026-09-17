<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Test\Unit\Service;

use ControlAltDelete\ColissimoHyva\Config;
use ControlAltDelete\ColissimoHyva\Service\GetLocationFromGoogleMaps;
use ControlAltDelete\ColissimoHyva\Test\Unit\Fake\FakeClientFactory;
use ControlAltDelete\ColissimoHyva\Test\Unit\Fake\FakeHttpClient;
use ControlAltDelete\ColissimoHyva\Test\Unit\Fake\FakeScopeConfig;
use Exception;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GetLocationFromGoogleMapsTest extends TestCase
{
    private const NIMES_RESPONSE = <<<JSON
        {
            "status": "OK",
            "results": [{
                "address_components": [
                    {"long_name": "30000", "short_name": "30000", "types": ["postal_code"]},
                    {"long_name": "Nîmes", "short_name": "Nîmes", "types": ["locality", "political"]},
                    {"long_name": "Gard", "short_name": "Gard", "types": ["administrative_area_level_2", "political"]},
                    {"long_name": "France", "short_name": "FR", "types": ["country", "political"]}
                ]
            }]
        }
        JSON;

    #[Test]
    public function itReturnsTheCityPostcodeAndCountryForCoordinates(): void
    {
        $getLocationFromGoogleMaps = $this->createGetLocationFromGoogleMaps(new FakeHttpClient(self::NIMES_RESPONSE));

        $location = $getLocationFromGoogleMaps->byLatitudeLongitude('FR', 43.8367, 4.3601);

        $this->assertSame(['city' => 'Nîmes', 'postalCode' => '30000', 'countryCode' => 'FR'], $location);
    }

    #[Test]
    public function itUsesTheAdministrativeAreaAsCityWhenThereIsNoLocality(): void
    {
        $getLocationFromGoogleMaps = $this->createGetLocationFromGoogleMaps(new FakeHttpClient(<<<JSON
            {
                "status": "OK",
                "results": [{
                    "address_components": [
                        {"long_name": "30190", "short_name": "30190", "types": ["postal_code"]},
                        {"long_name": "Gard", "short_name": "Gard", "types": ["administrative_area_level_2", "political"]},
                        {"long_name": "France", "short_name": "FR", "types": ["country", "political"]}
                    ]
                }]
            }
            JSON));

        $location = $getLocationFromGoogleMaps->byLatitudeLongitude('FR', 43.9, 4.3);

        $this->assertSame('Gard', $location['city']);
    }

    #[Test]
    public function itSendsTheCoordinatesAndConfiguredApiKeyToGoogle(): void
    {
        $httpClient = new FakeHttpClient(self::NIMES_RESPONSE);
        $getLocationFromGoogleMaps = $this->createGetLocationFromGoogleMaps($httpClient);

        $getLocationFromGoogleMaps->byLatitudeLongitude('FR', 43.8367, 4.3601);

        $this->assertStringContainsString('latlng=43.8367%2C4.3601', $httpClient->getRequestedUris()[0]);
        $this->assertStringEndsWith('&key=configured-api-key', $httpClient->getRequestedUris()[0]);
    }

    #[Test]
    public function itThrowsWhenGoogleDoesNotReturnALocation(): void
    {
        $getLocationFromGoogleMaps = $this->createGetLocationFromGoogleMaps(
            new FakeHttpClient('{"status": "REQUEST_DENIED", "results": []}')
        );

        $this->expectException(Exception::class);

        $getLocationFromGoogleMaps->byLatitudeLongitude('FR', 43.8367, 4.3601);
    }

    private function createGetLocationFromGoogleMaps(FakeHttpClient $httpClient): GetLocationFromGoogleMaps
    {
        return new GetLocationFromGoogleMaps(
            new Config(new FakeScopeConfig([
                'controlaltdelete_colissimo_hyva/google_maps/api_key' => 'configured-api-key',
            ])),
            new FakeClientFactory($httpClient)
        );
    }
}
