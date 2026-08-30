<?php

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering\QuickMode;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeSelectBuilder;

final class QuickModeSelectBuilderTest extends TestCase
{
    public function testBuildsSelectedEscapedOptionsAndAttributes(): void
    {
        $builder = new QuickModeSelectBuilder();

        self::assertSame(
            '<select data-chosen="no-chzn" class="ff_elem form-select" style="width:10px;" multiple="multiple" '
            . 'tabindex="1" name="ff_nm_choice[]" id="ff_elem14">' . "\n"
            . '<option selected="selected" value="a&amp;b">A &amp; B</option>' . "\n"
            . '</select>' . "\n",
            $builder->build('ff_elem form-select', 'choice', 14, "1;A & B;a&b\r\ninvalid", true, 'tabindex="1" ', 'style="width:10px;" ')
        );
    }

    public function testBuildsNonMultipleSelectWithoutOptions(): void
    {
        self::assertSame(
            '<select data-chosen="no-chzn" class="ff_elem" name="ff_nm_empty[]" id="ff_elem15">' . "\n"
            . '</select>' . "\n",
            (new QuickModeSelectBuilder())->build('ff_elem', 'empty', 15, '', false)
        );
    }
}
