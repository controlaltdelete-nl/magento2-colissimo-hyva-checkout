<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Test\Unit\Service;

use ControlAltDelete\ColissimoHyva\Service\SelectedPickupPoint;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SelectedPickupPointTest extends TestCase
{
    #[Test]
    public function itReturnsTheNameOfThePickupPoint(): void
    {
        $selectedPickupPoint = new SelectedPickupPoint(['name' => 'BUREAU DE POSTE NIMES ROMANITE']);

        $name = $selectedPickupPoint->getName();

        $this->assertSame('BUREAU DE POSTE NIMES ROMANITE', $name);
    }

    #[Test]
    public function itReturnsADefaultNameWhenThePickupPointHasNoName(): void
    {
        $selectedPickupPoint = new SelectedPickupPoint([]);

        $name = $selectedPickupPoint->getName();

        $this->assertSame('Selected Relay Point', $name);
    }

    #[Test]
    public function itReturnsTheAddressAndThePostcodeWithTheCity(): void
    {
        $selectedPickupPoint = new SelectedPickupPoint([
            'address' => '1 RUE DE LA REPUBLIQUE',
            'post_code' => '30000',
            'city' => 'NIMES',
        ]);

        $addressLines = $selectedPickupPoint->getAddressLines();

        $this->assertSame(['1 RUE DE LA REPUBLIQUE', '30000 - NIMES'], $addressLines);
    }

    #[Test]
    public function itReturnsOnlyTheCityWhenThereIsNoPostcode(): void
    {
        $selectedPickupPoint = new SelectedPickupPoint(['city' => 'NIMES']);

        $addressLines = $selectedPickupPoint->getAddressLines();

        $this->assertSame(['NIMES'], $addressLines);
    }

    #[Test]
    public function itReturnsNoAddressLinesWhenThereIsNoAddressInformation(): void
    {
        $selectedPickupPoint = new SelectedPickupPoint([]);

        $addressLines = $selectedPickupPoint->getAddressLines();

        $this->assertSame([], $addressLines);
    }
}
