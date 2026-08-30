<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ClassicStaticTextBuilder;

final class ClassicStaticTextBuilderTest extends TestCase
{
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
