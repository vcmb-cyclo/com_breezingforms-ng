<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ClassicChoiceBuilder;

final class ClassicChoiceBuilderTest extends TestCase
{
    public function testBuildsCheckedDisabledCheckboxWithLabelAndEvents(): void
    {
        self::assertSame(
            "\t<div id=\"ff_div50\" style=\"position:absolute;\" class=\"choice\">\n\t\t<input id=\"ff_elem50\" type=\"checkbox\" name=\"ff_nm_terms[]\" value=\"yes\" checked=\"checked\" disabled=\"disabled\" onclick=\"toggle();\" class=\"control\"/><label id=\"ff_lbl50\" for=\"ff_elem50\"> Terms</label>\n\t</div>\n",
            (new ClassicChoiceBuilder())->build('checkbox', 50, 'terms', 'yes', 'Terms', 'position:absolute;', ' class="choice"', ' class="control"', true, true, ' onclick="toggle();"')
        );
    }

    public function testBuildsUncheckedRadioWithCustomFormatting(): void
    {
        $html = (new ClassicChoiceBuilder())->build('radio', 51, 'size', 'L', 'Large', '', '', '', false, false, '', '  ', "\r\n");

        self::assertStringContainsString('type="radio"', $html);
        self::assertStringContainsString('name="ff_nm_size[]"', $html);
        self::assertStringNotContainsString('checked="checked"', $html);
        self::assertStringNotContainsString('disabled="disabled"', $html);
        self::assertStringContainsString("\r\n", $html);
    }
}
