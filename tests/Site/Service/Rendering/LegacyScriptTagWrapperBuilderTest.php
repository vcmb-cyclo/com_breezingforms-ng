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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\LegacyScriptTagWrapperBuilder;

final class LegacyScriptTagWrapperBuilderTest extends TestCase
{
    public function testContentBuilderReadonlyWrapsScriptWithHistoricalMarkers(): void
    {
        self::assertSame(
            "<script type=\"text/javascript\">\n<!--\nreadonly();\n//-->\n</script>\n",
            (new LegacyScriptTagWrapperBuilder())->contentBuilderReadonly("readonly();\n")
        );
    }

    public function testFormValidationBuildsValidationScriptEnvelope(): void
    {
        $builder = new LegacyScriptTagWrapperBuilder();

        self::assertSame(
            "<script type=\"text/javascript\">\n<!--\n\nfiles();captcha();",
            $builder->formValidationOpen('files();', 'captcha();')
        );
        self::assertSame("//-->\r\n</script>\r\n", $builder->formValidationClose("\r\n"));
    }

    public function testEditableRecordWrapsHydrationAndClearsLegacySecurityCode(): void
    {
        $script = (new LegacyScriptTagWrapperBuilder())->editableRecord(27, 'hydrate();', "\n");

        self::assertStringContainsString('function bfLoadEditable(){', $script);
        self::assertStringContainsString('hydrate();', $script);
        self::assertStringContainsString('document.ff_form27.elements.length', $script);
        self::assertStringContainsString('name == "ff_nm_seccode[]"', $script);
        self::assertStringContainsString('value = "";', $script);
        self::assertStringEndsWith("\n", $script);
    }

    public function testContentBuilderEditableWrapsValidationHydrationAndSecurityCodeCleanup(): void
    {
        $script = (new LegacyScriptTagWrapperBuilder())->contentBuilderEditable(
            7,
            'function ff_flashupload_not_empty(){}',
            'JQuery("#field").val("value");'
        );

        self::assertStringContainsString('var cbFlashElemCnt = new Array();', $script);
        self::assertStringContainsString('function ff_flashupload_not_empty(){}', $script);
        self::assertStringContainsString('function bfLoadContentBuilderEditable(){', $script);
        self::assertStringContainsString('JQuery("#field").val("value");', $script);
        self::assertStringContainsString('document.ff_form7.elements.length', $script);
        self::assertStringContainsString('ff_nm_seccode[]', $script);
        self::assertStringEndsWith("                    \n", $script);
    }
}
