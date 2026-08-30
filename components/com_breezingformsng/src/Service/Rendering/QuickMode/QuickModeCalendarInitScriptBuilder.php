<?php

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

final class QuickModeCalendarInitScriptBuilder
{
    public function buildResponsive(
        int $elementId,
        string $format,
        int $selectYears,
        int $firstDay,
        bool $hasYearScroller
    ): string {
        return '<script type="text/javascript">bfInitCalendarResponsive(' . json_encode($elementId) . ', '
            . json_encode([
                'format' => $format,
                'selectYears' => $selectYears,
                'firstDay' => $firstDay,
                'hasYearScroller' => $hasYearScroller,
            ]) . ');</script>' . "\n";
    }

    public function buildMobile(int $elementId, string $openLabel): string
    {
        return 'bfInitMobileCalendar(' . json_encode($elementId) . ', ' . json_encode($openLabel) . ');';
    }
}
