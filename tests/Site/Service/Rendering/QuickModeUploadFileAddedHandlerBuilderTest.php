<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeUploadFileAddedHandlerBuilder;

final class QuickModeUploadFileAddedHandlerBuilderTest extends TestCase
{
    public function testBuildsClassicHandlerBody(): void
    {
        $script = QuickModeUploadFileAddedHandlerBuilder::build(
            12,
            '/images/cancel.png',
            false,
            'true',
            2048,
            'jpg,pdf',
            '"too large"',
            '"invalid extension"'
        );

        self::assertStringContainsString('for (var i in files)', $script);
        self::assertStringContainsString("id != 'undefined'", $script);
        self::assertStringContainsString('bfFileQueueItem', $script);
        self::assertStringContainsString('2048', $script);
        self::assertStringContainsString("'jpg,pdf'", $script);
        self::assertStringNotContainsString('class="row"', $script);
    }

    public function testBuildsBootstrapHandlerBodyWithThemeMarkup(): void
    {
        $script = QuickModeUploadFileAddedHandlerBuilder::build(
            13,
            '/images/cancel.png',
            true,
            'false',
            4096,
            'png',
            '"too large"',
            '"invalid extension"'
        );

        self::assertStringContainsString("files[i].name.replace(/[/\\?%*:|\"<>]/g, '') ?", $script);
        self::assertStringContainsString('4096', $script);
        self::assertStringContainsString("'png'", $script);
    }
}
