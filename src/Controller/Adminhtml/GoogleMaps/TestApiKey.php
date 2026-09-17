<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Controller\Adminhtml\GoogleMaps;

use ControlAltDelete\ColissimoHyva\Service\TestGoogleMapsApiKey;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

class TestApiKey extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Magento_Shipping::carriers';

    public function __construct(
        Context $context,
        private readonly JsonFactory $jsonFactory,
        private readonly TestGoogleMapsApiKey $testGoogleMapsApiKey,
    ) {
        parent::__construct($context);
    }

    public function execute(): Json
    {
        $result = $this->jsonFactory->create();
        $apiKey = trim((string)$this->getRequest()->getParam('api_key'));

        if ($apiKey === '') {
            return $result->setData(['success' => false, 'message' => (string)__('Enter an API key first.')]);
        }

        try {
            return $result->setData($this->testGoogleMapsApiKey->execute($apiKey));
        } catch (Exception $exception) {
            return $result->setData(['success' => false, 'message' => $exception->getMessage()]);
        }
    }
}
