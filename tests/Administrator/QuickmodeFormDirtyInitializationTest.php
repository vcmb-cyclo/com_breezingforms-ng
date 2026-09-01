<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Administrator;

use PHPUnit\Framework\TestCase;

final class QuickmodeFormDirtyInitializationTest extends TestCase
{
    public function testDirtyTrackingWaitsForTheQuickModeApplicationBeforeCapturingTheBaseline(): void
    {
        $source = file_get_contents(
            __DIR__ . '/../../media/com_breezingformsng/js/admin/quickmode-form-dirty.js'
        );

        self::assertIsString($source);
        self::assertStringContainsString('var initialState = null;', $source);
        self::assertStringContainsString('if (!window.BFQMApp)', $source);
        self::assertStringContainsString("window.addEventListener('load', sync);", $source);
        self::assertStringNotContainsString('var initialState = formState();', $source);
    }
}
