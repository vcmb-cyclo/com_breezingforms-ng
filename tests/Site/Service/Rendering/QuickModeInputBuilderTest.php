<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeInputBuilder;

final class QuickModeInputBuilderTest extends TestCase
{
    public function testBuildEscapesValueAndPlaceholderAndKeepsAttributesOrder(): void
    {
        $html = (new QuickModeInputBuilder())->build(
            'ff_elem',
            'text',
            'name',
            '  A&B "quoted"  ',
            12,
            'style="width:20em" tabindex="1" ',
            'Enter a value'
        );

        self::assertSame(
            '<input placeholder="Enter a value" class="ff_elem" style="width:20em" tabindex="1" '
            . 'type="text" name="ff_nm_name[]" value="A&amp;B &quot;quoted&quot;" id="ff_elem12"/>' . "\n",
            $html
        );
    }

    public function testBuildSupportsTypeSpecificSuffixAttributes(): void
    {
        $html = (new QuickModeInputBuilder())->build(
            'ff_elem inputbox',
            'range',
            'age',
            '10',
            13,
            '',
            '',
            ' step="1" max="120" min="0"'
        );

        self::assertStringContainsString('type="range"', $html);
        self::assertStringContainsString('id="ff_elem13" step="1" max="120" min="0"', $html);
    }
}
