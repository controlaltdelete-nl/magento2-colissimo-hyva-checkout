<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Test\Unit\Fake;

use Magento\Framework\HTTP\ClientFactory;
use Magento\Framework\HTTP\ClientInterface;

class FakeClientFactory extends ClientFactory
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {}

    public function create(array $data = []): ClientInterface
    {
        return $this->client;
    }
}
