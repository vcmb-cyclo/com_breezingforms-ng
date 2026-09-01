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

final class ContentBuilderValueScriptBuilderTest extends TestCase
{
    public function testSimpleValueUsesJQueryAndNativeFallback(): void
    {
        $script = $this->builder()->build($this->entry('Text', 'Alice', 'name', 12), 7);

        self::assertSame(
            "if(typeof JQuery != \"undefined\"){JQuery(\"[name=\\\"ff_nm_name[]\\\"]\").val(\"Alice\");if(typeof JQuery != \"undefined\")JQuery(\"[name=\\\"ff_nm_name[]\\\"]\").trigger(\"change\");}else{if(document.getElementById(\"ff_elem12\"))document.getElementById(\"ff_elem12\").value=\"Alice\";if(typeof JQuery != \"undefined\")JQuery(document.getElementById(\"ff_elem12\")).trigger(\"change\");\n}",
            $script
        );
    }

    public function testCalendarValueIsDeferred(): void
    {
        $script = $this->builder()->build($this->entry('Calendar', '2026-08-30', 'date', 13), 7);

        self::assertStringStartsWith('setTimeout(function(){', $script);
        self::assertStringEndsWith('}, 100);', $script);
        self::assertStringContainsString('ff_nm_date[]', $script);
    }

    public function testCheckboxAndRadioValuesPreserveSelectedValues(): void
    {
        $checkboxScript = $this->builder()->build($this->entry('Checkbox Group', 'red, blue', 'colors', 14), 7);
        $radioScript = $this->builder()->build($this->entry('Radio Group', 'email', 'contact', 15), 7);

        self::assertSame(2, substr_count($checkboxScript, '.click();'));
        self::assertStringContainsString('value == "red"', $checkboxScript);
        self::assertStringContainsString('value == "blue"', $checkboxScript);
        self::assertStringContainsString('type == "radio"', $radioScript);
        self::assertStringContainsString('value == "email"', $radioScript);
    }

    public function testEmptyCheckboxAndUnsupportedValuesProduceNoScript(): void
    {
        self::assertSame('', $this->builder()->build($this->entry('Checkbox', '', 'empty', 16), 7));
        self::assertSame('', $this->builder()->build($this->entry('Signature', 'data', 'signature', 17), 7));
    }

    private function builder(): ContentBuilderValueScriptBuilder
    {
        return new ContentBuilderValueScriptBuilder();
    }

    private function entry(string $type, string $value, string $name, int $elementId): object
    {
        return (object) [
            'recType' => $type,
            'recValue' => $value,
            'recName' => $name,
            'recElementId' => $elementId,
        ];
    }
}
