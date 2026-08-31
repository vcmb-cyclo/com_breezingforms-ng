<?php

/**
 * BreezingForms NG - A Joomla Forms Application
 *
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 **/

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
