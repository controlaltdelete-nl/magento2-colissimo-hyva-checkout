<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Test\Integration\Observer;

use ControlAltDelete\ColissimoHyva\Observer\ClearSelectedPickupPoint;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\ConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[AppArea('frontend')]
class ClearSelectedPickupPointTest extends TestCase
{
    #[Test]
    public function itRunsAfterAnOrderIsPlacedInTheFrontend(): void
    {
        $eventConfig = Bootstrap::getObjectManager()->get(ConfigInterface::class);

        $observers = $eventConfig->getObservers('checkout_submit_all_after');

        $this->assertSame(
            ClearSelectedPickupPoint::class,
            $observers['controlaltdelete_colissimo_hyva_clear_selected_pickup_point']['instance'] ?? null
        );
    }

    #[Test]
    public function itRemovesTheSelectedPickupPointFromTheCheckoutSession(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $checkoutSession = $objectManager->get(Session::class);
        $checkoutSession->setLpcRelayInformation(['id' => '757170']);

        $objectManager->get(ClearSelectedPickupPoint::class)->execute(new Observer());

        $this->assertNull($checkoutSession->getLpcRelayInformation());
    }
}
