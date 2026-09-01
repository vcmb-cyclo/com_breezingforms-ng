<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering\QuickMode;

use PHPUnit\Framework\TestCase;

final class ClassicRendererEditorValueTest extends TestCase
{
    public function testClassicRendererUsesTheJoomla6EditorValueExpression(): void
    {
        $source = file_get_contents(
            __DIR__ . '/../../../../../components/com_breezingformsng/src/Service/Rendering/QuickMode/ClassicRenderer.php'
        );

        self::assertIsString($source);
        self::assertStringContainsString('QuickModeEditorValueBuilder::build($editor)', $source);
        self::assertStringContainsString(
            '$this->htmltextareasDbIds[] = \'ff_elem\' . $mdata[\'dbId\'];',
            $source
        );
        self::assertStringNotContainsString('->getContent(', $source);
        self::assertStringNotContainsString('(string) json_encode($editorContent)', $source);
    }
}
