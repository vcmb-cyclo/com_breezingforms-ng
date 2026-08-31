<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering\QuickMode;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeUploadValidationScriptBuilder;

final class QuickModeUploadValidationScriptBuilderTest extends TestCase
{
    public function testBuildsSizeAndExtensionValidation(): void
    {
        $script = QuickModeUploadValidationScriptBuilder::build(2048, 'jpg,png', '"Too large"', '"Bad type"', "\n");

        self::assertStringContainsString('var thebytes = 2048;', $script);
        self::assertStringContainsString("var exts = 'jpg,png'.split(',');", $script);
        self::assertStringContainsString('alert("Too large");', $script);
        self::assertStringContainsString('alert("Bad type");', $script);
        self::assertStringContainsString("JQuery('#'+files[i].id+'queueitem').remove();", $script);
        self::assertStringContainsString('bfFlashUploadersLength++;', $script);
    }
}
