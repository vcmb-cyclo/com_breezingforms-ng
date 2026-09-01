<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeUploadEntryCallbacksBuilder;

final class QuickModeUploadEntryCallbacksBuilderTest extends TestCase
{
    public function testComposesQueueAndFilesAddedCallbacks(): void
    {
        $script = QuickModeUploadEntryCallbacksBuilder::build(
            12,
            '/cancel.png',
            false,
            false,
            'false',
            1024,
            'jpg,png',
            ' too large',
            ' invalid',
            "\n"
        );

        self::assertStringContainsString("uploader.bind('FilesAdded'", $script);
        self::assertStringContainsString('bfFlashFileQueue12', $script);
        self::assertSame(1, substr_count($script, "uploader.bind('FilesAdded'"));
    }

    public function testBootstrapOptionIsForwardedToBothCallbacks(): void
    {
        $script = QuickModeUploadEntryCallbacksBuilder::build(
            12,
            '/cancel.png',
            true,
            true,
            'true',
            1024,
            'jpg',
            ' too large',
            ' invalid'
        );

        self::assertStringContainsString("JQuery('#bfPickFiles12').prop('disabled',false)", $script);
        self::assertStringContainsString("? files[i].name.replace(/[/\\?%*:|\"<>]/g, '') : ''", $script);
    }
}
