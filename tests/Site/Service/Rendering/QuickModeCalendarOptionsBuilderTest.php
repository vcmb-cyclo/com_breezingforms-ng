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

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeCalendarOptionsBuilder;

final class QuickModeCalendarOptionsBuilderTest extends TestCase
{
    public function testNormalizesCalendarOptions(): void
    {
        $builder = new QuickModeCalendarOptionsBuilder();

        self::assertTrue($builder->isTruthy(['enabled' => 'yes'], 'enabled'));
        self::assertFalse($builder->isTruthy(['enabled' => '0'], 'enabled'));
        self::assertTrue($builder->showTimeEnabled(['showTime' => 1]));
        self::assertSame('yyyy-mm-dd', $builder->toPickadateFormat(''));
        self::assertSame('yyyy-mm-dd', $builder->toPickadateFormat('%Y-%m-%d %H:%M'));
        self::assertSame(0, $builder->toPickadateFirstDay(7));
        self::assertSame(1, $builder->toPickadateFirstDay(0));
        self::assertSame(60, $builder->selectYears([]));
        self::assertSame(11, $builder->selectYears(['minYear' => 5, 'maxYear' => 5]));
    }
}
