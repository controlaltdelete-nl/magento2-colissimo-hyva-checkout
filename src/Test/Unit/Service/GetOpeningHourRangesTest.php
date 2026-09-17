<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Test\Unit\Service;

use ControlAltDelete\ColissimoHyva\Service\GetOpeningHourRanges;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GetOpeningHourRangesTest extends TestCase
{
    #[Test]
    public function itReturnsBothRangesForADayWithTwoOpeningSlots(): void
    {
        $getOpeningHourRanges = new GetOpeningHourRanges();

        $hourRanges = $getOpeningHourRanges->execute('09:00-12:00 14:00-18:00');

        $this->assertSame(
            [['start' => '09:00', 'end' => '12:00'], ['start' => '14:00', 'end' => '18:00']],
            $hourRanges
        );
    }

    #[Test]
    public function itIgnoresAClosedSecondSlot(): void
    {
        $getOpeningHourRanges = new GetOpeningHourRanges();

        $hourRanges = $getOpeningHourRanges->execute('09:00-19:00 00:00-00:00');

        $this->assertSame([['start' => '09:00', 'end' => '19:00']], $hourRanges);
    }

    #[Test]
    public function itReturnsNoRangesForAClosedDay(): void
    {
        $getOpeningHourRanges = new GetOpeningHourRanges();

        $hourRanges = $getOpeningHourRanges->execute('00:00-00:00 00:00-00:00');

        $this->assertSame([], $hourRanges);
    }

    #[Test]
    public function itReturnsNoRangesWhenThereAreNoOpeningHours(): void
    {
        $getOpeningHourRanges = new GetOpeningHourRanges();

        $hourRanges = $getOpeningHourRanges->execute('');

        $this->assertSame([], $hourRanges);
    }
}
