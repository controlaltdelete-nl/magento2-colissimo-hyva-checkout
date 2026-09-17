<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Service;

use ControlAltDelete\ColissimoHyva\Config;
use Exception;
use Magento\Framework\HTTP\ClientFactory;

class GetLocationFromGoogleMaps
{
    public function __construct(
        private readonly Config $config,
        private readonly ClientFactory $clientFactory,
    ) {}

    /** @return array{city: string|null, postalCode: string|null, countryCode: string|null} */
    public function byLatitudeLongitude(string $region, float $latitude, float $longitude): array
    {
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?' . http_build_query([
            'latlng' => $latitude . ',' . $longitude,
            'region' => $region,
        ]);

        $response = $this->makeRequest($url);

        return $this->extractLocationInfo($response['results'][0]);
    }

    /**
     * @param array<string, mixed> $result
     * @return array{city: string|null, postalCode: string|null, countryCode: string|null}
     */
    private function extractLocationInfo(array $result): array
    {
        $city        = null;
        $postalCode  = null;
        $countryCode = null;

        foreach ($result['address_components'] as $component) {
            $types = $component['types'];

            if (in_array('locality', $types) || in_array('sublocality', $types)) {
                $city = $component['long_name'];
            } elseif (in_array('postal_code', $types)) {
                $postalCode = $component['long_name'];
            } elseif (in_array('country', $types)) {
                $countryCode = $component['short_name'];
            }
        }

        // If no locality found, try administrative_area_level_2 (commune)
        if (!$city) {
            foreach ($result['address_components'] as $component) {
                if (in_array('administrative_area_level_2', $component['types'])) {
                    $city = $component['long_name'];
                    break;
                }
            }
        }

        return [
            'city'        => $city,
            'postalCode'  => $postalCode,
            'countryCode' => $countryCode,
        ];
    }

    private function getApiKey(): string
    {
        return $this->config->getGoogleMapsApiKey();
    }

    /** @return array<string, mixed> */
    private function makeRequest(string $url): array
    {
        $client = $this->clientFactory->create();
        $client->get($url . '&key=' . $this->getApiKey());

        $response = json_decode($client->getBody(), true);

        if ($response === null || $response['status'] !== 'OK' || empty($response['results'])) {
            throw new Exception('Unable to reverse geocode coordinates');
        }

        return $response;
    }
}
