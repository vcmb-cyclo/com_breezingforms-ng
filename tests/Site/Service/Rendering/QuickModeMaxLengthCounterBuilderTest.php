<?php

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering\QuickMode;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeMaxLengthCounterBuilder;

final class QuickModeMaxLengthCounterBuilderTest extends TestCase
{
    public function testBuildsLegacyCounterMarkup(): void
    {
        self::assertSame(
            ' <span class=***bfMaxLengthCounter*** id=***bfMaxLengthCounter16***>(120 characters left)</span>',
            (new QuickModeMaxLengthCounterBuilder())->build(16, 120, 'characters left')
        );
    }
}
