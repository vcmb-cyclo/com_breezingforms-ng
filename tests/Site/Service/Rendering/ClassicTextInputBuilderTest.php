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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ClassicTextInputBuilder;

final class ClassicTextInputBuilderTest extends TestCase
{
    public function testBuildsTextInputWithSizeAndReadonlyState(): void
    {
        self::assertSame(
            "\t<div id=\"ff_div30\" style=\"position:absolute;\" class=\"field\">\n\t\t<input id=\"ff_elem30\" size=\"20\" maxlength=\"80\" type=\"text\" readonly=\"readonly\" name=\"ff_nm_title[]\" value=\"Hello\" class=\"input\"/>\n\t</div>\n",
            (new ClassicTextInputBuilder())->build(30, 'title', 'Hello', 'position:absolute;', ' class="field"', ' class="input"', 20, 0, 80, false, 2, '')
        );
    }

    public function testBuildsPasswordWithResponsiveWidthDisabledStateAndEvents(): void
    {
        $html = (new ClassicTextInputBuilder())->build(31, 'secret', 'x', '', '', '', 50, 1, 0, true, 1, ' onclick="go();"');

        self::assertStringContainsString('style="width:50px;"', $html);
        self::assertStringContainsString('type="password"', $html);
        self::assertStringContainsString('disabled="disabled"', $html);
        self::assertStringContainsString('onclick="go();"', $html);
        self::assertStringContainsString('name="ff_nm_secret[]"', $html);
    }
}
