<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Block\Checkout\Shipping;

use Magento\Framework\View\Element\Template;

class OpeningHours extends Template
{
    protected $_template = 'ControlAltDelete_ColissimoHyva::checkout/shipping/opening-hours.phtml';

    public function getPickupPoint(): object
    {
        return $this->getData('pickupPoint');
    }

    public function getHours(string $hours): array
    {
        $hours = str_replace(' 00:00-00:00', '', $hours);

        $hourRanges = explode(' ', $hours);
        foreach ($hourRanges as $index => $hourRange) {
            $parts = explode('-', $hourRange);
            $parts = array_map('ltrim', $parts);

            $hourRanges[$index] = ['start' => $parts[0], 'end' => $parts[1]];
        }

        return $hourRanges;
    }
}
