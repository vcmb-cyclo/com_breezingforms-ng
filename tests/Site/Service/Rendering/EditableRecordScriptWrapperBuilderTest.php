<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\EditableRecordScriptWrapperBuilder;

final class EditableRecordScriptWrapperBuilderTest extends TestCase
{
    public function testWrapsHydrationAndClearsLegacySecurityCode(): void
    {
        $script = (new EditableRecordScriptWrapperBuilder())->build(27, 'hydrate();', "\n");

        self::assertStringContainsString('function bfLoadEditable(){', $script);
        self::assertStringContainsString('hydrate();', $script);
        self::assertStringContainsString('document.ff_form27.elements.length', $script);
        self::assertStringContainsString('name == "ff_nm_seccode[]"', $script);
        self::assertStringContainsString('value = "";', $script);
        self::assertStringEndsWith("\n", $script);
    }
}
