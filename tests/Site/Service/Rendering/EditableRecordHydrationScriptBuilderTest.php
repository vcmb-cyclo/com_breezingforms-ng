<?php

/**
 * BreezingForms NG - A Joomla Forms Application
 *
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 **/

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ContentBuilderValueScriptBuilder;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\EditableRecordHydrationScriptBuilder;

final class EditableRecordHydrationScriptBuilderTest extends TestCase
{
    public function testBuildsAllEditableRecordControlFamilies(): void
    {
        $script = (new EditableRecordHydrationScriptBuilder())->build([
            (object) ['type' => 'Text', 'name' => 'title', 'element' => 11, 'value' => 'Hello'],
            (object) ['type' => 'Checkbox', 'name' => 'flag', 'element' => 12, 'value' => 'yes'],
            (object) ['type' => 'Checkbox Group', 'name' => 'colors', 'element' => 13, 'value' => 'red & blue'],
            (object) ['type' => 'Radio Group', 'name' => 'size', 'element' => 14, 'value' => 'L'],
            (object) ['type' => 'Select List', 'name' => 'country', 'element' => 15, 'value' => 'FR'],
        ], 9);

        self::assertStringContainsString('ff_nm_title[]', $script);
        self::assertStringContainsString('ff_elem12', $script);
        self::assertStringContainsString('type == "checkbox"', $script);
        self::assertStringContainsString('type == "radio"', $script);
        self::assertStringContainsString('options[i].value == "FR"', $script);
        self::assertStringContainsString('"red & blue"', $script);
    }

    public function testSkipsEmptySingleCheckboxAndUnknownEntries(): void
    {
        $script = (new EditableRecordHydrationScriptBuilder())->build([
            (object) ['type' => 'Checkbox', 'name' => 'flag', 'element' => 12, 'value' => ''],
            (object) ['type' => 'Unsupported', 'name' => 'ignored', 'element' => 13, 'value' => 'value'],
        ], 9);

        self::assertSame('', $script);
    }

    public function testCleansValuesWithoutMutatingInputEntries(): void
    {
        $entry = (object) ['type' => 'Text', 'name' => 'title', 'element' => 11, 'value' => '  Hello  '];
        $builder = new EditableRecordHydrationScriptBuilder(
            new ContentBuilderValueScriptBuilder(),
            static fn(string $value): string => trim($value)
        );

        $script = $builder->build([$entry], 9);

        self::assertStringContainsString('Hello', $script);
        self::assertSame('  Hello  ', $entry->value);
    }
}
