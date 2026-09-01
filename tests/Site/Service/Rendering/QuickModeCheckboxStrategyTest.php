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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeCheckboxStrategy;

final class QuickModeCheckboxStrategyTest extends TestCase
{
    public function testBuildPreservesCheckedAndEscapesValue(): void
    {
        $html = (new QuickModeCheckboxStrategy())->build([
            'bfName' => 'accept',
            'value' => 'A&B',
            'dbId' => 31,
            'checked' => true,
        ], 'form-check-input', 'tabindex="2" ');

        self::assertSame(
            '<input class="form-check-input" checked="checked" tabindex="2" type="checkbox" '
            . 'name="ff_nm_accept[]" value="A&amp;B" id="ff_elem31"/>' . "\n",
            $html
        );
    }

    public function testBuildAddsDisabledAttributeForReadonlyFields(): void
    {
        $html = (new QuickModeCheckboxStrategy())->build([
            'bfName' => 'terms',
            'value' => 'yes',
            'dbId' => 32,
            'readonly' => true,
        ], 'ff_elem', '', true);

        self::assertStringContainsString('disabled="disabled"', $html);
    }
}
