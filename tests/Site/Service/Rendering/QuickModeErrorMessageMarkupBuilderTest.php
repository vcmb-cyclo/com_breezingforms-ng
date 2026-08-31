<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeErrorMessageMarkupBuilder;

final class QuickModeErrorMessageMarkupBuilderTest extends TestCase
{
    public function testBuildsHiddenBootstrapErrorMessageContainer(): void
    {
        self::assertSame(
            '<div class="bfErrorMessage alert alert-error" style="display:none"></div>' . "\n",
            QuickModeErrorMessageMarkupBuilder::build('alert', 'alert-error', "\n")
        );
    }
}
