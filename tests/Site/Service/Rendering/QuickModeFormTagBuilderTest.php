<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickModeFormTagBuilder;

final class QuickModeFormTagBuilderTest extends TestCase
{
    public function testBuildsFormTagWithoutCustomClass(): void
    {
        self::assertSame(
            '<form data-ajax="false" action="/submit" method="post" name="ff1" id="ff1" enctype="multipart/form-data" accept-charset="utf-8" onsubmit="return false;" class="bfQuickMode">' . "\n",
            (new QuickModeFormTagBuilder())->build('/submit', 'ff1')
        );
    }

    public function testPreservesHistoricalCustomClassAttribute(): void
    {
        self::assertStringContainsString(
            'enctype="multipart/form-data" class="custom" accept-charset=',
            (new QuickModeFormTagBuilder())->build('/submit', 'ff2', 'custom', "\r\n")
        );
    }
}
