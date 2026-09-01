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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ContentBuilderChoiceHydrationScriptBuilder;

final class ContentBuilderChoiceHydrationScriptBuilderTest extends TestCase
{
    public function testBuildHydratesEachCheckboxChoiceAndEscapesValues(): void
    {
        $script = (new ContentBuilderChoiceHydrationScriptBuilder())->build(
            'checkbox',
            'topics',
            7,
            'Cycling, A"B'
        );

        self::assertSame(2, substr_count($script, 'document.ff_form7.elements.length'));
        self::assertStringContainsString('type == "checkbox"', $script);
        self::assertStringContainsString('value == "A\\"B"', $script);
        self::assertStringContainsString('.click();', $script);
    }

    public function testBuildKeepsAnEmptyStoredChoiceAsAnEmptyValue(): void
    {
        $script = (new ContentBuilderChoiceHydrationScriptBuilder())->build('radio', 'choice', 8, '');

        self::assertStringContainsString('type == "radio"', $script);
        self::assertStringContainsString('value == ""', $script);
    }
}
