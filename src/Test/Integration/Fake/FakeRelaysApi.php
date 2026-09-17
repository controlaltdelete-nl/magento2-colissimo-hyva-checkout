<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Test\Integration\Fake;

use LaPoste\Colissimo\Model\RelaysWebservice\RelaysApi;
use stdClass;

class FakeRelaysApi extends RelaysApi
{
    private array $requests = [];

    public function __construct(
        private readonly array $pickupPoints = [],
        private readonly int $errorCode = 0,
        private readonly string $errorMessage = '',
    ) {}

    public function getRequests(): array
    {
        return $this->requests;
    }

    public function getRelays($params): stdClass
    {
        $this->requests[] = $params;

        $response = new stdClass();
        $response->return = new stdClass();
        $response->return->errorCode = $this->errorCode;
        $response->return->errorMessage = $this->errorMessage;
        $response->return->listePointRetraitAcheminement = $this->pickupPoints;

        return $response;
    }
}
