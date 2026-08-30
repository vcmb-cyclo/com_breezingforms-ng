<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ClassicGraphicButtonBuilder;

final class ClassicGraphicButtonBuilderTest extends TestCase
{
    public function testBuildsGraphicButtonWithImageAndEvents(): void
    {
        $html = (new ClassicGraphicButtonBuilder())->build(80, 'save', '/save.png', 'Save', 'position:absolute;', ' class="wrapper"', ' class="button"', 90, 30, false, ' onclick="save();"', 1);

        self::assertStringContainsString('<button id="ff_elem80" type="button" name="ff_nm_save" value="Save" onclick="save();" class="button">', $html);
        self::assertStringContainsString('src="/save.png"', $html);
        self::assertStringContainsString('alt=""', $html);
        self::assertStringContainsString('width="90" height="30"', $html);
        self::assertStringContainsString('Save', $html);
    }

    public function testBuildsAboveLayoutWithHistoricalLiteralAndDisabledState(): void
    {
        $html = (new ClassicGraphicButtonBuilder())->build(81, 'help', '/help.png', 'Help', '', '', '', 0, 0, true, '', 2);

        self::assertStringContainsString('disabled="disabled"', $html);
        self::assertStringContainsString('Help<br/>', $html);
        self::assertStringContainsString('</table>.nlc()', $html);
        self::assertStringContainsString('</button>', $html);
    }

    public function testBuildsLeftAndRightLayouts(): void
    {
        $builder = new ClassicGraphicButtonBuilder();

        self::assertStringContainsString('<td>Left</td>', $builder->build(82, 'left', 'x', 'Left', '', '', '', 0, 0, false, '', 3));
        self::assertStringContainsString('<td>Right</td>', $builder->build(83, 'right', 'x', 'Right', '', '', '', 0, 0, false, '', 9));
    }
}
