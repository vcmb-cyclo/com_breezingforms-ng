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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ClassicHiddenInputBuilder;

final class ClassicHiddenInputBuilderTest extends TestCase
{
    public function testBuildsHiddenInputWithHistoricalNameAndValue(): void
    {
        self::assertSame(
            "\t<input id=\"ff_elem17\" type=\"hidden\" name=\"ff_nm_token[]\" value=\"abc123\" />\n",
            (new ClassicHiddenInputBuilder())->build(17, 'token', 'abc123')
        );
    }

    public function testPreservesRawValueAndCustomFormatting(): void
    {
        self::assertSame(
            "  <input id=\"ff_elem18\" type=\"hidden\" name=\"ff_nm_raw[]\" value=\"A & B\" />\r\n",
            (new ClassicHiddenInputBuilder())->build(18, 'raw', 'A & B', '  ', "\r\n")
        );
    }
}
