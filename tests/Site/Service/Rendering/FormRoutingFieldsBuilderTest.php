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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\FormRoutingFieldsBuilder;

final class FormRoutingFieldsBuilderTest extends TestCase
{
    public function testBuildsReturnAndComponentTemplateFields(): void
    {
        self::assertSame(
            '<input type="hidden" name="return" value="https&quot;next"/>' . "\r\n"
            . '<input type="hidden" name="tmpl" value="component"/>' . "\r\n",
            (new FormRoutingFieldsBuilder())->build('https"next', 'component')
        );
    }

    public function testOmitsFieldsWhenRoutingParametersAreAbsent(): void
    {
        self::assertSame('', (new FormRoutingFieldsBuilder())->build('', ''));
        self::assertSame(
            '<input type="hidden" name="return" value="next"/>' . "\r\n",
            (new FormRoutingFieldsBuilder())->build('next', 'html')
        );
    }
}
