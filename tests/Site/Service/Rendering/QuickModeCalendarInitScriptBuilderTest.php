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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeCalendarInitScriptBuilder;

final class QuickModeCalendarInitScriptBuilderTest extends TestCase
{
    public function testBuildsResponsiveInitializationScript(): void
    {
        self::assertSame(
            '<script type="text/javascript">bfInitCalendarResponsive(23, {"format":"yyyy-mm-dd","selectYears":60,"firstDay":1,"hasYearScroller":true});</script>' . "\n",
            (new QuickModeCalendarInitScriptBuilder())->buildResponsive(23, 'yyyy-mm-dd', 60, 1, true)
        );
    }

    public function testBuildsMobileInitializationDeclaration(): void
    {
        self::assertSame(
            'bfInitMobileCalendar(24, "Open calendar");',
            (new QuickModeCalendarInitScriptBuilder())->buildMobile(24, 'Open calendar')
        );
    }
}
