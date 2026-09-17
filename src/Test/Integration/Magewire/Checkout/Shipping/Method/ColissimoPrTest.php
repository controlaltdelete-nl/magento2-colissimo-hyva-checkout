<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Test\Integration\Magewire\Checkout\Shipping\Method;

use ControlAltDelete\ColissimoHyva\Magewire\Checkout\Shipping\Method\ColissimoPr;
use Hyva\Checkout\Model\Magewire\Component\Evaluation\ErrorMessage;
use Hyva\Checkout\Model\Magewire\Component\Evaluation\Success;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Magento\Checkout\Model\Session;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[AppArea('frontend')]
class ColissimoPrTest extends TestCase
{
    private Session $checkoutSession;

    protected function setUp(): void
    {
        $this->checkoutSession = Bootstrap::getObjectManager()->get(Session::class);
        $this->checkoutSession->unsLpcRelayInformation();
    }

    #[Test]
    public function itRequiresAPickupPointWhenColissimoPickupIsSelected(): void
    {
        $this->checkoutSession->getQuote()->getShippingAddress()->setShippingMethod('colissimo_pr');

        $result = $this->evaluateCompletion();

        $this->assertInstanceOf(ErrorMessage::class, $result);
    }

    #[Test]
    public function itAcceptsColissimoPickupWhenAPickupPointIsSelected(): void
    {
        $this->checkoutSession->getQuote()->getShippingAddress()->setShippingMethod('colissimo_pr');
        $this->checkoutSession->setLpcRelayInformation(['id' => '757170']);

        $result = $this->evaluateCompletion();

        $this->assertInstanceOf(Success::class, $result);
    }

    #[Test]
    public function itDoesNotRequireAPickupPointForOtherShippingMethods(): void
    {
        $this->checkoutSession->getQuote()->getShippingAddress()->setShippingMethod('flatrate_flatrate');

        $result = $this->evaluateCompletion();

        $this->assertInstanceOf(Success::class, $result);
    }

    private function evaluateCompletion(): object
    {
        $objectManager = Bootstrap::getObjectManager();

        return $objectManager->create(ColissimoPr::class)
            ->evaluateCompletion($objectManager->get(EvaluationResultFactory::class));
    }
}
