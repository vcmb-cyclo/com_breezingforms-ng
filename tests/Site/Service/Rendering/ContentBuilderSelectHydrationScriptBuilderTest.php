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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ContentBuilderSelectHydrationScriptBuilder;

final class ContentBuilderSelectHydrationScriptBuilderTest extends TestCase
{
    public function testBuildHydratesEachSelectedValueAndTriggersChange(): void
    {
        $script = (new ContentBuilderSelectHydrationScriptBuilder())->build(21, 'one, two');

        self::assertSame(2, substr_count($script, 'value == "'));
        self::assertStringContainsString('value == "one"', $script);
        self::assertStringContainsString('value == "two"', $script);
        self::assertStringContainsString('.attr("selected", true).trigger("change")', $script);
    }

    public function testBuildPreservesAnEmptyStoredValue(): void
    {
        $script = (new ContentBuilderSelectHydrationScriptBuilder())->build(22, '');

        self::assertStringContainsString('value == ""', $script);
    }
}
