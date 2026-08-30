<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ClassicStaticTextBuilder;

final class ClassicStaticTextBuilderTest extends TestCase
{
    public function testBuildsRectangleWithOptionalBorderAndBackground(): void
    {
        self::assertSame(
            "\t<div id=\"ff_div8\" style=\"font-size:0px;position:absolute;border:1px solid red;background-color:#fff;\" class=\"box\"></div>\n",
            (new ClassicStaticTextBuilder())->buildRectangle(
                8,
                'position:absolute;',
                ' class="box"',
                '1px solid red',
                '#fff'
            )
        );
    }

    public function testBuildsRectangleWithoutOptionalStyles(): void
    {
        self::assertSame(
            "  <div id=\"ff_div9\" style=\"font-size:0px;\"></div>\r\n",
            (new ClassicStaticTextBuilder())->buildRectangle(9, '', '', '', '', '  ', "\r\n")
        );
    }

    public function testBuildsStaticHtmlWithClassicElementContract(): void
    {
        self::assertSame(
            "\t<div id=\"ff_div31\" style=\"position:absolute;left:4px;\" class=\"intro\"><p>content</p></div>\n",
            (new ClassicStaticTextBuilder())->build(
                31,
                'position:absolute;left:4px;',
                ' class="intro"',
                '<p>content</p>'
            )
        );
    }

    public function testBuildPreservesEmptyClassAndRawHtml(): void
    {
        self::assertSame(
            "  <div id=\"ff_div7\" style=\"\"><strong>raw</strong></div>\r\n",
            (new ClassicStaticTextBuilder())->build(7, '', '', '<strong>raw</strong>', '  ', "\r\n")
        );
    }
}
