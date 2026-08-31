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

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering\QuickMode;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeCheckboxBuilder;

final class QuickModeCheckboxBuilderTest extends TestCase
{
    public function testBuildsCheckedCheckboxWithEscapedValueAndAttributes(): void
    {
        $builder = new QuickModeCheckboxBuilder();

        self::assertSame(
            '<input class="ff_elem form-check-input" checked="checked" tabindex="2" '
            . 'disabled="disabled" type="checkbox" name="ff_nm_terms[]" value="A &amp; B" id="ff_elem12"/>' . "\n",
            $builder->build('ff_elem form-check-input', 'terms', ' A & B ', 12, true, 'tabindex="2" disabled="disabled" ')
        );
    }

    public function testBuildsUncheckedCheckboxWithoutOptionalAttributes(): void
    {
        $builder = new QuickModeCheckboxBuilder();

        self::assertSame(
            '<input class="ff_elem" type="checkbox" name="ff_nm_optin[]" value="yes" id="ff_elem13"/>' . "\n",
            $builder->build('ff_elem', 'optin', 'yes', 13, false)
        );
    }
}
