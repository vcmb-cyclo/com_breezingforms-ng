<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeNextPageValidationBuilder;

final class QuickModeNextPageValidationBuilderTest extends TestCase
{
    public function testBuildsNextPageValidationCallback(): void
    {
        $script = (new QuickModeNextPageValidationBuilder())->build("\n");

        self::assertStringContainsString('function bf_validate_nextpage(page)', $script);
        self::assertStringContainsString('error = ff_validation(ff_currentpage);', $script);
        self::assertStringContainsString('ladda_button.ladda("stop");', $script);
        self::assertStringContainsString('JQuery("#bfPage"+(page)).ScrollTo({offsetTop: 50});', $script);
        self::assertStringContainsString('ff_initialize("pageentry");', $script);
        self::assertStringContainsString('css("opacity","1")', $script);
    }
}
