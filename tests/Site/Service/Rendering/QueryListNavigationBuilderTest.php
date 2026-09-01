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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QueryListNavigationBuilder;

final class QueryListNavigationBuilderTest extends TestCase
{
    public function testBuildsNavigationForFirstPreviousNextAndLastPages(): void
    {
        $script = (new QueryListNavigationBuilder())->build([
            'start' => 'Start',
            'previous' => 'Previous',
            'next' => 'Next',
            'end' => 'End',
        ], "\n");

        self::assertStringContainsString('ff_dispQueryPage(\'+id+\',1)', $script);
        self::assertStringContainsString('Start', $script);
        self::assertStringContainsString('Previous', $script);
        self::assertStringContainsString('Next', $script);
        self::assertStringContainsString('End', $script);
        self::assertStringContainsString("navi += '<\/a>';", $script);
        self::assertStringEndsWith('        } // if', $script);
    }
}
