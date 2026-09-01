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

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\FormOpeningMarkupBuilder;

final class FormOpeningMarkupBuilderTest extends TestCase
{
    public function testBuildsWrapperWithOptionalClass(): void
    {
        self::assertSame(
            '<div id="ff_formdiv12" class="bfFormDiv custom-form">',
            (new FormOpeningMarkupBuilder())->build('12', 'custom-form')
        );
    }

    public function testBuildsWrapperWithoutClass(): void
    {
        self::assertSame(
            '<div id="ff_formdiv12" class="bfFormDiv">',
            (new FormOpeningMarkupBuilder())->build('12', '')
        );
    }
}
