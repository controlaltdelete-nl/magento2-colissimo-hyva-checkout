<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Service;

class GetOpeningHourRanges
{
    private const CLOSED_HOUR_RANGE = '00:00-00:00';

    public function execute(string $hours): array
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
