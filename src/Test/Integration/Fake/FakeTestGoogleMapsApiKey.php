<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Test\Integration\Fake;

use ControlAltDelete\ColissimoHyva\Service\TestGoogleMapsApiKey;

class FakeTestGoogleMapsApiKey extends TestGoogleMapsApiKey
{
    private array $testedApiKeys = [];

    public function __construct(
        private readonly array $result,
    ) {}

    public function getTestedApiKeys(): array
    {
        return $this->testedApiKeys;
    }

    public function execute(string $apiKey): array
    {
        $this->testedApiKeys[] = $apiKey;

        return $this->result;
    }
}
