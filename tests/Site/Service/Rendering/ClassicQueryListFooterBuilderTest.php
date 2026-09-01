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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ClassicQueryListFooterBuilder;

final class ClassicQueryListFooterBuilderTest extends TestCase
{
    public function testBuildsFullPaginationFooter(): void
    {
        $html = (new ClassicQueryListFooterBuilder())->build(70, 3, 3, 1, ' class="footer"', ' class="cell"', 'Start', 'Previous', 'Next', 'End');

        self::assertStringContainsString('<tr class="footer">', $html);
        self::assertStringContainsString('<td colspan="3" class="cell">', $html);
        self::assertStringContainsString('ff_dispQueryPage(70,2)', $html);
        self::assertStringContainsString('ff_dispQueryPage(70,3)', $html);
        self::assertStringContainsString('1 ', $html);
        self::assertStringContainsString('>2</a>', $html);
        self::assertStringContainsString('>3</a>', $html);
    }

    public function testBuildsFooterWithoutNavigationAndSupportsCompactMode(): void
    {
        $builder = new ClassicQueryListFooterBuilder();
        $html = $builder->build(71, 1, 1, 1, '', '', 'Start', 'Previous', 'Next', 'End', '  ', '    ', '      ', '', "\r\n");
        $compact = $builder->build(72, 1, 2, 2, '', '', 'Start', 'Previous', 'Next', 'End', '', '', '', '', "\r\n");

        self::assertStringNotContainsString('ff_dispQueryPage', $html);
        self::assertStringContainsString("  </tr>\r\n", $html);
        self::assertStringContainsString('Start', $compact);
        self::assertStringContainsString('Next', $compact);
    }
}
