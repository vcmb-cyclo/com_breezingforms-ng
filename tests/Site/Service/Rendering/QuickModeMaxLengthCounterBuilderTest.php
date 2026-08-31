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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeMaxLengthCounterBuilder;

final class QuickModeMaxLengthCounterBuilderTest extends TestCase
{
    public function testBuildsLegacyCounterMarkup(): void
    {
        self::assertSame(
            ' <span class=***bfMaxLengthCounter*** id=***bfMaxLengthCounter16***>(120 characters left)</span>',
            (new QuickModeMaxLengthCounterBuilder())->build(16, 120, 'characters left')
        );
    }
}
