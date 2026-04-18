<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Block\Checkout\Shipping;

use Magento\Framework\View\Element\Template;

class RelayPoint extends Template
{
    protected $_template = 'ControlAltDelete_ColissimoHyva::checkout/shipping/relay-point.phtml';

    public function formatDistance(int $distanceEnMetre): string
    {
        if ($distanceEnMetre < 1000) {
            return sprintf('%d m', $distanceEnMetre);
        }

        return sprintf('%.1f km', $distanceEnMetre / 1000);
    }
}
