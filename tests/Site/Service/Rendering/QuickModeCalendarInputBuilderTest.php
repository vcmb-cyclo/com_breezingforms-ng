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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeCalendarInputBuilder;

final class QuickModeCalendarInputBuilderTest extends TestCase
{
    public function testBuildsStyledCalendarInput(): void
    {
        self::assertSame(
            '<input autocomplete="off" class="form-control ff_elem" style="width:65%;" type="text" name="ff_nm_date[]"  id="ff_elem22" value="&lt;"/>' . "\n",
            (new QuickModeCalendarInputBuilder())->build('form-control ff_elem', 'date', 22, '<', 'style="width:65%;" ')
        );
    }
}
