<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ContentBuilderSignatureScriptBuilder;

final class ContentBuilderSignatureScriptBuilderTest extends TestCase
{
    public function testBuildRestoresSignatureForNamedPad(): void
    {
        $script = (new ContentBuilderSignatureScriptBuilder())->build('signature', 18, 'encoded-image');

        self::assertStringContainsString('bf_signaturePad18', $script);
        self::assertStringContainsString('ff_nm_signature[]', $script);
        self::assertStringContainsString('data:image\\/png;base64,encoded-image', $script);
        self::assertStringContainsString('.fromDataURL(', $script);
    }

    public function testBuildPreservesEmptyEncodedImageGuard(): void
    {
        $script = (new ContentBuilderSignatureScriptBuilder())->build('signature', 18, '');

        self::assertStringContainsString('if(false)', $script);
        self::assertStringNotContainsString('.fromDataURL("data:image/png;base64,")', $script);
    }
}
