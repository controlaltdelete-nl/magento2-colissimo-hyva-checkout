<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Test\Integration\Controller\Adminhtml\GoogleMaps;

use ControlAltDelete\ColissimoHyva\Service\TestGoogleMapsApiKey;
use ControlAltDelete\ColissimoHyva\Test\Integration\Fake\FakeTestGoogleMapsApiKey;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Form\FormKey;
use Magento\TestFramework\TestCase\AbstractBackendController;
use PHPUnit\Framework\Attributes\Test;

class TestApiKeyTest extends AbstractBackendController
{
    protected $resource = 'Magento_Shipping::carriers';
    protected $uri = 'backend/colissimohyva/googlemaps/testapikey';
    protected $httpMethod = Http::METHOD_POST;

    #[Test]
    public function itAsksForAnApiKeyWhenNoApiKeyIsEntered(): void
    {
        $this->dispatchTestRequest(['api_key' => '  ']);

        $this->assertSame(
            ['success' => false, 'message' => 'Enter an API key first.'],
            json_decode($this->getResponse()->getBody(), true)
        );
    }

    #[Test]
    public function itReturnsTheResultOfTestingTheEnteredApiKey(): void
    {
        $testGoogleMapsApiKey = new FakeTestGoogleMapsApiKey(['success' => true, 'message' => 'OK']);
        $this->_objectManager->addSharedInstance($testGoogleMapsApiKey, TestGoogleMapsApiKey::class);

        $this->dispatchTestRequest(['api_key' => ' entered-api-key ']);

        $this->assertSame(['entered-api-key'], $testGoogleMapsApiKey->getTestedApiKeys());
        $this->assertSame(
            ['success' => true, 'message' => 'OK'],
            json_decode($this->getResponse()->getBody(), true)
        );
    }

    protected function tearDown(): void
    {
        $this->_objectManager->removeSharedInstance(TestGoogleMapsApiKey::class);

        parent::tearDown();
    }

    private function dispatchTestRequest(array $postValues): void
    {
        $this->getRequest()->setMethod(Http::METHOD_POST);
        $this->getRequest()->setPostValue(
            $postValues + ['form_key' => $this->_objectManager->get(FormKey::class)->getFormKey()]
        );

        $this->dispatch($this->uri);
    }
}
