<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering\QuickMode;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeUploadProgressScriptBuilder;

final class QuickModeUploadProgressScriptBuilderTest extends TestCase
{
    public function testBuildsProgressUpdateCallback(): void
    {
        $script = QuickModeUploadProgressScriptBuilder::build("\n");

        self::assertStringContainsString("uploader.bind('UploadProgress', function(up, file) {", $script);
        self::assertStringContainsString("file.percent + '% <div style=\"height: 5px;width: '", $script);
        self::assertStringContainsString("getElementsByTagName('b')[0].innerHTML", $script);
        self::assertStringEndsWith("                                                                });", $script);
    }
}
