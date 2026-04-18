<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Service;

use ControlAltDelete\ColissimoHyva\Config;
use Exception;
use Magento\Framework\HTTP\Client\Curl;

class GetLocationFromGoogleMaps
{
    public function __construct(
        private readonly Config $config,
        private readonly Curl $curl,
    ) {}

    public function byLatitudeLongitude(string $region, float $latitude, float $longitude): array
    {
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?' . http_build_query([
            'latlng' => $latitude . ',' . $longitude,
            'region' => $region,
        ]);

        $response = $this->makeRequest($url);

        return $this->extractLocationInfo($response['results'][0]);
    }

    public function byZipcode(string $country, string $zipcode): array
    {
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?' . http_build_query([
            'address' => $zipcode . ', ' . $country,
            'region'  => $this->config->getRegion(),
        ]);

        $response = $this->makeRequest($url);

        return $this->extractLocationInfo($response['results'][0]);
    }

    public function byAddress(string $address): array
    {
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?' . http_build_query([
            'address' => $address,
            'region'  => $this->config->getRegion(),
        ]);

        $response = $this->makeRequest($url);

        return $this->extractLocationInfo($response['results'][0], true);
    }

    private function extractLocationInfo(array $result, bool $getCoordinates = false): array
    {
        if ($getCoordinates) {
            return $result['geometry']['location'] ?? [];
        }

        $city       = null;
        $postalCode = null;

        foreach ($result['address_components'] as $component) {
            $types = $component['types'];

            if (in_array('locality', $types) || in_array('sublocality', $types)) {
                $city = $component['long_name'];
            } elseif (in_array('postal_code', $types)) {
                $postalCode = $component['long_name'];
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
            'city'       => $city,
            'postalCode' => $postalCode,
        ];
    }

    private function getApiKey(): string
    {
        return $this->config->getGoogleMapsApiKey();
    }

    private function makeRequest(string $url): array
    {
        $this->curl->get($url . '&key=' . $this->getApiKey());

        $response = json_decode($this->curl->getBody(), true);

        if ($response === null || $response['status'] !== 'OK' || empty($response['results'])) {
            throw new Exception('Unable to reverse geocode coordinates');
        }

        return $response;
    }
}
