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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\FormContextFieldsBuilder;

final class FormContextFieldsBuilderTest extends TestCase
{
    public function testBuildsFieldsInProvidedOrderAndEscapesValues(): void
    {
        self::assertSame(
            "\t<input type=\"hidden\" name=\"ff_applic\" value=\"module&amp;name\"/>\r\n"
            . "\t<input type=\"hidden\" name=\"ff_record_id\" value=\"12\"/>\r\n",
            (new FormContextFieldsBuilder())->build(
                ['ff_applic' => 'module&name', 'ff_record_id' => 12],
                "\t"
            )
        );
    }

    public function testBuildsNoOutputForEmptyContext(): void
    {
        self::assertSame('', (new FormContextFieldsBuilder())->build([], ''));
    }
}
