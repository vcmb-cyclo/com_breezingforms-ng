<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ContentBuilderReadonlyScriptBuilder;

final class ContentBuilderReadonlyScriptBuilderTest extends TestCase
{
    public function testNoNonEditableFieldsProducesNoScript(): void
    {
        self::assertSame('', (new ContentBuilderReadonlyScriptBuilder())->build([]));
    }

    public function testNonEditableFieldsProduceVisibleControlAndDisableLogic(): void
    {
        $script = (new ContentBuilderReadonlyScriptBuilder())->build([21, 22]);

        self::assertStringContainsString('function bfContentBuilderFieldHasVisibleControl(fieldId){', $script);
        self::assertStringContainsString('function bfDisableContentBuilderFields(){', $script);
        self::assertStringContainsString('document.getElementById("ff_elem21")', $script);
        self::assertStringContainsString('document.getElementById("ff_elem22")', $script);
        self::assertSame(4, substr_count($script, 'bfDeactivateField[bfCbName]=true;'));
        self::assertStringContainsString('bfCbElements[i].disabled = true;', $script);
        self::assertStringContainsString('bfContentBuilderFieldHasVisibleControl("21")', $script);
        self::assertStringContainsString('bfContentBuilderFieldHasVisibleControl("22")', $script);
    }
}
