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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeGroupOptionBuilder;

final class QuickModeGroupOptionBuilderTest extends TestCase
{
    public function testBuildsCheckedRadioOption(): void
    {
        self::assertSame(
            '<input checked="checked"  class="ff_elem" tabindex="3" type="radio" name="ff_nm_color[]" value="red" id="ff_elem17"/>',
            (new QuickModeGroupOptionBuilder())->build('radio', 'ff_elem', 'color', 'red', '17', true, 'tabindex="3" ')
        );
    }

    public function testEscapesUncheckedCheckboxValue(): void
    {
        self::assertSame(
            '<input  class="ff_elem form-check-input" type="checkbox" name="ff_nm_terms[]" value="A &amp; B" id="ff_elem18"/>',
            (new QuickModeGroupOptionBuilder())->build('checkbox', 'ff_elem form-check-input', 'terms', 'A & B', '18', false)
        );
    }

    public function testEscapesClassTypeFieldNameAndElementId(): void
    {
        $html = (new QuickModeGroupOptionBuilder())->build(
            'radio" onfocus="alert(1)',
            'ff_elem" onfocus="alert(2)',
            'field" onfocus="alert(3)',
            'value',
            '18" onfocus="alert(4)',
            false
        );

        self::assertStringContainsString('type="radio&quot; onfocus=&quot;alert(1)', $html);
        self::assertStringContainsString('ff_elem&quot; onfocus=&quot;alert(2)', $html);
        self::assertStringContainsString('ff_nm_field&quot; onfocus=&quot;alert(3)[]', $html);
        self::assertStringContainsString('ff_elem18&quot; onfocus=&quot;alert(4)', $html);
    }
}
