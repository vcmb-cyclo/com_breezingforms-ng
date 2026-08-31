<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

/** Shared calendar option adapters used by all QuickMode renderers. */
trait CalendarOptionsTrait
{
    private function bfCalendarIsTruthy(array $mdata, string $key): bool
    {
        return (new QuickModeCalendarOptionsBuilder())->isTruthy($mdata, $key);
    }

    private function bfCalendarShowTimeEnabled(array $mdata): bool
    {
        return (new QuickModeCalendarOptionsBuilder())->showTimeEnabled($mdata);
    }

    private function bfCalendarToPickadateFormat(mixed $format): string
    {
        return (new QuickModeCalendarOptionsBuilder())->toPickadateFormat($format);
    }

    private function bfCalendarToPickadateFirstDay(mixed $firstDay): int
    {
        return (new QuickModeCalendarOptionsBuilder())->toPickadateFirstDay($firstDay);
    }

    private function bfCalendarSelectYears(array $mdata): int
    {
        return (new QuickModeCalendarOptionsBuilder())->selectYears($mdata);
    }
}
