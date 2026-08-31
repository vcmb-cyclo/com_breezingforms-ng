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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ContentBuilderEditableScriptWrapperBuilder;

final class ContentBuilderEditableScriptWrapperBuilderTest extends TestCase
{
    public function testBuildWrapsValidationHydrationAndSecurityCodeCleanup(): void
    {
        $script = (new ContentBuilderEditableScriptWrapperBuilder())->build(
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
