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

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering\QuickMode;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeCalendarButtonBuilder;

final class QuickModeCalendarButtonBuilderTest extends TestCase
{
    public function testBuildsCalendarButtonAndEscapesValue(): void
    {
        self::assertSame(
            '<button type="button" id="ff_elem21_calendarButton" class="bfCalendar btn" value="&lt;">'
            . '<span>&lt;</span></button>' . "\n",
            (new QuickModeCalendarButtonBuilder())->build(
                'type="button"', 'ff_elem21_calendarButton', 'bfCalendar btn', '<', '<span>&lt;</span>'
            )
        );
    }
}
