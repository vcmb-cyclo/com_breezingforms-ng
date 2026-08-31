<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeErrorRuntimeConfigBuilder;

final class QuickModeErrorRuntimeConfigBuilderTest extends TestCase
{
    public function testBuildsDefaultErrorConfiguration(): void
    {
        self::assertSame(
            "var bfUseErrorAlerts = false;\n"
            . "var bfShowDefaultErrors = true;\n"
            . "var bfErrorPageScoped = false;\n",
            QuickModeErrorRuntimeConfigBuilder::build(true, false, "\n")
        );
    }

    public function testBuildsPageScopedConfiguration(): void
    {
        self::assertStringContainsString(
            'var bfErrorPageScoped = true;',
            QuickModeErrorRuntimeConfigBuilder::build(false, true, "\n")
        );
    }
}
