<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ClassicSelectBuilder;

final class ClassicSelectBuilderTest extends TestCase
{
    public function testBuildsSelectedMultipleListWithEscapedOptionsAndDimensions(): void
    {
        $html = (new ClassicSelectBuilder())->build(60, 'country', 'position:absolute;', ' class="wrapper"', ' class="control"', '3', "1;France;FR\n0;Germany;DE\nIgnored", 120, 40, true, false, ' onchange="changed();"');

        self::assertStringContainsString('multiple="multiple"', $html);
        self::assertStringContainsString('size="3"', $html);
        self::assertStringContainsString('style="width:120px;height:40px;"', $html);
        self::assertStringContainsString('value="FR" selected="selected">France</option>', $html);
        self::assertStringContainsString('value="DE">Germany</option>', $html);
        self::assertStringContainsString('value="Ignored">Ignored</option>', $html);
        self::assertStringContainsString('onchange="changed();"', $html);
    }

    public function testBuildsDisabledListAndSupportsEmptyQuotedValue(): void
    {
        $html = (new ClassicSelectBuilder())->build(61, 'choice', '', '', '', '', "1;Label;''\n0;A & B;X&Y", 0, 0, false, true, '');

        self::assertStringContainsString('disabled="disabled"', $html);
        self::assertStringContainsString('<option value="" selected="selected">Label</option>', $html);
        self::assertStringContainsString('value="X&amp;Y">A &amp; B</option>', $html);
    }
}
