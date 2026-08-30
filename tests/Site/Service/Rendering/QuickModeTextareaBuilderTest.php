<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeTextareaBuilder;

final class QuickModeTextareaBuilderTest extends TestCase
{
    public function testBuildEscapesPlainTextareaContent(): void
    {
        $html = (new QuickModeTextareaBuilder())->build(
            'ff_elem',
            'message',
            "  <b>A & B</b>  ",
            21,
            'cols="20" rows="5" ',
            'Write here'
        );

        self::assertSame(
            '<textarea placeholder="Write here" class="ff_elem" cols="20" rows="5" '
            . 'name="ff_nm_message[]" id="ff_elem21">&lt;b&gt;A &amp; B&lt;/b&gt;</textarea>' . "\n",
            $html
        );
    }
}
