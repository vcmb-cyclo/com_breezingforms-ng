<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
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

    public function testDoesNotMutateRecordValuesWhileCleaningThem(): void
    {
        $record = $this->record(23, 'title', 'Text', '  Hello  ');
        $builder = new ContentBuilderEditableRecordScriptBuilder(
            static fn(string $value): string => trim($value),
            static fn(string $value, int $width, string $break, bool $cut): string =>
                wordwrap($value, $width, $break, $cut)
        );

        $result = $builder->build([$record], [], false, 7, sys_get_temp_dir());

        self::assertStringContainsString('Hello', $result['javascript']);
        self::assertSame('  Hello  ', $record->recValue);
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

    public function testBuildsQuickModeFileUploadHydration(): void
    {
        $file = new stdClass();
        $file->recElementId = 31;
        $file->recName = 'document';
        $file->recType = 'File Upload';
        $file->recValue = "document.pdf\nsecond.jpg";

        $result = $this->builder()->build(
            [$file],
            [],
            true,
            7,
            sys_get_temp_dir()
        );

        self::assertSame(1, substr_count($result['contentBuilderScript'], 'function ff_flashupload_not_empty'));
        self::assertStringContainsString('cbFlashElemCnt["ff_elem31"] = 2;', $result['contentBuilderScript']);
        self::assertStringContainsString('cb_delete_31[0]', $result['javascript']);
        self::assertStringContainsString('cb_delete_31[1]', $result['javascript']);
        self::assertStringContainsString('bfDeactivateField["ff_nm_document[]"]', $result['javascript']);
    }

    public function testBuildsSignatureHydrationFromAnExistingFile(): void
    {
        $signature = new stdClass();
        $signature->recElementId = 41;
        $signature->recName = 'drawn_signature';
        $signature->recType = 'Signature';
        $signature->recValue = basename(__FILE__);

        $result = $this->builder()->build(
            [$signature],
            [],
            true,
            7,
            __DIR__
        );

        self::assertStringContainsString('ff_nm_drawn_signature[]', $result['javascript']);
        self::assertStringContainsString('data:image', $result['javascript']);
        self::assertStringContainsString('base64,', $result['javascript']);
    }

    public function testSkipsMissingAndOutOfDirectorySignatures(): void
    {
        $missing = $this->record(42, 'missing_signature', 'Signature', 'missing.png');
        $outside = $this->record(43, 'outside_signature', 'Signature', '../' . basename(__FILE__));

        $result = $this->builder()->build(
            [$missing, $outside],
            [],
            true,
            7,
            __DIR__
        );

        self::assertSame('', $result['contentBuilderScript']);
        self::assertSame('', $result['javascript']);
    }

    public function testBuildsRadioSelectAndCalendarHydration(): void
    {
        $radio = $this->record(51, 'gender', 'Radio Group', 'female');
        $select = $this->record(52, 'country', 'Select List', 'France');
        $calendar = $this->record(53, 'birthdate', 'Calendar', '2026-08-31');

        $result = $this->builder()->build(
            [$radio, $select, $calendar],
            [],
            true,
            9,
            sys_get_temp_dir()
        );

        self::assertStringContainsString('type == "radio"', $result['javascript']);
        self::assertStringContainsString('ff_elem52', $result['javascript']);
        self::assertStringContainsString('ff_nm_birthdate[]', $result['javascript']);
    }

    #[DataProvider('simpleValueTypes')]
    public function testBuildsHydrationForEachRemainingSimpleValueType(string $type, string $name): void
    {
        $result = $this->builder()->build(
            [$this->record(61, $name, $type, 'value')],
            [],
            false,
            9,
            sys_get_temp_dir()
        );

        self::assertStringContainsString('ff_nm_' . $name . '[]', $result['javascript']);
        self::assertStringContainsString('value', $result['javascript']);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function simpleValueTypes(): array
    {
        return [
            'textarea' => ['Textarea', 'description'],
            'number' => ['Number Input', 'quantity'],
            'hidden' => ['Hidden Input', 'token'],
        ];
    }

    public function testIgnoresUnsupportedRecordTypes(): void
    {
        $result = $this->builder()->build(
            [$this->record(62, 'unknown', 'Unsupported', 'value')],
            [],
            true,
            9,
            sys_get_temp_dir()
        );

        self::assertSame(['contentBuilderScript' => '', 'javascript' => ''], $result);
    }

    private function record(int $elementId, string $name, string $type, string $value): stdClass
    {
        $record = new stdClass();
        $record->recElementId = $elementId;
        $record->recName = $name;
        $record->recType = $type;
        $record->recValue = $value;

        return $record;
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
