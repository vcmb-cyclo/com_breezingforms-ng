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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\FormTokenFieldBuilder;

final class FormTokenFieldBuilderTest extends TestCase
{
    public function testFormatsTokenWithIndentationAndHistoricalNewline(): void
    {
        self::assertSame(
            "\t<input type=\"hidden\" name=\"token\" value=\"abc\"/>\r\n",
            (new FormTokenFieldBuilder())->build(
                '<input type="hidden" name="token" value="abc"/>',
                "\t"
            )
        );
    }

    public function testPreservesEmptyTokenInput(): void
    {
        self::assertSame("\r\n", (new FormTokenFieldBuilder())->build('', ''));
    }
}
