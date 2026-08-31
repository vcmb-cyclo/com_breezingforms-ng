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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeTextareaBuilder;

final class QuickModeTextareaBuilderTest extends TestCase
{
    public function testBuildEscapesPlainTextareaContent(): void
    {
        $html = (new QuickModeTextareaBuilder())->build(
            'ff_elem',
            'message',
            "  <b>A & B</b>  ",
            21,
            'cols="20" rows="5" ',
            'Write here'
        );

        self::assertSame(
            '<textarea placeholder="Write here" class="ff_elem" cols="20" rows="5" '
            . 'name="ff_nm_message[]" id="ff_elem21">&lt;b&gt;A &amp; B&lt;/b&gt;</textarea>' . "\n",
            $html
        );
    }

    public function testEscapesClassAndFieldName(): void
    {
        $html = (new QuickModeTextareaBuilder())->build(
            'ff_elem" onfocus="alert(1)',
            'message" onfocus="alert(2)',
            'value',
            21
        );

        self::assertStringContainsString('ff_elem&quot; onfocus=&quot;alert(1)', $html);
        self::assertStringContainsString('ff_nm_message&quot; onfocus=&quot;alert(2)[]', $html);
    }
}
