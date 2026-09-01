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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ContentBuilderValueHydrationScriptBuilder;

final class ContentBuilderValueHydrationScriptBuilderTest extends TestCase
{
    public function testBuildHydratesSimpleValueThroughJQueryAndNativeFallback(): void
    {
        $script = (new ContentBuilderValueHydrationScriptBuilder())->build(
            'Text',
            'title',
            17,
            'A "quoted" value'
        );

        self::assertStringContainsString(
            'JQuery("[name=\\"ff_nm_title[]\\"]").val("A \\"quoted\\" value")',
            $script
        );
        self::assertStringContainsString(
            'document.getElementById("ff_elem17").value="A \\"quoted\\" value"',
            $script
        );
        self::assertStringNotContainsString('setTimeout(function(){', $script);
    }

    public function testBuildWrapsCalendarHydrationInTheHistoricalTimeout(): void
    {
        $script = (new ContentBuilderValueHydrationScriptBuilder())->build(
            'Calendar',
            'date',
            18,
            '2026-08-30'
        );

        self::assertStringStartsWith('setTimeout(function(){', $script);
        self::assertStringEndsWith('}, 100);', $script);
        self::assertStringContainsString('ff_nm_date[]', $script);
    }
}
