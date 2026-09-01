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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeTextFieldStrategy;

final class QuickModeTextFieldStrategyTest extends TestCase
{
    public function testTextfieldUsesTranslatedValueAndPlaceholderAndPasswordType(): void
    {
        $html = (new QuickModeTextFieldStrategy())->textfield([
            'bfName' => 'customer',
            'dbId' => 12,
            'value' => 'old',
            'value_translationfr-FR' => 'new & value',
            'placeholder' => 'old placeholder',
            'placeholder_translationfr-FR' => 'Your name',
            'password' => true,
            'maxLength' => 40,
        ], 'fr-FR', 'ff_elem', 'tabindex="1" ');

        self::assertStringContainsString('placeholder="Your name"', $html);
        self::assertStringContainsString('type="password"', $html);
        self::assertStringContainsString('value="new &amp; value"', $html);
        self::assertStringContainsString('tabindex="1" maxlength="40"', $html);
    }

    public function testNumberInputKeepsRangeBoundsAndRendererSpecificLengthAttribute(): void
    {
        $html = (new QuickModeTextFieldStrategy())->numberInput([
            'bfName' => 'age',
            'dbId' => 13,
            'value' => '10',
            'range' => true,
            'maxLength' => 3,
            'step' => '1',
            'max' => '120',
            'min' => '0',
        ], 'en-GB', 'ff_elem', '', '', 'max');

        self::assertStringContainsString('type="range"', $html);
        self::assertStringContainsString('max="3"', $html);
        self::assertStringContainsString('id="ff_elem13" step="1" max="120" min="0"', $html);
    }

    public function testBootstrapNumberPlaceholderCompatibilityIsExplicit(): void
    {
        $html = (new QuickModeTextFieldStrategy())->numberInput([
            'bfName' => 'amount',
            'dbId' => 14,
            'value' => '5',
            'placeholder_translationen-GB' => 'Amount',
            'step' => '1',
            'max' => '10',
            'min' => '0',
        ], 'en-GB', 'form-control', '', '', 'maxlength', true);

        self::assertStringContainsString('placeholder="000"', $html);
    }
}
