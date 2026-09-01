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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ClassicTextareaBuilder;

final class ClassicTextareaBuilderTest extends TestCase
{
    public function testBuildsTextareaWithColsRowsAndReadonlyState(): void
    {
        self::assertSame(
            "\t<div id=\"ff_div40\" style=\"position:absolute;\" class=\"wrapper\">\n\t\t<textarea id=\"ff_elem40\" name=\"ff_nm_notes[]\" readonly=\"readonly\" cols=\"30\" rows=\"4\" class=\"control\">Text</textarea>\n\t</div>\n",
            (new ClassicTextareaBuilder())->build(40, 'notes', 'Text', 'position:absolute;', ' class="wrapper"', ' class="control"', 30, 0, 5, 0, true, 2, '')
        );
    }

    public function testBuildsResponsiveTextareaAndAdjustsMozillaRows(): void
    {
        $html = (new ClassicTextareaBuilder())->build(41, 'body', 'Value', '', '', '', 60, 1, 20, 1, false, 0, ' onchange="changed();"');

        self::assertStringContainsString('style="width:60px;height:20px;"', $html);
        self::assertStringContainsString('onchange="changed();"', $html);
        self::assertStringContainsString('>Value</textarea>', $html);

        $mozilla = (new ClassicTextareaBuilder())->build(42, 'body', '', '', '', '', 0, 0, 5, 0, true, 0, '');
        self::assertStringContainsString('rows="4"', $mozilla);
    }
}
