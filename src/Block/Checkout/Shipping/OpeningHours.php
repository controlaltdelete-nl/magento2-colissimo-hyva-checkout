<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Block\Checkout\Shipping;

use ControlAltDelete\ColissimoHyva\Service\GetOpeningHourRanges;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class OpeningHours extends Template
{
    protected $_template = 'ControlAltDelete_ColissimoHyva::checkout/shipping/opening-hours.phtml';

    /** @param array<string, mixed> $data */
    public function __construct(
        Context $context,
        private readonly GetOpeningHourRanges $getOpeningHourRanges,
        array $data = [],
    ) {
        parent::__construct($context, $data);
    }

    public function getPickupPoint(): object
    {
        return $this->getData('pickupPoint');
    }

    /** @return list<array{start: string, end: string}> */
    public function getHours(string $hours): array
    {
        return $this->getOpeningHourRanges->execute($hours);
    }
}
