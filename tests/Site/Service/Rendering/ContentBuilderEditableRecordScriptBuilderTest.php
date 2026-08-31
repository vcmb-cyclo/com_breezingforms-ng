<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use stdClass;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ContentBuilderEditableRecordScriptBuilder;

final class ContentBuilderEditableRecordScriptBuilderTest extends TestCase
{
    public function testBuildsHydrationForEditableValueFamilies(): void
    {
        $text = new stdClass();
        $text->recElementId = 21;
        $text->recName = 'title';
        $text->recType = 'Text';
        $text->recValue = 'Hello';

        $checkbox = new stdClass();
        $checkbox->recElementId = 22;
        $checkbox->recName = 'terms';
        $checkbox->recType = 'Checkbox';
        $checkbox->recValue = 'yes';

        $result = $this->builder()->build(
            [$text, $checkbox],
            [],
            false,
            7,
            sys_get_temp_dir()
        );

        self::assertSame('', $result['contentBuilderScript']);
        self::assertStringContainsString('ff_nm_title[]', $result['javascript']);
        self::assertStringContainsString('ff_form7', $result['javascript']);
    }

    public function testSkipsNonEditableRecordsAndNonQuickModeFiles(): void
    {
        $file = new stdClass();
        $file->recElementId = 31;
        $file->recName = 'document';
        $file->recType = 'File Upload';
        $file->recValue = 'document.pdf';

        $hidden = new stdClass();
        $hidden->recElementId = 32;
        $hidden->recName = 'ignored';
        $hidden->recType = 'Text';
        $hidden->recValue = 'ignored';

        $result = $this->builder()->build(
            [$file, $hidden],
            [32],
            false,
            7,
            sys_get_temp_dir()
        );

        self::assertSame('', $result['contentBuilderScript']);
        self::assertSame('', $result['javascript']);
    }

    private function builder(): ContentBuilderEditableRecordScriptBuilder
    {
        return new ContentBuilderEditableRecordScriptBuilder(
            static fn(string $value): string => $value,
            static fn(string $value, int $width, string $break, bool $cut): string =>
                wordwrap($value, $width, $break, $cut)
        );
    }
}
