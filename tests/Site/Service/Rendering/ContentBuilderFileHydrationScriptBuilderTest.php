<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ContentBuilderFileHydrationScriptBuilder;

final class ContentBuilderFileHydrationScriptBuilderTest extends TestCase
{
    public function testBuildRestoresUploadControlsForEmptyAndExistingQueues(): void
    {
        $script = (new ContentBuilderFileHydrationScriptBuilder())->build(27, '<div>file.pdf</div>');

        self::assertStringContainsString('bfFlashFileQueue27', $script);
        self::assertStringContainsString('#ff_elem27_files', $script);
        self::assertStringContainsString('mydiv.innerHTML = "<br/><div>file.pdf</div>";', $script);
        self::assertStringContainsString('JQuery("#bfFlashFileQueue27").after(mydiv);', $script);
    }
}
