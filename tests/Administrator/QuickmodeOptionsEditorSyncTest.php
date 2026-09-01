<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Administrator;

use PHPUnit\Framework\TestCase;

final class QuickmodeOptionsEditorSyncTest extends TestCase
{
    public function testEditorValuesAreSynchronizedBeforeOptionsFieldsAreSubmitted(): void
    {
        $source = file_get_contents(
            __DIR__ . '/../../media/com_breezingformsng/js/admin/quickmode-app.js'
        );

        self::assertIsString($source);

        $syncCall = strpos($source, 'syncOptionsEditors($wrap);');
        $moveFields = strpos($source, 'JQuery(tempForm).append($wrap.children());');

        self::assertIsInt($syncCall);
        self::assertIsInt($moveFields);
        self::assertLessThan($moveFields, $syncCall);
        self::assertStringContainsString("\$wrap.find('textarea').each", $source);
        self::assertStringContainsString("var editor = JoomlaEditor.get(field.id);", $source);
        self::assertStringContainsString("editor = JoomlaEditor.get(field.name);", $source);
        self::assertStringContainsString('field.value = editor.getValue();', $source);
        self::assertStringContainsString("window.dispatchEvent(new CustomEvent('bfqm:ready'));", $source);
    }
}
