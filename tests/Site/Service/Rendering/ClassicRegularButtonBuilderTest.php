<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ClassicRegularButtonBuilder;

final class ClassicRegularButtonBuilderTest extends TestCase
{
    public function testBuildsEnabledRegularButtonWithEvents(): void
    {
        self::assertSame(
            "\t<div id=\"ff_div70\" style=\"position:absolute;\" class=\"wrapper\">\n\t\t<input id=\"ff_elem70\" type=\"button\" name=\"ff_nm_send\" value=\"Send\" onclick=\"send();\" class=\"button\"/>\n\t</div>\n",
            (new ClassicRegularButtonBuilder())->build(70, 'send', 'Send', 'position:absolute;', ' class="wrapper"', ' class="button"', false, ' onclick="send();"')
        );
    }

    public function testBuildsDisabledRegularButtonWithCustomFormatting(): void
    {
        $html = (new ClassicRegularButtonBuilder())->build(71, 'cancel', 'Cancel', '', '', '', true, '', '  ', "\r\n");

        self::assertStringContainsString('type="button"', $html);
        self::assertStringContainsString('disabled="disabled"', $html);
        self::assertStringContainsString('name="ff_nm_cancel"', $html);
        self::assertStringContainsString('value="Cancel"', $html);
    }
}
