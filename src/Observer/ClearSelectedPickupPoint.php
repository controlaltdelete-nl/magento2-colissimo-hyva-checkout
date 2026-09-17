<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ClearSelectedPickupPoint implements ObserverInterface
{
    public function __construct(
        private readonly Session $checkoutSession,
    ) {}

    public function execute(Observer $observer): void
    {
        $this->checkoutSession->unsLpcRelayInformation();
    }
}
