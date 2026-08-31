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
