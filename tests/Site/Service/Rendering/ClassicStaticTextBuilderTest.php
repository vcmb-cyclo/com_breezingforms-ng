<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ClassicStaticTextBuilder;

final class ClassicStaticTextBuilderTest extends TestCase
{
    public function testBuildsImageWithDimensionsAndClasses(): void
    {
        self::assertSame(
            "\t<div id=\"ff_div10\" style=\"position:absolute;\" class=\"wrapper\">\n\t\t<img id=\"ff_elem10\" src=\"/image.png\"  alt=\"An image\" border=\"0\" width=\"120\" height=\"40\"  class=\"image\"/>\n\t</div>\n",
            (new ClassicStaticTextBuilder())->buildImage(
                10,
                'position:absolute;',
                ' class="wrapper"',
                ' class="image"',
                '/image.png',
                'An image',
                120,
                40
            )
        );
    }

    public function testBuildsImageWithoutOptionalDimensions(): void
    {
        self::assertSame(
            "  <div id=\"ff_div11\" style=\"\">\r\n    <img id=\"ff_elem11\" src=\"x\"  alt=\"\" border=\"0\"  class=\"img\"/>\r\n  </div>\r\n",
            (new ClassicStaticTextBuilder())->buildImage(11, '', '', ' class="img"', 'x', '', 0, 0, '  ', "\r\n")
        );
    }

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
