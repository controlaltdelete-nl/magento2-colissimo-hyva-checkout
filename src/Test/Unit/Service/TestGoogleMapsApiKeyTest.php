<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Test\Unit\Service;

use ControlAltDelete\ColissimoHyva\Service\TestGoogleMapsApiKey;
use ControlAltDelete\ColissimoHyva\Test\Unit\Fake\FakeClientFactory;
use ControlAltDelete\ColissimoHyva\Test\Unit\Fake\FakeHttpClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TestGoogleMapsApiKeyTest extends TestCase
{
    #[Test]
    public function itReportsSuccessWhenGoogleAcceptsTheApiKey(): void
    {
        $testGoogleMapsApiKey = new TestGoogleMapsApiKey(
            new FakeClientFactory(new FakeHttpClient('{"status": "OK", "results": []}'))
        );

        $result = $testGoogleMapsApiKey->execute('valid-key');

        $this->assertSame(['success' => true, 'message' => 'OK'], $result);
    }

    #[Test]
    public function itReportsTheStatusAndMessageWhenGoogleDeniesTheApiKey(): void
    {
        $testGoogleMapsApiKey = new TestGoogleMapsApiKey(new FakeClientFactory(new FakeHttpClient(
            '{"status": "REQUEST_DENIED", "error_message": "The provided API key is invalid."}'
        )));

        $result = $testGoogleMapsApiKey->execute('invalid-key');

        $this->assertSame(
            ['success' => false, 'message' => 'REQUEST_DENIED: The provided API key is invalid.'],
            $result
        );
    }

    #[Test]
    public function itReportsAnInvalidResponseWhenGoogleDoesNotReturnJson(): void
    {
        $testGoogleMapsApiKey = new TestGoogleMapsApiKey(
            new FakeClientFactory(new FakeHttpClient('<html>Bad gateway</html>'))
        );

        $result = $testGoogleMapsApiKey->execute('valid-key');

        $this->assertSame(['success' => false, 'message' => 'INVALID_RESPONSE'], $result);
    }

    #[Test]
    public function itSendsTheApiKeyToTheGeocodingApiWithATimeout(): void
    {
        $httpClient = new FakeHttpClient('{"status": "OK"}');
        $testGoogleMapsApiKey = new TestGoogleMapsApiKey(new FakeClientFactory($httpClient));

        $testGoogleMapsApiKey->execute('my-api-key');

        $this->assertStringStartsWith(
            'https://maps.googleapis.com/maps/api/geocode/json?',
            $httpClient->getRequestedUris()[0]
        );
        $this->assertStringContainsString('key=my-api-key', $httpClient->getRequestedUris()[0]);
        $this->assertSame(10, $httpClient->getTimeout());
    }
}
