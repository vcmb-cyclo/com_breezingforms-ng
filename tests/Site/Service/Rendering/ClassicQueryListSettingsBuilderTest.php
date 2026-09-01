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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ClassicQueryListSettingsBuilder;

final class ClassicQueryListSettingsBuilderTest extends TestCase
{
    public function testBuildsTableAttributesClassesAndPagination(): void
    {
        $settings = (new ClassicQueryListSettingsBuilder())->build(
            "1\n2\n3\nhead\nodd\neven\nfoot\ncell\n4",
            100,
            static fn (string $class): string => 'resolved-' . $class
        );

        self::assertSame(' border="1" cellspacing="2" cellpadding="3" width="100%"', $settings['tableAttributes']);
        self::assertSame(' class="resolved-head"', $settings['headerClass']);
        self::assertSame(' class="resolved-odd"', $settings['oddClass']);
        self::assertSame(' class="resolved-even"', $settings['evenClass']);
        self::assertSame(' class="resolved-foot"', $settings['footerClass']);
        self::assertSame(' class="resolved-cell"', $settings['footerCellClass']);
        self::assertSame(4, $settings['pageNavigation']);
    }

    public function testUsesEmptyClassesAndDefaultPagination(): void
    {
        $settings = (new ClassicQueryListSettingsBuilder())->build('', 0, static fn (string $class): string => $class);

        self::assertSame('', $settings['tableAttributes']);
        self::assertSame('', $settings['headerClass']);
        self::assertSame('', $settings['footerCellClass']);
        self::assertSame(1, $settings['pageNavigation']);
    }
}
