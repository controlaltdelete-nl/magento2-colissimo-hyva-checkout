<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Test\Integration\Config;

use ControlAltDelete\ColissimoHyva\Config;
use Magento\Config\Model\Config\Structure;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GoogleMapsApiKeyTest extends TestCase
{
    private const API_KEY_CONFIG_PATH = 'controlaltdelete_colissimo_hyva/google_maps/api_key';

    #[Test]
    #[AppArea('adminhtml')]
    public function itSavesTheAdminFieldToTheConfigPathThatTheExtensionReads(): void
    {
        $structure = Bootstrap::getObjectManager()->get(Structure::class);

        $field = $structure->getElement('carriers/lpc_group/google_maps/api_key');

        $this->assertSame(self::API_KEY_CONFIG_PATH, $field->getConfigPath());
    }

    #[Test]
    #[ConfigFixture(self::API_KEY_CONFIG_PATH, 'configured-api-key')]
    public function itReadsTheConfiguredApiKey(): void
    {
        $config = Bootstrap::getObjectManager()->get(Config::class);

        $apiKey = $config->getGoogleMapsApiKey();

        $this->assertSame('configured-api-key', $apiKey);
    }
}
