<?php

declare(strict_types=1);

namespace BreezingFormsNG\Tests\Site\Service\Export;

use PHPUnit\Framework\TestCase;

final class ContentBuilderCleanupRegressionTest extends TestCase
{
    public function testEditableOverrideRemovesContentBuilderTrackingBeforeSourceRecord(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4) . '/components/com_breezingformsng/src/Service/Export/ExportEngine.php'
        );

        $contentBuilderDelete = strpos($source, "->delete(\$this->processor->database->quoteName('#__contentbuilderng_records'))");
        $sourceDelete = strpos($source, "->delete(\$this->processor->database->quoteName('#__facileforms_records'))");

        self::assertNotFalse($contentBuilderDelete);
        self::assertNotFalse($sourceDelete);
        self::assertLessThan($sourceDelete, $contentBuilderDelete);
        self::assertStringContainsString("'type') . ' = ' . \$this->processor->database->quote('com_breezingformsng')", $source);
        self::assertStringContainsString("'reference_id') . ' = :deletedFormId'", $source);
        self::assertStringContainsString("'record_id') . ' = :deletedRecordId'", $source);
    }
}
