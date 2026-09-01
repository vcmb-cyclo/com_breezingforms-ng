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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ClassicStaticTextBuilder;

final class ClassicStaticTextBuilderTest extends TestCase
{
    public function testBuildsIconLayoutsAndHoverEvents(): void
    {
        $builder = new ClassicStaticTextBuilder();
        $html = $builder->buildIcon(
            20,
            'position:absolute;',
            ' class="icon-wrapper"',
            ' class="icon-image"',
            '/icon.png',
            'Label',
            '/hover.png',
            'onclick="runIcon();"',
            3,
            true,
            24,
            18
        );

        self::assertStringContainsString('padding:3px;position:absolute;', $html);
        self::assertStringContainsString('MM_swapImage(\'ff_img20\',\'\',\'/hover.png\',1);', $html);
        self::assertStringContainsString('onclick="runIcon();"', $html);
        self::assertStringContainsString('Label &nbsp;<img id="ff_img20"', $html);
        self::assertStringContainsString('width="24" height="18"', $html);
    }

    public function testBuildsIconAboveAndDefaultRightLayouts(): void
    {
        $builder = new ClassicStaticTextBuilder();
        $above = $builder->buildIcon(21, '', '', '', 'icon.png', 'Above', '', '', 2, false, 0, 0);
        $right = $builder->buildIcon(22, '', '', '', 'icon.png', 'Right', '', '', 9, false, 0, 0);

        self::assertStringContainsString('<table id="ff_elem21" cellpadding="2"', $above);
        self::assertStringContainsString('<tr><td style="text-align:center;">Above</td></tr>', $above);
        self::assertStringContainsString('<span id="ff_elem22"  style="vertical-align:middle;">', $right);
        self::assertStringContainsString('icon.png', $right);
        self::assertStringContainsString('&nbsp; Right', $right);
    }

    public function testBuildsTooltipWithDefaultImageAndEscapedTitle(): void
    {
        $html = (new ClassicStaticTextBuilder())->buildTooltip(
            12,
            'position:absolute;',
            ' class="tip"',
            ' class="icon"',
            '<b>Title</b>',
            "Line & details\nnext",
            '/custom.png',
            0,
            '/site'
        );

        self::assertStringContainsString('title="<strong>Title</strong><br />Line &amp; detailsnext"', $html);
        self::assertStringContainsString('class="hasTooltip tip"', $html);
        self::assertStringContainsString('/site/media/com_breezingformsng/images/site/tooltip.png', $html);
        self::assertStringContainsString('class="icon"', $html);
    }

    public function testBuildsTooltipWithWarningAndCustomImageVariants(): void
    {
        $builder = new ClassicStaticTextBuilder();

        self::assertStringContainsString(
            '/site/media/com_breezingformsng/images/site/warning.png',
            $builder->buildTooltip(13, '', '', '', 'Warning', 'Text', '/custom.png', 1, '/site')
        );
        self::assertStringContainsString(
            'src="/custom.png"',
            $builder->buildTooltip(14, '', '', '', 'Custom', 'Text', '/custom.png', 2, '/site')
        );
    }

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
