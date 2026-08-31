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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\FormClosingMarkupBuilder;

final class FormClosingMarkupBuilderTest extends TestCase
{
    public function testBuildsModernWrapperClosure(): void
    {
        self::assertSame(
            "</div><!-- form end -->\n",
            (new FormClosingMarkupBuilder())->build(false, "\n")
        );
    }

    public function testBuildsLegacyWrapperClosure(): void
    {
        self::assertSame(
            "</div></div></div><div class=\"bfPage-bl\"><div class=\"bfPage-br\"><div class=\"bfPage-b\"></div></div></div></div><!-- form end -->\n",
            (new FormClosingMarkupBuilder())->build(true, "\n")
        );
    }
}
