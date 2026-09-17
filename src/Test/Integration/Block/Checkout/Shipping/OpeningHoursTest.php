<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Test\Integration\Block\Checkout\Shipping;

use ControlAltDelete\ColissimoHyva\Block\Checkout\Shipping\OpeningHours;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

#[AppArea('frontend')]
class OpeningHoursTest extends TestCase
{
    #[Test]
    public function itShowsTheOpeningHoursAndClosedDaysOfAPickupPoint(): void
    {
        $pickupPoint = new stdClass();
        foreach (['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'] as $day) {
            $pickupPoint->{'horairesOuverture' . $day} = '09:00-12:00 14:00-18:00';
        }
        $pickupPoint->horairesOuvertureDimanche = '00:00-00:00 00:00-00:00';

        $html = Bootstrap::getObjectManager()->get(LayoutInterface::class)
            ->createBlock(OpeningHours::class, '', ['data' => ['pickupPoint' => $pickupPoint]])
            ->toHtml();

        $this->assertStringContainsString('09:00 - 12:00', $html);
        $this->assertStringContainsString('Closed', $html);
        $this->assertStringNotContainsString('00:00 - 00:00', $html);
    }
}
