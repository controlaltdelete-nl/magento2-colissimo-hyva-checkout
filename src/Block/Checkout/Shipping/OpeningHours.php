<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Block\Checkout\Shipping;

use Magento\Framework\View\Element\Template;

class OpeningHours extends Template
{
    private const CLOSED_HOUR_RANGE = '00:00-00:00';

    protected $_template = 'ControlAltDelete_ColissimoHyva::checkout/shipping/opening-hours.phtml';

    public function getPickupPoint(): object
    {
        return $this->getData('pickupPoint');
    }

    public function getHours(string $hours): array
    {
        $hourRanges = array_filter(
            explode(' ', trim($hours)),
            fn (string $hourRange): bool => $hourRange !== '' && $hourRange !== self::CLOSED_HOUR_RANGE
        );

        return array_values(array_map(
            function (string $hourRange): array {
                [$start, $end] = array_pad(explode('-', $hourRange), 2, '');

                return ['start' => $start, 'end' => $end];
            },
            $hourRanges
        ));
    }
}
