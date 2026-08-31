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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeTextareaStrategy;

final class QuickModeTextareaStrategyTest extends TestCase
{
    public function testBuildAppliesTranslationsSizingAndMaxLengthCallback(): void
    {
        $html = (new QuickModeTextareaStrategy())->build([
            'bfName' => 'message',
            'dbId' => 21,
            'value' => 'old',
            'value_translationfr-FR' => '<new>',
            'placeholder_translationfr-FR' => 'Write here',
            'width' => '20em',
            'height' => '5em',
            'maxlength' => 120,
            'showMaxlengthCounter' => true,
        ], 'fr-FR', 'ff_elem', 'tabindex="1" ', 'cols="20" rows="5" ');

        self::assertStringContainsString('placeholder="Write here"', $html);
        self::assertStringContainsString('style="width:20em;height:5em;"', $html);
        self::assertStringContainsString('onkeyup="bfCheckMaxlength(21, 120, true)"', $html);
        self::assertStringContainsString('&lt;new&gt;', $html);
    }

    public function testBootstrapSizingKeepsMinimumWidthAndDisablesCounterWhenRequested(): void
    {
        $html = (new QuickModeTextareaStrategy())->build([
            'bfName' => 'description',
            'dbId' => 22,
            'value' => 'text',
            'width' => '30em',
            'height' => '',
            'maxlength' => 80,
            'showMaxlengthCounter' => false,
        ], 'en-GB', 'form-control', '', '', true);

        self::assertStringContainsString('width:30em !important; min-width:30em !important;', $html);
        self::assertStringContainsString('bfCheckMaxlength(22, 80, false)', $html);
    }
}
