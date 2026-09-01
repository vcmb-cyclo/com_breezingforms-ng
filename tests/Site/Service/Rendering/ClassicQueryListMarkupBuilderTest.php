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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ClassicQueryListMarkupBuilder;

final class ClassicQueryListMarkupBuilderTest extends TestCase
{
    public function testBuildsQueryListWrapperAndTable(): void
    {
        $html = (new ClassicQueryListMarkupBuilder())->open(
            80,
            'position:absolute;',
            ' class="wrapper"',
            ' style="width:100%"',
            ' class="table"'
        );

        self::assertSame(
            "\t<div id=\"ff_div80\" style=\"position:absolute;\" class=\"wrapper\">\n"
            . "\t\t<table id=\"ff_elem80\" style=\"width:100%\" class=\"table\">\n",
            $html
        );
    }

    public function testBuildsClosingMarkupWithCustomFormatting(): void
    {
        $html = (new ClassicQueryListMarkupBuilder())->close('  ', ' ', "\r\n", "\r\n");

        self::assertSame("  </table>\r\n </div>\r\n", $html);
    }
}
