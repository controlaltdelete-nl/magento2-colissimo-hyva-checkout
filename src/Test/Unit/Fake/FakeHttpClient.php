<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Test\Unit\Fake;

use Magento\Framework\HTTP\ClientInterface;

class FakeHttpClient implements ClientInterface
{
    private array $requestedUris = [];
    private ?int $timeout = null;

    public function __construct(
        private readonly string $responseBody,
    ) {}

    public function getRequestedUris(): array
    {
        return $this->requestedUris;
    }

    public function getTimeout(): ?int
    {
        return $this->timeout;
    }

    public function setTimeout($value): void
    {
        $this->timeout = $value;
    }

    public function setHeaders($headers): void
    {
    }

    public function addHeader($name, $value): void
    {
    }

    public function removeHeader($name): void
    {
    }

    public function setCredentials($login, $pass): void
    {
    }

    public function addCookie($name, $value): void
    {
    }

    public function removeCookie($name): void
    {
    }

    public function setCookies($cookies): void
    {
    }

    public function removeCookies(): void
    {
    }

    public function get($uri): void
    {
        $this->requestedUris[] = $uri;
    }

    public function post($uri, $params): void
    {
        $this->requestedUris[] = $uri;
    }

    public function getHeaders(): array
    {
        return [];
    }

    public function getBody(): string
    {
        return $this->responseBody;
    }

    public function getStatus(): int
    {
        return 200;
    }

    public function getCookies(): array
    {
        return [];
    }

    public function setOption($key, $value): void
    {
    }

    public function setOptions($arr): void
    {
    }
}
