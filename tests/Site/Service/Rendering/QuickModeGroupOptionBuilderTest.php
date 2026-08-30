<?php

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering\QuickMode;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeGroupOptionBuilder;

final class QuickModeGroupOptionBuilderTest extends TestCase
{
    public function testBuildsCheckedRadioOption(): void
    {
        self::assertSame(
            '<input checked="checked"  class="ff_elem" tabindex="3" type="radio" name="ff_nm_color[]" value="red" id="ff_elem17"/>',
            (new QuickModeGroupOptionBuilder())->build('radio', 'ff_elem', 'color', 'red', '17', true, 'tabindex="3" ')
        );
    }

    public function testEscapesUncheckedCheckboxValue(): void
    {
        self::assertSame(
            '<input  class="ff_elem form-check-input" type="checkbox" name="ff_nm_terms[]" value="A &amp; B" id="ff_elem18"/>',
            (new QuickModeGroupOptionBuilder())->build('checkbox', 'ff_elem form-check-input', 'terms', 'A & B', '18', false)
        );
    }
}
