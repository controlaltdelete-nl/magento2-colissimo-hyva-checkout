<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Service;

use Magento\Framework\HTTP\ClientFactory;

class TestGoogleMapsApiKey
{
    private const GEOCODE_URL = 'https://maps.googleapis.com/maps/api/geocode/json';
    private const TEST_COORDINATES = '48.8566,2.3522';
    private const SUCCESS_STATUS = 'OK';
    private const TIMEOUT_IN_SECONDS = 10;

    public function __construct(
        private readonly ClientFactory $clientFactory,
    ) {}

    /** @return array{success: bool, message: string} */
    public function execute(string $apiKey): array
    {
        $client = $this->clientFactory->create();
        $client->setTimeout(self::TIMEOUT_IN_SECONDS);
        $client->get(self::GEOCODE_URL . '?' . http_build_query([
            'latlng' => self::TEST_COORDINATES,
            'key' => $apiKey,
        ]));

        $response = json_decode($client->getBody(), true) ?? [];
        $status = $response['status'] ?? 'INVALID_RESPONSE';

        if ($status === self::SUCCESS_STATUS) {
            return ['success' => true, 'message' => (string)__('OK')];
        }

        return [
            'success' => false,
            'message' => trim($status . ': ' . ($response['error_message'] ?? ''), ': '),
        ];
    }
}
