<?php

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering\QuickMode;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeCalendarButtonBuilder;

final class QuickModeCalendarButtonBuilderTest extends TestCase
{
    public function testBuildsCalendarButtonAndEscapesValue(): void
    {
        self::assertSame(
            '<button type="button" id="ff_elem21_calendarButton" class="bfCalendar btn" value="&lt;">'
            . '<span>&lt;</span></button>' . "\n",
            (new QuickModeCalendarButtonBuilder())->build(
                'type="button"', 'ff_elem21_calendarButton', 'bfCalendar btn', '<', '<span>&lt;</span>'
            )
        );
    }
}
