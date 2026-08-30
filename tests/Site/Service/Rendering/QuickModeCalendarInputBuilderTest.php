<?php

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering\QuickMode;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeCalendarInputBuilder;

final class QuickModeCalendarInputBuilderTest extends TestCase
{
    public function testBuildsStyledCalendarInput(): void
    {
        self::assertSame(
            '<input autocomplete="off" class="form-control ff_elem" style="width:65%;" type="text" name="ff_nm_date[]"  id="ff_elem22" value="&lt;"/>' . "\n",
            (new QuickModeCalendarInputBuilder())->build('form-control ff_elem', 'date', 22, '<', 'style="width:65%;" ')
        );
    }
}
