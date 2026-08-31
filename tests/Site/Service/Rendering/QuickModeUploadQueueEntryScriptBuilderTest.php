<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering\QuickMode;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeUploadQueueEntryScriptBuilder;

final class QuickModeUploadQueueEntryScriptBuilderTest extends TestCase
{
    public function testBuildsQueueEntryAndOptionalCallback(): void
    {
        $script = QuickModeUploadQueueEntryScriptBuilder::build("\n");

        self::assertStringContainsString("uploader.bind('FilesAdded', function(up, files) {", $script);
        self::assertStringContainsString("bfUploadFileAdded(files[i]);", $script);
        self::assertStringContainsString("JQuery('#bfFileQueue').append(", $script);
        self::assertStringContainsString("files[i].name.replace(/[/\\?%*:|\"<>]/g, '')", $script);
    }

    public function testCanPreserveRendererSpecificBlankLine(): void
    {
        $script = QuickModeUploadQueueEntryScriptBuilder::build("\n", true);

        self::assertStringContainsString("function(up, files) {\n\n", $script);
    }
}
